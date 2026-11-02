<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Plugins\Analytics\Support\AnalyticsDatabase;

return new class extends Migration
{
    public function up(): void
    {
        AnalyticsDatabase::schema()->create('analytics_afl_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('code', 60);
            $table->string('general_status')->nullable();
            $table->string('site_id_a')->nullable();
            $table->string('end_id_a')->nullable();
            $table->string('site_id_b')->nullable();
            $table->string('end_id_b')->nullable();
            $table->string('project')->nullable();
            $table->text('description')->nullable();
            $table->string('supervisor')->nullable();
            $table->string('coordinator')->nullable();
            $table->string('oc_tim')->nullable();
            $table->string('chave_mw')->nullable();
            $table->string('smp_nokia')->nullable();
            $table->text('general_notes')->nullable();
            $table->date('activation_registered_on')->nullable();
            $table->string('boq_status')->nullable();
            $table->date('request_date')->nullable();
            $table->date('created_on')->nullable();
            $table->string('boq_number')->nullable();
            $table->text('boq_notes')->nullable();
            $table->string('access_status')->nullable();
            $table->date('access_request_a')->nullable();
            $table->date('access_approval_a')->nullable();
            $table->date('access_start_a')->nullable();
            $table->date('access_end_a')->nullable();
            $table->string('crq_a')->nullable();
            $table->date('crq_start_a')->nullable();
            $table->date('crq_end_a')->nullable();
            $table->date('access_request_b')->nullable();
            $table->date('access_approval_b')->nullable();
            $table->date('access_start_b')->nullable();
            $table->date('access_end_b')->nullable();
            $table->string('crq_b')->nullable();
            $table->date('crq_start_b')->nullable();
            $table->date('crq_end_b')->nullable();
            $table->text('access_notes')->nullable();
            $table->string('mos_status')->nullable();
            $table->date('mos_planned_on')->nullable();
            $table->date('mos_actual_on')->nullable();
            $table->text('mos_notes')->nullable();
            $table->string('activity_status')->nullable();
            $table->string('activity_responsible_a')->nullable();
            $table->date('activity_planned_start_a')->nullable();
            $table->date('activity_actual_start_a')->nullable();
            $table->date('activity_planned_end_a')->nullable();
            $table->date('activity_actual_end_a')->nullable();
            $table->string('activity_responsible_b')->nullable();
            $table->date('activity_planned_start_b')->nullable();
            $table->date('activity_actual_start_b')->nullable();
            $table->date('activity_planned_end_b')->nullable();
            $table->date('activity_actual_end_b')->nullable();
            $table->text('activity_notes')->nullable();
            $table->string('activation_status')->nullable();
            $table->string('activation_responsible_a')->nullable();
            $table->date('activation_planned_start_a')->nullable();
            $table->date('activation_actual_start_a')->nullable();
            $table->date('activation_planned_end_a')->nullable();
            $table->date('activation_actual_end_a')->nullable();
            $table->string('activation_responsible_b')->nullable();
            $table->date('activation_planned_start_b')->nullable();
            $table->date('activation_actual_start_b')->nullable();
            $table->date('activation_planned_end_b')->nullable();
            $table->date('activation_actual_end_b')->nullable();
            $table->text('activation_notes')->nullable();
            $table->string('documentation_status')->nullable();
            $table->string('documentation_responsible_a')->nullable();
            $table->date('documentation_planned_start_a')->nullable();
            $table->date('documentation_actual_start_a')->nullable();
            $table->date('documentation_planned_end_a')->nullable();
            $table->date('documentation_actual_end_a')->nullable();
            $table->date('documentation_sent_a')->nullable();
            $table->string('documentation_responsible_b')->nullable();
            $table->date('documentation_planned_start_b')->nullable();
            $table->date('documentation_actual_start_b')->nullable();
            $table->date('documentation_planned_end_b')->nullable();
            $table->date('documentation_actual_end_b')->nullable();
            $table->date('documentation_sent_b')->nullable();
            $table->text('documentation_directory')->nullable();
            $table->text('documentation_notes')->nullable();
            $table->string('acceptance_status')->nullable();
            $table->string('acceptance_responsible_a')->nullable();
            $table->date('acceptance_planned_start_a')->nullable();
            $table->date('acceptance_actual_start_a')->nullable();
            $table->date('acceptance_planned_end_a')->nullable();
            $table->date('acceptance_actual_end_a')->nullable();
            $table->string('acceptance_responsible_b')->nullable();
            $table->date('acceptance_planned_start_b')->nullable();
            $table->date('acceptance_actual_start_b')->nullable();
            $table->date('acceptance_planned_end_b')->nullable();
            $table->date('acceptance_actual_end_b')->nullable();
            $table->text('acceptance_notes')->nullable();
            $table->string('billing_status')->nullable();
            $table->decimal('billed_value', 15, 2)->nullable();
            $table->decimal('contracted_value', 15, 2)->nullable();
            $table->decimal('invoice_value', 15, 2)->nullable();
            $table->string('invoice_number')->nullable();
            $table->date('invoice_issued_on')->nullable();
            $table->date('billed_on')->nullable();
            $table->string('service_po')->nullable();
            $table->string('gr_ia')->nullable();
            $table->date('gr_ia_on')->nullable();
            $table->boolean('is_billed')->nullable();
            $table->string('advance_billing')->nullable();
            $table->decimal('billed_percentage', 15, 2)->nullable();
            $table->text('billing_notes')->nullable();
            $table->boolean('os_cancelled')->nullable();
            $table->dateTime('last_changed_at')->nullable();
            $table->string('last_user')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'code']);
            $table->index(['user_id', 'created_on']);
            $table->index(['user_id', 'general_status']);
            $table->index(['user_id', 'billing_status']);
        });
    }

    public function down(): void
    {
        AnalyticsDatabase::schema()->dropIfExists('analytics_afl_records');
    }
};
