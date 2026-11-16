<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gonyeti's own billing of its client companies (the monthly license fee on
 * companies.plan / companies.fee). Nothing here touches a client's ledger:
 * these are Gonyeti's invoices TO the client, not the client's sales invoices.
 */
class CreateSubscriptionBillingTables extends Migration
{
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->boolean('billing_enabled')->default(false);
            $table->unsignedTinyInteger('billing_day')->default(1);          // day of month the invoice is raised
            $table->unsignedSmallInteger('billing_due_days')->default(7);    // payment terms
            $table->unsignedSmallInteger('billing_reminder_days')->default(3); // days between reminders, 0 = none
            $table->date('billing_start_date')->nullable();                  // no invoice is raised before this date
            // Used on Gonyeti's own (Admin) company row only: how clients should pay, printed on every license invoice.
            $table->text('billing_payment_details')->nullable();
        });

        Schema::create('subscription_billing_contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phonenumber')->nullable();
            $table->boolean('send_email')->default(true);
            $table->boolean('send_whatsapp')->default(true);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('subscription_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('invoice_number')->nullable()->unique();
            $table->string('period', 7);                 // YYYY-MM of the billing date
            $table->date('period_start');
            $table->date('period_end');
            $table->date('issue_date');
            $table->date('due_date');
            $table->string('plan')->nullable();
            $table->string('description')->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->decimal('balance', 15, 2)->default(0);
            $table->string('status', 20)->default('unpaid')->index(); // unpaid | partial | paid | cancelled
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->unsignedTinyInteger('send_attempts')->default(0);
            $table->timestamp('last_reminder_at')->nullable();
            $table->unsignedSmallInteger('reminder_count')->default(0);
            $table->unsignedBigInteger('generated_by')->nullable();  // null = raised automatically
            $table->timestamps();

            // One invoice per company per billing month - this is what makes the daily run idempotent.
            $table->unique(['company_id', 'period']);
        });

        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('subscription_invoice_id')->index();
            $table->date('date');
            $table->decimal('amount', 15, 2);
            $table->string('method')->nullable();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->timestamps();
        });

        Schema::create('subscription_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscription_invoice_id')->index();
            $table->string('type', 20);      // invoice | reminder | receipt
            $table->string('channel', 20);   // email | whatsapp
            $table->string('recipient')->nullable();
            $table->string('status', 20);    // sent | failed
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('subscription_notifications');
        Schema::dropIfExists('subscription_payments');
        Schema::dropIfExists('subscription_invoices');
        Schema::dropIfExists('subscription_billing_contacts');

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['billing_enabled', 'billing_day', 'billing_due_days', 'billing_reminder_days', 'billing_start_date', 'billing_payment_details']);
        });
    }
}
