<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\{
    ToCollection,
    SkipsEmptyRows,
    WithHeadingRow,
    SkipsOnError,
    WithValidation,
    WithChunkReading,
    WithBatchInserts,
    // ShouldQueue
};
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;

class VendorsImport implements
    ToCollection,
    SkipsEmptyRows,
    WithHeadingRow,
    SkipsOnError,
    WithValidation,
    WithChunkReading,
    WithBatchInserts
    // ShouldQueue
{
    use Importable, SkipsErrors;

    protected $initialVendorId;
    protected $initials;
    protected $roleId = 3;

    public function __construct()
    {
        $this->initialVendorId = Vendor::max('id') ?? 0;
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

    protected function generateVendorNumber(): string
    {
        $this->initialVendorId++;
        return $this->initials . 'V' . str_pad($this->initialVendorId, 5, '0', STR_PAD_LEFT);
    }

    public function collection(Collection $rows)
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
            // Sage Intacct VENDORID carried by the bulk-from-Sage template.
            $customRef   = $row->get('custom_ref');


            $vendor = Vendor::firstOrNew(['name' => $name]);

            $vendor->company_id     = Auth::user()->employee->company_id ?? null;
            $vendor->creator_id     = Auth::user()->id;

            if (!$vendor->exists) {
                $vendor->vendor_number = $this->generateVendorNumber();
            }

            $vendor->email          = $email;
            $vendor->phonenumber    = $phonenumber;
            $vendor->worknumber     = $worknumber;
            $vendor->country        = $country;
            $vendor->city           = $city;
            $vendor->suburb         = $suburb;
            $vendor->street_address = $street;

            // A custom_ref means the record came from Sage and already exists
            // there — mirror it into sage_intacct_id and mark it synced so it
            // is never pushed back as a duplicate.
            if (!empty($customRef)) {
                $vendor->custom_ref       = $customRef;
                $vendor->sage_intacct_id  = $customRef;
                $vendor->sage_sync_status = 'synced';
            }

            $vendor->save();
        }
    }

    public function rules(): array
    {
        return [
            // Define field-level rules if needed
        ];
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
