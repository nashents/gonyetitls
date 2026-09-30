<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Suppliers journal: a manual adjustment to one vendor's account that isn't
 * a bill, payment or debit note (opening balances, settlement discounts
 * received, balances written back, interest charged, corrections). The
 * payables-side mirror of the debtors journal.
 *
 *  - credit raises what we owe the supplier:   DR contra / CR Accounts Payable
 *  - debit  reduces what we owe the supplier:  DR Accounts Payable / CR contra
 *
 * The AP line carries vendor_id, and the journal feeds the vendor statement
 * (VendorLedgerService) and aged payables, so the creditors subledger keeps
 * agreeing with the AP control account. Debit journals can be allocated to
 * open bills through bill_payments (source = 'supplier_journal'), which
 * reduces the bill balance exactly like a payment would.
 */
class CreateSupplierJournalsTable extends Migration
{
    public function up()
    {
        Schema::create('supplier_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('journal_number')->unique();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('currency_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('exchange_rate', 18, 6)->nullable();
            // 'debit' (decrease what we owe) | 'credit' (increase it)
            $table->string('type');
            // the other side of the entry (discount received, opening balance equity...)
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

            $table->index(['vendor_id', 'currency_id']);
        });

        Schema::table('bill_payments', function (Blueprint $table) {
            $table->foreignId('supplier_journal_id')->nullable()->after('payment_id')->constrained()->nullOnDelete();
        });

        Schema::table('journal_entries', function (Blueprint $table) {
            $table->foreignId('supplier_journal_id')->nullable()->after('debtor_journal_id')->constrained()->nullOnDelete();
        });

        $moduleId = DB::table('modules')->where('slug', 'vendor-statements')->value('id');
        if ($moduleId) {
            // Same audience as debit note management - finance / admins.
            $visibility = DB::table('sub_modules')->where('slug', 'pending-debit-notes')->value('visibility');

            DB::table('sub_modules')->updateOrInsert(
                ['module_id' => $moduleId, 'slug' => 'supplier-journals'],
                [
                    'module_id' => $moduleId,
                    'slug' => 'supplier-journals',
                    'name' => 'Suppliers Journal',
                    'icon' => 'fas fa-book',
                    'route_name' => 'supplier_journals.index',
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
        DB::table('sub_modules')->where('slug', 'supplier-journals')->delete();

        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('supplier_journal_id');
        });

        Schema::table('bill_payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('supplier_journal_id');
        });

        Schema::dropIfExists('supplier_journals');
    }
}
