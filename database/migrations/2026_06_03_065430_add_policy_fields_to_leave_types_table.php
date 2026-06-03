<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPolicyFieldsToLeaveTypesTable extends Migration
{
    public function up()
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->string('code')->nullable()->after('name');

            $table->boolean('is_paid')->default(true)->after('entitlement');
            $table->boolean('is_accruable')->default(false)->after('is_paid');
            $table->boolean('requires_medical_report')->default(false)->after('is_accruable');
            $table->boolean('requires_attachment')->default(false)->after('requires_medical_report');
            $table->boolean('carry_forward_allowed')->default(false)->after('requires_attachment');

            $table->decimal('monthly_accrual_rate', 8, 2)->default(0)->after('carry_forward_allowed');
            $table->integer('max_carry_forward_days')->default(0)->after('monthly_accrual_rate');
            $table->integer('max_consecutive_days')->nullable()->after('max_carry_forward_days');

            $table->boolean('requires_hod_approval')->default(true)->after('max_consecutive_days');
            $table->boolean('requires_management_approval')->default(true)->after('requires_hod_approval');

            $table->boolean('active')->default(true)->after('requires_management_approval');
            $table->text('description')->nullable()->after('active');
        });
    }

    public function down()
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropColumn([
                'code',
                'is_paid',
                'is_accruable',
                'requires_medical_report',
                'requires_attachment',
                'carry_forward_allowed',
                'monthly_accrual_rate',
                'max_carry_forward_days',
                'max_consecutive_days',
                'requires_hod_approval',
                'requires_management_approval',
                'active',
                'description',
            ]);
        });
    }
}