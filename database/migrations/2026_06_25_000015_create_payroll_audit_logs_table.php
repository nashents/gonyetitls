<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollAuditLogsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('payroll_audit_logs')) {
            return;
        }


        Schema::create('payroll_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('company_id')->unsigned()->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');

            // Subject — can be a run, a salary line, a reversal, a config change, etc.
            $table->bigInteger('payroll_run_id')->unsigned()->nullable();
            $table->foreign('payroll_run_id')->references('id')->on('payroll_runs')->onDelete('set null');
            $table->bigInteger('payroll_id')->unsigned()->nullable()
                ->comment('Legacy payrolls.id — for auditing old payroll records');
            $table->string('auditable_type')->nullable()
                ->comment('Fully qualified model class name');
            $table->bigInteger('auditable_id')->unsigned()->nullable();

            $table->string('action')
                ->comment('created, calculated, validated, approved, locked, exported, posted, reversed, amended, config_changed, rate_changed');

            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->text('reason')->nullable()
                ->comment('Mandatory for reversals and amendments');

            $table->timestamp('performed_at');

            // No updated_at / soft deletes — audit logs are immutable
            $table->timestamp('created_at')->useCurrent();

            $table->index(['payroll_run_id', 'action']);
            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['user_id', 'performed_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('payroll_audit_logs');
    }
}
