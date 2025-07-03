<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Customer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithLimit;

class CustomersImport implements 
    ToCollection,
    SkipsEmptyRows,
    WithHeadingRow,
    SkipsOnError,
    WithValidation,
    WithChunkReading,
    WithBatchInserts,
    WithLimit
{
    use Importable, SkipsErrors;

  protected $initialCustomerId;
    protected $initials;
    protected $roleId = 3;

    public function __construct()
    {
        $this->initialCustomerId = Customer::max('id') ?? 0;
        $this->initials = $this->getCompanyInitials();
    }

    protected function generatePIN($digits = 4): string
    {
        return str_pad(mt_rand(0, pow(10, $digits) - 1), $digits, '0', STR_PAD_LEFT);
    }

    protected function getCompanyInitials(): string
    {
        $companyName = Auth::user()->company->name ?? Auth::user()->employee->company->name ?? 'VC';
        $words = explode(' ', $companyName);
        return strtoupper(substr($words[0], 0, 1) . ($words[1][0] ?? ''));
    }

    protected function generateCustomerNumber(): string
    {
        $this->initialCustomerId++;
        return $this->initials . 'V' . str_pad($this->initialCustomerId, 5, '0', STR_PAD_LEFT);
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            if ($row->filter()->isEmpty()) continue;
  $name        = $row->get('name');
            $email       = $row->get('email');
            $phonenumber = $row->get('phonenumber');
            $worknumber  = $row->get('worknumber');
            $country     = $row->get('country');
            $city        = $row->get('city');
            $suburb      = $row->get('suburb');
            $street      = $row->get('streetaddress');


            $customer = Customer::firstOrNew(['name' => $name]);

            $customer->company_id     = Auth::user()->employee->company_id ?? null;
            $customer->creator_id     = Auth::user()->id;

            if (!$customer->exists) {
                $customer->customer_number = $this->generateCustomerNumber();
            }

            $customer->email          = $email;
            $customer->phonenumber    = $phonenumber;
            $customer->worknumber     = $worknumber;
            $customer->country        = $country;
            $customer->city           = $city;
            $customer->suburb         = $suburb;
            $customer->street_address = $street;

            $customer->save();
        }
    }

    public function rules(): array
    {
        return [
            // Add validation rules as needed
        ];
    }

    public function chunkSize(): int
    {
        return 50;
    }

    public function batchSize(): int
    {
        return 50;
    }

    public function limit(): int
    {
        return 2500;
    }
}
