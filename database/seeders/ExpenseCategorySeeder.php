<?php

namespace Database\Seeders;

use App\Models\AccountType;
use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $operating_expense = AccountType::where('name','Operating Expense')->get()->first();
        $cost_of_goods_sold = AccountType::where('name','Cost Of Goods Sold')->get()->first();
        $business_owner_contribution_and_drawing = AccountType::where('name','Business Owner Contribution & Drawing')->get()->first();
        $taxes_payable = AccountType::where('name','Taxes Payable')->get()->first();
        $shareholder_loan = AccountType::where('name','Shareholder Loan')->get()->first();
        $taxes_recoverable_refundable = AccountType::where('name','Taxes Recoverable/Refundable')->get()->first();
        $expense_categories = [
            ['account_type_id' =>  $operating_expense->id, 'name' => 'Accounting Fees'],
            ['account_type_id' =>  $operating_expense->id,'name' => 'Advertising & Promotion'],
            ['account_type_id' =>  $operating_expense->id,'name' => 'Bank Service Charges'],
            ['account_type_id' =>  $operating_expense->id,'name' => 'Computer -  Hardware'],
            ['account_type_id' =>  $operating_expense->id,'name' => 'Computer - Hosting'],
            ['account_type_id' =>  $operating_expense->id,'name' => 'Computer - Internet'],
            ['account_type_id' =>  $operating_expense->id,'name' => 'Computer - Software'],
            ['account_type_id' =>  $operating_expense->id,'name' => 'Depriciation - Expense'],
            ['account_type_id' =>  $operating_expense->id,'name' =>  'Fuel'],
            ['account_type_id' =>  $operating_expense->id,'name' => 'Insurance - Vehicles'],
            ['account_type_id' =>  $operating_expense->id,'name' =>  'Interest Expense'],
            ['account_type_id' =>  $operating_expense->id,'name' => ' Meals & Entertainment'],
            ['account_type_id' =>  $operating_expense->id,'name' => 'Office Supplies'],
            ['account_type_id' =>  $operating_expense->id,'name' => 'Payroll - Employee Benefits'],
            
            ['account_type_id' =>  $operating_expense->id,'name' => 'Payroll -  Salary & Wages'],
            ['account_type_id' =>  $operating_expense->id,'name' => 'Professional fees'],
            ['account_type_id' =>  $operating_expense->id,'name' => 'Rent & Expense'],
            ['account_type_id' =>  $operating_expense->id,'name' => 'Repairs & Maintenance'],
            ['account_type_id' =>  $operating_expense->id,'name' => 'Taxes - Corporate Tax'],
            ['account_type_id' =>  $operating_expense->id,'name' => 'Telephone - Landline'],
            ['account_type_id' =>  $operating_expense->id,'name' => 'Telephone - Wireless'],
            ['account_type_id' =>  $operating_expense->id,'name' => 'Travel Expense'],
            ['account_type_id' =>  $operating_expense->id,'name' => 'Trip Expense'],
            ['account_type_id' =>  $operating_expense->id,'name' =>  'Utilities'],
            ['account_type_id' => $business_owner_contribution_and_drawing ? $business_owner_contribution_and_drawing->id : "",'name' => 'Common Shares'],
            ['account_type_id' => $cost_of_goods_sold ? $cost_of_goods_sold->id : "",'name' => 'Creditor Payment'],
            ['account_type_id' => $shareholder_loan ? $shareholder_loan->id : "",'name' => 'Shareholder Loan'],
            ['account_type_id' => $taxes_payable ? $taxes_payable->id : "",'name' => 'Taxes Payable'],
            ['account_type_id' => $taxes_recoverable_refundable ? $taxes_recoverable_refundable->id : "", 'name' =>  'Taxes Recoverable/Refundable'],
           ];

           ExpenseCategory::insert($expense_categories);
    }
}
