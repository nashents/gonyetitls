<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Debtors journal: a manual adjustment to one customer's account that isn't
 * an invoice, receipt or credit note (bad debt write-offs, discounts
 * allowed, opening balances, interest, corrections).
 *
 *  - debit  raises what the customer owes:   DR Accounts Receivable / CR contra
 *  - credit reduces what the customer owes:  DR contra / CR Accounts Receivable
 *
 * The AR line carries customer_id, and the journal feeds the customer
 * statement (CustomerLedgerService) and aged receivables, so the debtors
 * subledger keeps agreeing with the AR control account. Credit journals can
 * be allocated to open invoices through invoice_payments
 * (source = 'debtor_journal'), which reduces the invoice balance exactly
 * like a receipt would.
 */
class CreateDebtorJournalsTable extends Migration
{
    public function up()
    {
        Schema::create('debtor_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('journal_number')->unique();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('currency_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('exchange_rate', 18, 6)->nullable();
            // 'debit' (increase what they owe) | 'credit' (decrease it)
            $table->string('type');
            // the other side of the entry (bad debts, discount allowed, opening balance equity...)
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->decimal('amount', 18, 2);
            $table->string('reference')->nullable();
            $table->text('description')->nullable();
            // 'posted' | 'voided'
            $table->string('status')->default('posted');
            $table->foreignId('voided_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('voided_at')->nullable();
            $table->text('void_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['customer_id', 'currency_id']);
        });

        Schema::table('invoice_payments', function (Blueprint $table) {
            $table->foreignId('debtor_journal_id')->nullable()->after('customer_fuel_supply_id')->constrained()->nullOnDelete();
        });

        Schema::table('journal_entries', function (Blueprint $table) {
            $table->foreignId('debtor_journal_id')->nullable()->after('customer_fuel_supply_id')->constrained()->nullOnDelete();
        });

        $moduleId = DB::table('modules')->where('slug', 'customer-statements')->value('id');
        if ($moduleId) {
            // Same audience as credit note management - finance / admins.
            $visibility = DB::table('sub_modules')->where('slug', 'pending-credit-notes')->value('visibility');

            DB::table('sub_modules')->updateOrInsert(
                ['module_id' => $moduleId, 'slug' => 'debtor-journals'],
                [
                    'module_id' => $moduleId,
                    'slug' => 'debtor-journals',
                    'name' => 'Debtors Journal',
                    'icon' => 'fas fa-book',
                    'route_name' => 'debtor_journals.index',
                    'sort_order' => 20,
                    'is_active' => true,
                    'badge_key' => null,
                    'visibility' => $visibility,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down()
    {
        DB::table('sub_modules')->where('slug', 'debtor-journals')->delete();

        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('debtor_journal_id');
        });

        Schema::table('invoice_payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('debtor_journal_id');
        });

        Schema::dropIfExists('debtor_journals');
    }
}
