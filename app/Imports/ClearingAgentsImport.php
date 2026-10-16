<?php

namespace App\Imports;

use App\Models\Border;
use App\Models\ClearingAgent;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithLimit;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

/**
 * Columns: name, email, phonenumber, worknumber, country, city, suburb,
 * street_address, borders (comma separated border names).
 * A row whose name matches an existing clearing agent updates it. Borders
 * are matched by name and created when missing; borders picked on the
 * import form are attached to every agent in the file. Borders are only
 * ever added, never detached.
 */
class ClearingAgentsImport implements  ToCollection, SkipsEmptyRows, WithLimit,
WithHeadingRow,
SkipsOnError,
WithValidation,
WithChunkReading,
WithBatchInserts
{
    use Importable, SkipsErrors;

    private $border_ids;

    public function __construct($border_ids = [])
    {
        $this->border_ids = array_filter((array) $border_ids);
    }

    public function limit(): int
    {
        return 2500;
    }

    public function clearing_agentNumber(){

        $initials = "";
        if (isset(Auth::user()->company)) {
            $str = Auth::user()->company->name;
        }elseif (isset(Auth::user()->employee->company)) {
            $str = Auth::user()->employee->company->name;
        }
        if (isset($str)) {
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }

        $clearing_agent = ClearingAgent::withTrashed()->orderBy('id', 'desc')->first();
        $number = $clearing_agent ? $clearing_agent->id + 1 : 1;

        return $initials .'CA'. str_pad($number, 5, "0", STR_PAD_LEFT);
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if ($row->filter()->isNotEmpty()) {
                $name = trim((string) $row['name']);
                // "#" marks the template's example row
                if ($name == "" || str_starts_with($name, '#')) {
                    continue;
                }

                $fields = [
                    'email' => $row['email'] ?? null,
                    'phonenumber' => $row['phonenumber'] ?? ($row['phone_number'] ?? null),
                    'worknumber' => $row['worknumber'] ?? ($row['work_number'] ?? null),
                    'country' => $row['country'] ?? null,
                    'city' => $row['city'] ?? null,
                    'suburb' => $row['suburb'] ?? null,
                    'street_address' => $row['street_address'] ?? ($row['streetaddress'] ?? null),
                ];

                $clearing_agent = ClearingAgent::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($name)])->first();

                if (isset($clearing_agent)) {
                    // only overwrite what the file actually has a value for
                    foreach ($fields as $field => $value) {
                        if (filled($value)) {
                            $clearing_agent->$field = $value;
                        }
                    }
                    $clearing_agent->update();
                } else {
                    $clearing_agent = new ClearingAgent;
                    $clearing_agent->user_id = Auth::user()->id;
                    $clearing_agent->clearing_agent_number = $this->clearing_agentNumber();
                    $clearing_agent->name = $name;
                    foreach ($fields as $field => $value) {
                        $clearing_agent->$field = $value;
                    }
                    $clearing_agent->status = 1;
                    $clearing_agent->save();
                }

                $border_ids = array_merge($this->border_ids, $this->rowBorderIds($row['borders'] ?? ($row['border'] ?? null)));
                if (count($border_ids) > 0) {
                    $clearing_agent->borders()->syncWithoutDetaching(array_unique($border_ids));
                }
            }
        }
    }

    private function rowBorderIds($borders)
    {
        $ids = [];
        foreach (preg_split('/[,;]/', (string) $borders) as $name) {
            $name = trim($name);
            if ($name == "") {
                continue;
            }
            $border = BordersImport::findBorder($name);
            if (!isset($border)) {
                $border = new Border;
                $border->user_id = Auth::user()->id;
                $border->name = $name;
                $border->save();
            }
            $ids[] = $border->id;
        }

        return $ids;
    }

    public function rules(): array{
        return[
            '*.name' => ['required'],
        ];
    }

    public function batchSize(): int
    {
        return 10;
    }

    public function chunkSize(): int
    {
        return 10;
    }
}
