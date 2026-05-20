<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Currency;
use App\Models\AccountType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */


     public function accountNumber(){
       
            $initials = "3B";

            $account = Account::orderBy('id', 'desc')->first();

        if (!$account) {
            $account_number =  $initials .'A'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $account->id + 1;
            $account_number =  $initials .'A'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $account_number;


    }

    public function run()
    {
        
        
        $cash_bank = AccountType::where('name','Cash & Bank')->get()->first();
        $income = AccountType::where('name','Income')->get()->first();
        $uncategorized_income = AccountType::where('name','Uncategorized Income')->get()->first();
        $gain_on_foreign_exchange = AccountType::where('name','Gain On Foreign Exchange')->get()->first();
        $operating_expense = AccountType::where('name','Operating Expense')->get()->first();
        $sales_taxes = AccountType::where('name','Sales Taxes')->get()->first();
        $discount = AccountType::where('name','Discount')->get()->first();
        $cost_of_goods_sold = AccountType::where('name','Cost Of Goods Sold')->get()->first();
        
        $business_owner_contribution_and_drawing = AccountType::where('name','Business Owner Contribution & Drawing')->get()->first();
        $retained_earnings_profit = AccountType::where('name','Retained Earnings: Profit')->get()->first();
        
        $other_short_term_asset = AccountType::where('name','Other Short-Term Asset')->get()->first();
        $expected_payments_from_customers = AccountType::where('name','Expected Payments from Customers')->get()->first();
        $expected_payments_to_vendors = AccountType::where('name','Expected Payments to Vendors')->get()->first();
        $due_to_you_and_other_business_owners = AccountType::where('name','Due to You & Other Business Owners')->get()->first();
        $other_short_term_liability = AccountType::where('name','Other Short-Term Liability')->get()->first();
        $customer_prepayments_customer_credits = AccountType::where('name','Customer Prepayments & Customer Credits')->get()->first();
        
        $loss_on_foreign_exchange = AccountType::where('name','Loss On Foreign Exchange')->get()->first();
        $uncategorized_expense = AccountType::where('name','Uncategorized Expense')->get()->first();
        $payroll_expense = AccountType::where('name','Payroll Expense')->get()->first();
        
        
        $currency = Currency::where('name','USD')->first();

        $accounts = [    
            [ 'currency_id' => Null, 'account_type_group_id' =>  $operating_expense->account_type_group->id, 'account_type_id' =>  $operating_expense->id, 'name' => 'Accounting Fees','abbreviation' => '','rate' => '' , 'description' => 'Accounting or bookkeeping services for your business.', 'hs_code' => ''],
            [ 'currency_id' => Null, 'account_type_group_id' =>  $operating_expense->account_type_group->id, 'account_type_id' =>  $operating_expense->id,'name' => 'Advertising & Promotion','abbreviation' => '','rate' => '', 'description' => 'Advertising or other costs to promote your business. Includes web or social media promotion.', 'hs_code' => ''],
            [ 'currency_id' => Null, 'account_type_group_id' =>  $operating_expense->account_type_group->id, 'account_type_id' =>  $operating_expense->id,'name' => 'Bank Service Charges','abbreviation' => '','rate' => '', 'description' => 'Fees you pay to your bank like transaction charges, monthly charges, and overdraft charges.', 'hs_code' => ''],
            [ 'currency_id' => Null, 'account_type_group_id' =>  $operating_expense->account_type_group->id, 'account_type_id' =>  $operating_expense->id,'name' => 'Computer -  Hardware','abbreviation' => '','rate' => '', 'description' => 'Desktop or laptop computers, mobile phones, tablets, and accessories used for your business.', 'hs_code' => ''],
            [ 'currency_id' => Null, 'account_type_group_id' =>  $operating_expense->account_type_group->id, 'account_type_id' =>  $operating_expense->id,'name' => 'Computer - Hosting','abbreviation' => '','rate' => '', 'description' => 'Fees for web storage and access, like hosting your business website or app.', 'hs_code' => ''],
            [ 'currency_id' => Null, 'account_type_group_id' =>  $operating_expense->account_type_group->id, 'account_type_id' =>  $operating_expense->id,'name' => 'Computer - Internet','abbreviation' => '','rate' => '', 'description' => 'Internet services for your business. Does not include data access for mobile devices.', 'hs_code' => ''],
            [ 'currency_id' => Null, 'account_type_group_id' =>  $operating_expense->account_type_group->id, 'account_type_id' =>  $operating_expense->id,'name' => 'Computer - Software','abbreviation' => '','rate' => '', 'description' => 'Apps, software, and web or cloud services you use for business on your mobile phone or computer. Includes one-time purchases and subscriptions.', 'hs_code' => ''],
            [ 'currency_id' => Null, 'account_type_group_id' =>  $operating_expense->account_type_group->id, 'account_type_id' =>  $operating_expense->id,'name' => 'Depriciation - Expense','abbreviation' => '','rate' => '', 'description' => 'The amount of depreciation reported on the income statement. To determine the appropriate depreciation expense for a period, estimate the average useful life of the fixed asset in question. For example, if you paid $1,800 for a computer for your business, and the computer has an estimated useful life of 3 years, each monthly income statement will report a depreciation expense of $50 for 36 months.', 'hs_code' => ''],
            [ 'currency_id' => Null, 'account_type_group_id' =>  $operating_expense->account_type_group->id, 'account_type_id' =>  $operating_expense->id,'name' =>  'Fuel','abbreviation' => '','rate' => '', 'description' => '', 'hs_code' => ''],
            [ 'currency_id' => Null, 'account_type_group_id' =>  $operating_expense->account_type_group->id, 'account_type_id' =>  $operating_expense->id,'name' => 'Insurance - Vehicles','abbreviation' => '','rate' => '', 'description' => 'Insurance for the vehicle you use for business.', 'hs_code' => ''],
            [ 'currency_id' => Null, 'account_type_group_id' =>  $operating_expense->account_type_group->id, 'account_type_id' =>  $operating_expense->id,'name' =>  'Interest Expense','abbreviation' => '','rate' => '', 'description' => 'Interest your business pays on loans and other forms of debt, including business loans, credit cards, mortgages, and vehicle payments.', 'hs_code' => ''],
            [ 'currency_id' => Null, 'account_type_group_id' =>  $operating_expense->account_type_group->id, 'account_type_id' =>  $operating_expense->id,'name' => ' Meals & Entertainment','abbreviation' => '','rate' => '', 'description' => 'Food and beverages you consume while conducting business, with clients and vendors, or entertaining customers.', 'hs_code' => ''],
            [ 'currency_id' => Null, 'account_type_group_id' =>  $operating_expense->account_type_group->id, 'account_type_id' =>  $operating_expense->id,'name' => 'Office Supplies','abbreviation' => '','rate' => '', 'description' => 'Office supplies and services for your business office or space.', 'hs_code' => ''],
            [ 'currency_id' => Null, 'account_type_group_id' =>  $operating_expense->account_type_group->id, 'account_type_id' =>  $operating_expense->id,'name' => 'Payroll - Employee Benefits','abbreviation' => '','rate' => '', 'description' => '', 'hs_code' => ''],
            [ 'currency_id' => Null, 'account_type_group_id' =>  $operating_expense->account_type_group->id, 'account_type_id' =>  $operating_expense->id,'name' => 'Payroll -  Salary & Wages','abbreviation' => '','rate' => '', 'description' => '', 'hs_code' => ''],
            [ 'currency_id' => Null, 'account_type_group_id' =>  $operating_expense->account_type_group->id, 'account_type_id' =>  $operating_expense->id,'name' => 'Professional fees','abbreviation' => '','rate' => '', 'description' => 'Fees you pay to consultants or trained professionals for advice or services related to your business.', 'hs_code' => ''],
            [ 'currency_id' => Null, 'account_type_group_id' =>  $operating_expense->account_type_group->id, 'account_type_id' =>  $operating_expense->id,'name' => 'Rent Expense','abbreviation' => '','rate' => '', 'description' => 'Costs to rent or lease property or furniture for your business office space. Does not include equipment rentals.', 'hs_code' => ''],
            [ 'currency_id' => Null, 'account_type_group_id' =>  $operating_expense->account_type_group->id, 'account_type_id' =>  $operating_expense->id,'name' => 'Repairs & Maintenance','abbreviation' => '','rate' => '','abbreviation' => '','rate' => '', 'description' => "Repair and upkeep of property or equipment, as long as the repair doesn't add value to the property. Does not include replacements or upgrades.", 'hs_code' => ''],
            [ 'currency_id' => Null, 'account_type_group_id' =>  $operating_expense->account_type_group->id, 'account_type_id' =>  $operating_expense->id,'name' => 'Taxes - Corporate Tax','abbreviation' => '','rate' => '', 'description' => 'A tax imposed on corporations. If your business is incorporated, you may be required to pay this tax depending on your jurisdiction.', 'hs_code' => ''],
            [ 'currency_id' => Null, 'account_type_group_id' =>  $operating_expense->account_type_group->id, 'account_type_id' =>  $operating_expense->id,'name' => 'Telephone - Landline','abbreviation' => '','rate' => '', 'description' => 'Land line phone services for your business.', 'hs_code' => ''],
            [ 'currency_id' => Null, 'account_type_group_id' =>  $operating_expense->account_type_group->id, 'account_type_id' =>  $operating_expense->id,'name' => 'Telephone - Wireless','abbreviation' => '','rate' => '', 'description' => 'Mobile phone services for your business.', 'hs_code' => ''],
            [ 'currency_id' => Null, 'account_type_group_id' =>  $operating_expense->account_type_group->id, 'account_type_id' =>  $operating_expense->id,'name' => 'Travel Expense','abbreviation' => '','rate' => '', 'description' => 'Transportation and travel costs while traveling for business. Does not include daily commute costs.', 'hs_code' => ''],
            [ 'currency_id' => Null, 'account_type_group_id' =>  $operating_expense->account_type_group->id, 'account_type_id' =>  $operating_expense->id,'name' =>  'Utilities','abbreviation' => '','rate' => '', 'description' => 'Utilities (electricity, water, etc.) for your business office. Does not include phone use.', 'hs_code' => ''],
            [ 'currency_id' => Null, 'account_type_group_id' =>  $loss_on_foreign_exchange->account_type_group->id, 'account_type_id' =>  $loss_on_foreign_exchange->id,'name' =>  'Loss on Foreign Exchange','abbreviation' => '','rate' => '', 'description' => "Foreign exchange losses happen when the exchange rate between your business's home currency and a foreign currency transaction changes and results in a loss. This can happen in the time between a transaction being entered in Wave and being settled, for example, between when you send an invoice and when your customer pays it. This can affect foreign currency invoice payments, bill payments, or foreign currency held in your bank account.", 'hs_code' => ''],
            [ 'currency_id' => Null, 'account_type_group_id' =>  $uncategorized_expense->account_type_group->id,'account_type_id' =>  $uncategorized_expense->id,'name' =>  'Uncategorized Expense','abbreviation' => '','rate' => '', 'description' => "A business cost you haven't categorized yet. Categorize it now to keep your records accurate.", 'hs_code' => ''],
            [ 'currency_id' => Null, 'account_type_group_id' =>  $payroll_expense->account_type_group->id,'account_type_id' =>  $payroll_expense->id,'name' =>  'Payroll – Employee Benefits','abbreviation' => '','rate' => '', 'description' => "Federal and provincial/state deductions taken from an employee's pay, like employment insurance. These are usually described as line deductions on the pay stub.", 'hs_code' => ''],
            [ 'currency_id' => Null, 'account_type_group_id' =>  $payroll_expense->account_type_group->id,'account_type_id' =>  $payroll_expense->id,'name' =>  "Payroll – Salary & Wages",'abbreviation' => '','rate' => '', 'description' => "Wages and salaries paid to your employees.", 'hs_code' => ''],

            //Cost of goods sold 
            [ 'currency_id' => Null, 'account_type_group_id' =>  $cost_of_goods_sold->account_type_group->id,'account_type_id' => $cost_of_goods_sold ? $cost_of_goods_sold->id : "",'name' => 'Creditor Payment','abbreviation' => '','rate' => '', 'description' => '', 'hs_code' => ''],
            [ 'currency_id' => Null, 'account_type_group_id' =>  $cost_of_goods_sold->account_type_group->id,'account_type_id' => $cost_of_goods_sold ? $cost_of_goods_sold->id : "",'name' => 'Merchandise','abbreviation' => '','rate' => '', 'description' => '', 'hs_code' => ''],
            [ 'currency_id' => Null, 'account_type_group_id' =>  $cost_of_goods_sold->account_type_group->id,'account_type_id' => $cost_of_goods_sold ? $cost_of_goods_sold->id : "",'name' => 'Trip Expense','abbreviation' => '','rate' => '', 'description' => '', 'hs_code' => ''],
            
            //equity
            [ 'currency_id' => Null, 'account_type_group_id' =>  $business_owner_contribution_and_drawing->account_type_group->id,'account_type_id' => $business_owner_contribution_and_drawing ? $business_owner_contribution_and_drawing->id : "", 'name' => 'Common Shares', 'abbreviation' => '', 'rate' => '', 'description' => "Common shares of a corporation can be issued to business owners, investors, and employees.", 'hs_code' => ''],
            [ 'currency_id' => Null, 'account_type_group_id' =>  $retained_earnings_profit->account_type_group->id,'account_type_id' => $retained_earnings_profit ? $retained_earnings_profit->id : "",'name' => 'Retained Earnings/Deficit','abbreviation' => '','rate' => '', 'description' => "Retained earnings are the total net income your business has earned from its first day to the current date, minus any dividends you've already distributed. If the amount of retained earnings is negative, report it as a deficit.", 'hs_code' => ''],
        
            //Income

            [ 'currency_id' => Null, 'account_type_group_id' =>  $income->account_type_group->id,'account_type_id' => $income ? $income->id : "",'name' => 'Sales','abbreviation' => '','rate' => '', 'description' => "Payments from your customers for products and services that your business sold.", 'hs_code' => ''],
            [ 'currency_id' => Null, 'account_type_group_id' =>  $discount->account_type_group->id,'account_type_id' => $discount ? $discount->id : "",'name' => 'Sales Discounts','abbreviation' => '','rate' => '', 'description' => "Sales Discounts reduce the price of a product or service that is offered to customers. Discounts reduce your Sales, which is why it will be shown as a negative amount on the Profit & Loss report.", 'hs_code' => ''],
            [ 'currency_id' => Null, 'account_type_group_id' =>  $uncategorized_income->account_type_group->id,'account_type_id' => $uncategorized_income ? $uncategorized_income->id : "",'name' => 'Uncategorized Income','abbreviation' => '','rate' => '', 'description' => "Income you haven't categorized yet. Categorize it now to keep your records accurate.", 'hs_code' => ''],
            [ 'currency_id' => Null, 'account_type_group_id' =>  $gain_on_foreign_exchange->account_type_group->id,'account_type_id' => $gain_on_foreign_exchange ? $gain_on_foreign_exchange->id : "",'name' => 'Gain on Foreign Exchange','abbreviation' => '','rate' => '', 'description' => "Foreign exchange gains happen when the exchange rate between your business's home currency and a foreign currency transaction changes and results in a gain. This can happen in the time between a transaction being entered in Wave and being settled, for example, between when you send an invoice and when your customer pays it. This can affect foreign currency invoice payments, bill payments, or foreign currency held in your bank account.", 'hs_code' => ''],
           
        //    //Liabilities
            [ 'currency_id' => Null, 'account_type_group_id' =>  $expected_payments_to_vendors->account_type_group->id,'account_type_id' => $expected_payments_to_vendors ? $expected_payments_to_vendors->id : "", 'name' =>  'Accounts Payable','abbreviation' => '','rate' => '','description' => "", 'hs_code' => ''],
            [ 'currency_id' => Null, 'account_type_group_id' =>  $sales_taxes->account_type_group->id,'account_type_id' => $sales_taxes ? $sales_taxes->id : "",'name' => 'Value Added Tax','abbreviation' => 'VAT','rate' => '','description' => '', 'hs_code' => '' ],
            [ 'currency_id' => Null, 'account_type_group_id' =>  $due_to_you_and_other_business_owners->account_type_group->id,'account_type_id' => $due_to_you_and_other_business_owners ? $due_to_you_and_other_business_owners->id : "",'name' => 'Shareholder Loan','abbreviation' => '','rate' => '', 'description' => "A loan made to your business by individual shareholders or partnerships.", 'hs_code' => ''],
            [ 'currency_id' => Null, 'account_type_group_id' =>  $other_short_term_liability->account_type_group->id,'account_type_id' => $other_short_term_liability ? $other_short_term_liability->id : "",'name' => 'Taxes Payable','abbreviation' => '','rate' => '', 'description' => "The money your business owes in taxes at the federal, state/provincial, or municipal level.", 'hs_code' => ''],
            [ 'currency_id' => Null, 'account_type_group_id' =>  $customer_prepayments_customer_credits->account_type_group->id,'account_type_id' => $customer_prepayments_customer_credits ? $customer_prepayments_customer_credits->id : "",'name' => 'Customer Deposits','abbreviation' => '','rate' => '', 'description' => "This account records advance payments received from customers before services are rendered or before invoices are applied. These deposits represent a liability to the company until they are allocated to outstanding invoices or refunded. Once the funds are applied to an invoice, they are reclassified as revenue or offset against accounts receivable.", 'hs_code' => ''],
            
            //Group Assets
            [ 'currency_id' => $currency->id, 'account_type_group_id' =>  $cash_bank->account_type_group->id,'account_type_id' => $cash_bank->id, 'name' => 'Cash on Hand','abbreviation' => '','rate' => '','description' => "Cash you haven’t deposited in the bank. Add your bank and credit card accounts to accurately categorize transactions that aren`t cash.", 'hs_code' => ''],
            [ 'currency_id' => Null, 'account_type_group_id' =>  $expected_payments_from_customers->account_type_group->id,'account_type_id' => $expected_payments_from_customers->id, 'name' => 'Accounts Receivable','abbreviation' => '','rate' => '', 'description' => "", 'hs_code' => ''],
            [  'currency_id' => Null, 'account_type_group_id' =>  $other_short_term_asset->account_type_group->id,'account_type_id' => $other_short_term_asset->id, 'name' => 'Taxes Recoverable/Refundable','abbreviation' => '','rate' => '','description' => "A tax is recoverable if you can deduct the tax you've paid from the tax you've collected. Many sales taxes are considered recoverable.", 'hs_code' => ''],
            [  'currency_id' => Null, 'account_type_group_id' =>  $other_short_term_asset->account_type_group->id,'account_type_id' => $other_short_term_asset->id, 'name' => 'Sample Account','abbreviation' => '','rate' => '','description' => "", 'hs_code' => ''],
           
        ];

           Account::insert($accounts);
    }
}
