<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('nokia_site_rejections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nokia_site_id')->constrained('nokia_sites')->cascadeOnDelete();
            $table->string('checklist_field', 50);
            $table->string('reason', 255);
            $table->unsignedTinyInteger('return_days');
            $table->boolean('requires_field_return')->default(false);
            $table->timestamp('started_at');
            $table->timestamps();
        });

        DB::table('nokia_sites')
            ->whereNotNull('rejection_reason')
            ->orderBy('id')
            ->each(function (object $site): void {
                DB::table('nokia_site_rejections')->insert([
                    'nokia_site_id' => $site->id,
                    'checklist_field' => 'checklist_fam_entrega',
                    'reason' => $site->rejection_reason,
                    'return_days' => $site->rejection_return_days ?? 7,
                    'requires_field_return' => (bool) ($site->rejection_requires_field_return ?? false),
                    'started_at' => $site->rejection_started_at ?? now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        Schema::table('nokia_sites', function (Blueprint $table) {
            $table->dropColumn([
                'rejection_reason',
                'rejection_return_days',
                'rejection_started_at',
                'rejection_requires_field_return',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nokia_sites', function (Blueprint $table) {
            $table->string('rejection_reason', 255)->nullable()->after('interruption_started_at');
            $table->unsignedTinyInteger('rejection_return_days')->nullable()->after('rejection_reason');
            $table->timestamp('rejection_started_at')->nullable()->after('rejection_return_days');
            $table->boolean('rejection_requires_field_return')->default(false)->after('rejection_started_at');
        });

        Schema::dropIfExists('nokia_site_rejections');
    }
};
