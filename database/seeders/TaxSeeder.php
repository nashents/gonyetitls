<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tax;
use App\Models\Account;

class TaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /*
        |--------------------------------------------------------------------------
        | Get Tax Control Account
        |--------------------------------------------------------------------------
        | This should be the ledger account where VAT is posted.
        | Example account names:
        | - VAT Payable
        | - Sales Tax Payable
        |--------------------------------------------------------------------------
        */

        $taxAccount = Account::where('name', 'Value Added Tax')->first();

        if (!$taxAccount) {
            return;
        }

        $taxes = [

            [   
                'user_id' => Null,
                'account_id'   => $taxAccount->id,
                'category'     => 'VAT',
                'name'         => 'Value Added Tax 15.5%',
                'abbreviation' => 'VAT 15.5%',
                'rate' => '15.5',
                'hs_code'      => '99001000',
                'description'  => 'Standard VAT rate of 15.5%',
            ],

            [
                'account_id'   => $taxAccount->id,
                'category'     => 'VAT',
                'name'         => 'Value Added Tax 0%',
                'abbreviation' => 'VAT 0%',
                'rate' => '0',
                'hs_code'      => '99002000',
                'description'  => 'Zero rated VAT',
            ],

            [
                'account_id'   => $taxAccount->id,
                'category'     => 'VAT',
                'name'         => 'Value Added Tax Exempt',
                'abbreviation' => 'VAT Exempt',
                'rate' => '0',
                'hs_code'      => '99003000',
                'description'  => 'VAT exempt supplies',
            ],

        ];

        foreach ($taxes as $tax) {

            Tax::updateOrCreate(
                [
                    'name' => $tax['name'],
                ],
                $tax
            );
        }
    }
}