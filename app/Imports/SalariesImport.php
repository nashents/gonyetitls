<?php

namespace App\Imports;

use App\Exports\Templates\SalariesImportTemplate;
use App\Imports\Concerns\SkipsTemplateExampleRows;
use App\Models\Currency;
use App\Models\Deduction;
use App\Models\Employee;
use App\Models\Salary;
use App\Models\SalaryItem;
use App\Services\Payroll\SalaryPayeService;
use App\Services\Payroll\StatutoryCalculator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithLimit;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Salaries import: one salary structure per row, the bulk version of
 * Salaries\Create::store() (new employee) and Salaries\Edit::update()
 * (employee who already has a salary).
 *
 * Columns: employee_number (or name + surname), currency, frequency, basic*,
 * paye, aids_levy, then one "Allowance: <name>" / "Deduction: <name>" amount
 * column per allowance / deduction (see SalariesImportTemplate::itemColumns()).
 * All amounts are in the row's salary currency.
 *
 * Employees, currencies, allowances and deductions are matched, never created.
 * A blank cell never clears anything: on an existing salary it means "keep",
 * and 0 in an allowance / deduction column removes that item. PAYE, AIDS Levy
 * and (where rates are configured) NSSA / NEC / Pension are calculated, the
 * same way the Add Salary form does. Loans and recoveries are left untouched.
 *
 * Rows that can't be imported are skipped and reported back via $skipped;
 * every row is its own transaction, so one bad row never blocks the rest.
 */
class SalariesImport implements ToCollection, SkipsEmptyRows, WithHeadingRow, WithLimit, WithMultipleSheets
{
    use SkipsTemplateExampleRows;
    use Importable;

    private const FIXED_COLUMNS = ['employee_number', 'name', 'surname', 'currency', 'frequency', 'basic', 'paye', 'aids_levy'];

    /** Statutory deduction name => salaries column prefix. */
    private const STATUTORY = ['NSSA' => 'nssa', 'NEC' => 'nec', 'Pension' => 'pension'];

    public int $created = 0;
    public int $updated = 0;
    public array $skipped = [];
    public array $unknownColumns = [];

    private ?array $itemColumns = null;
    private $company;

    // Only the first sheet holds data; the template's Instructions/Lists sheets are ignored.
    public function sheets(): array
    {
        return [0 => $this];
    }

    public function limit(): int
    {
        return 2500;
    }

    public function collection(Collection $rows)
    {
        $this->company = Auth::user()->employee?->company;
        $this->itemColumns ??= SalariesImportTemplate::itemColumns(false);

        if ($rows->isNotEmpty()) {
            $this->noteUnknownColumns($rows->first()->keys()->all());
        }

        foreach ($rows as $row) {
            $number = trim((string) $row->get('employee_number'));
            $fullName = trim($row->get('name').' '.$row->get('surname'));
            $label = trim($number.' '.$fullName) ?: 'a row';

            // Item cells that were filled in on this row: column key => raw value.
            $typed = [];
            foreach (array_keys($this->itemColumns) as $key) {
                if (filled($row->get($key))) {
                    $typed[$key] = $row->get($key);
                }
            }

            // An employee pre-listed on the template that nobody filled in.
            if (empty($typed) && collect(['currency', 'frequency', 'basic', 'paye', 'aids_levy'])->every(fn ($c) => blank($row->get($c)))) {
                continue;
            }

            [$employee, $problem] = $this->findEmployee($number, $row->get('name'), $row->get('surname'));
            if (!$employee) {
                $this->skipped[] = "{$label}: {$problem}";
                continue;
            }

            $salary = Salary::where('employee_id', $employee->id)->first();

            $basic = $this->amount($row->get('basic'));
            if ($basic === false) {
                $this->skipped[] = "{$label}: basic must be a number of 0 or more";
                continue;
            }
            if ($basic === null && !$salary) {
                $this->skipped[] = "{$label}: basic is required for a new salary";
                continue;
            }

            $currency = null;
            if (filled($row->get('currency'))) {
                $currency = Currency::whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim((string) $row->get('currency')))])->first();
                if (!$currency) {
                    $this->skipped[] = "{$label}: currency \"{$row->get('currency')}\" not found";
                    continue;
                }
            }
            $currencyId = $currency?->id ?? $salary?->currency_id ?? $this->company?->currency_id;
            if (!$currencyId) {
                $this->skipped[] = "{$label}: currency is missing";
                continue;
            }

            $frequency = strtolower(trim((string) $row->get('frequency')));
            if ($frequency !== '' && !in_array($frequency, SalariesImportTemplate::FREQUENCIES, true)) {
                $this->skipped[] = "{$label}: frequency must be one of ".implode(', ', SalariesImportTemplate::FREQUENCIES);
                continue;
            }

            $paye = $this->yesNo($row->get('paye'));
            $aidsLevy = $this->yesNo($row->get('aids_levy'));
            if ($paye === 'invalid' || $aidsLevy === 'invalid') {
                $this->skipped[] = "{$label}: paye and aids_levy must be Yes or No";
                continue;
            }

            $amounts = [];
            foreach ($typed as $key => $value) {
                $amount = $this->amount($value);
                if ($amount === false) {
                    $this->skipped[] = "{$label}: \"{$this->itemColumns[$key]['heading']}\" must be a number of 0 or more";
                    continue 2;
                }
                $amounts[$key] = $amount;
            }

            try {
                DB::transaction(function () use ($employee, $salary, $basic, $currencyId, $frequency, $paye, $aidsLevy, $amounts) {
                    $isNew = !$salary;

                    if ($isNew) {
                        $salary = new Salary;
                        $salary->user_id = Auth::id();
                        $salary->salary_number = $this->salaryNumber();
                        $salary->employee_id = $employee->id;
                        $salary->frequency = 'monthly';
                        $salary->paye = false;
                        $salary->aids_levy = false;
                    }

                    $salary->currency_id = $currencyId;
                    if ($basic !== null) {
                        $salary->basic = $basic;
                    }
                    if ($frequency !== '') {
                        $salary->frequency = $frequency;
                    }
                    if ($paye !== null) {
                        $salary->paye = $paye;
                    }
                    if ($aidsLevy !== null) {
                        $salary->aids_levy = $aidsLevy;
                    }
                    $salary->save();

                    $typedStatutory = [];
                    foreach ($amounts as $key => $amount) {
                        $item = $this->itemColumns[$key];

                        // A typed amount replaces the employee's current one; 0 just removes it.
                        SalaryItem::where('salary_id', $salary->id)->where($item['column'], $item['id'])->delete();
                        if ($amount > 0) {
                            SalaryItem::create([
                                'salary_id'     => $salary->id,
                                $item['column'] => $item['id'],
                                'amount'        => $amount,
                            ]);
                        }

                        if ($item['column'] === 'deduction_id') {
                            foreach (array_keys(self::STATUTORY) as $name) {
                                if (strcasecmp($item['name'], $name) === 0) {
                                    $typedStatutory[] = $name;
                                }
                            }
                        }
                    }

                    $this->recalculate($salary, $employee, $typedStatutory);

                    $isNew ? $this->created++ : $this->updated++;
                });
            } catch (\Throwable $e) {
                Log::error("Salaries import: {$label}: ".$e->getMessage());
                $this->skipped[] = "{$label}: could not be saved (".$e->getMessage().')';
            }
        }
    }

    /**
     * Totals, statutory contributions and PAYE from the salary's full set of
     * items, as Salaries\Edit::update() does - so loans / recoveries already
     * on the salary stay counted exactly as before.
     */
    private function recalculate(Salary $salary, Employee $employee, array $typedStatutory): void
    {
        $companyCurrencyId = SalaryPayeService::companyCurrencyId($salary);
        $value = fn (SalaryItem $item) => SalaryPayeService::itemValue($item, $companyCurrencyId);

        // PAYE / AIDS Levy are always recomputed, never stacked on last time's.
        $autoIds = Deduction::whereIn('name', SalariesImportTemplate::AUTO_DEDUCTIONS)->pluck('id');
        SalaryItem::where('salary_id', $salary->id)->whereIn('deduction_id', $autoIds)->delete();

        $totalAllowances = SalaryItem::where('salary_id', $salary->id)->whereNotNull('allowance_id')->get()->sum($value);
        $gross = (float) $salary->basic + $totalAllowances;

        $this->applyStatutory($salary, $employee, $gross, $typedStatutory);

        $totalDeductions = SalaryItem::where('salary_id', $salary->id)
            ->where(fn ($q) => $q->whereNotNull('deduction_id')->orWhereNotNull('loan_id'))
            ->get()->sum($value);

        if ($salary->paye) {
            $payeService = new SalaryPayeService();
            $taxable = max(0, $gross - $payeService->nonTaxableAllowances($salary->id, $companyCurrencyId));
            $totalDeductions += $payeService->applyPaye($salary, $taxable);
        }

        $salary->gross = $gross;
        $salary->net = $gross - $totalDeductions;
        $salary->total_allowances = $totalAllowances;
        $salary->total_deductions = $totalDeductions;
        $salary->save();
    }

    /**
     * NSSA / NEC / Pension, as Salaries\Create::processStatutoryContributions().
     * An amount typed on the row - or entered by hand earlier - is an override
     * and is left alone; otherwise the system figure is (re)calculated on the
     * new gross. Each is zero when no rate / assignment is configured.
     */
    private function applyStatutory(Salary $salary, Employee $employee, float $gross, array $typedStatutory): void
    {
        $companyId = $employee->company_id ?? $this->company?->id;
        if (!$companyId) {
            return;
        }

        $calculator = new StatutoryCalculator($companyId);
        $today = now()->toDateString();

        foreach (self::STATUTORY as $name => $prefix) {
            $deductionId = Deduction::where('name', $name)->value('id');
            $existing = $deductionId
                ? SalaryItem::where('salary_id', $salary->id)->where('deduction_id', $deductionId)->get()
                : collect();
            $lastAuto = (float) $salary->{"{$prefix}_employee_amount"};

            if (in_array($name, $typedStatutory, true)) {
                // Manual override: no system-calculated amount on record.
                $salary->{"{$prefix}_employee_amount"} = 0;
                $salary->{"{$prefix}_employer_amount"} = 0;
                continue;
            }

            $isSystemFigure = $existing->isEmpty()
                || ($existing->count() === 1 && $lastAuto > 0 && abs((float) $existing->first()->amount - $lastAuto) < 0.005);
            if (!$isSystemFigure) {
                continue;
            }

            $result = match ($name) {
                'NSSA'    => $calculator->computeNssa($gross, $today),
                'NEC'     => $calculator->computeNec($employee, $gross, $today),
                'Pension' => $calculator->computePension($employee, $gross, $today),
            };

            SalaryItem::whereIn('id', $existing->pluck('id'))->delete();
            if ($result['employee_amount'] > 0) {
                SalaryItem::create([
                    'salary_id'    => $salary->id,
                    'deduction_id' => Deduction::firstOrCreate(['name' => $name], ['status' => 1])->id,
                    'amount'       => $result['employee_amount'],
                ]);
            }

            $salary->{"{$prefix}_employee_amount"} = $result['employee_amount'];
            $salary->{"{$prefix}_employer_amount"} = $result['employer_amount'];
            if ($name === 'NEC') {
                $salary->nec_category_id = $result['nec_category_id'];
            } elseif ($name === 'Pension') {
                $salary->pension_scheme_id = $result['pension_scheme_id'];
            }
        }
    }

    /** @return array{0:?Employee,1:?string} the employee, or why they couldn't be found */
    private function findEmployee(string $number, $name, $surname): array
    {
        if ($number !== '') {
            $employee = Employee::whereRaw('LOWER(TRIM(employee_number)) = ?', [strtolower($number)])->first();

            return $employee ? [$employee, null] : [null, 'employee number not found'];
        }

        $name = strtolower(trim((string) $name));
        $surname = strtolower(trim((string) $surname));
        if ($name === '' || $surname === '') {
            return [null, 'give the employee_number, or both name and surname'];
        }

        $matches = Employee::whereRaw('LOWER(TRIM(name)) = ?', [$name])
            ->whereRaw('LOWER(TRIM(surname)) = ?', [$surname])
            ->limit(2)->get();

        if ($matches->count() > 1) {
            return [null, 'more than one employee has this name - use the employee_number'];
        }

        return $matches->isEmpty() ? [null, 'employee not found'] : [$matches->first(), null];
    }

    /** null = blank, false = not a valid amount, otherwise the number. */
    private function amount($value)
    {
        if (blank($value)) {
            return null;
        }
        $value = is_string($value) ? str_replace([',', ' '], '', $value) : $value;

        return is_numeric($value) && $value >= 0 ? (float) $value : false;
    }

    /** null = blank, true / false, or 'invalid'. */
    private function yesNo($value)
    {
        if (is_bool($value)) {
            return $value;
        }
        if (blank($value)) {
            return null;
        }
        $value = strtolower(trim((string) $value));
        if (in_array($value, ['yes', 'y', '1', 'true'], true)) {
            return true;
        }

        return in_array($value, ['no', 'n', '0', 'false'], true) ? false : 'invalid';
    }

    /** Allowance / deduction columns in the file that match nothing in the system. */
    private function noteUnknownColumns(array $headings): void
    {
        foreach ($headings as $heading) {
            if (!is_string($heading) || in_array($heading, self::FIXED_COLUMNS, true) || isset($this->itemColumns[$heading])) {
                continue;
            }
            if (str_starts_with($heading, 'allowance_') || str_starts_with($heading, 'deduction_')) {
                $this->unknownColumns[] = $heading;
            }
        }
    }

    /** Same format as Salaries\Create::salaryNumber(). */
    private function salaryNumber(): string
    {
        $words = explode(' ', trim((string) $this->company?->name));
        $initials = ($words[0][0] ?? '').($words[1][0] ?? '');
        $next = (Salary::withTrashed()->max('id') ?? 0) + 1;

        return $initials.'S'.str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}
