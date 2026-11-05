<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remove the columns that linked projects to plugins this application does
     * not ship with (work orders, ERBs and data-collection forms).
     */
    public function up(): void
    {
        if (! Schema::hasTable('projects')) {
            return;
        }

        if (Schema::hasColumn('projects', 'work_order_id')) {
            $this->dropConstraint('projects_work_order_id_unique');
            $this->dropConstraint('projects_work_order_id_foreign');
        }

        if (Schema::hasColumn('projects', 'erb_id')) {
            $this->dropConstraint('projects_erb_id_foreign');
        }

        $columns = array_values(array_filter(
            ['work_order_id', 'erb_id'],
            fn (string $column): bool => Schema::hasColumn('projects', $column),
        ));

        if ($columns === []) {
            return;
        }

        Schema::table('projects', function (Blueprint $table) use ($columns): void {
            $table->dropColumn($columns);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('projects')) {
            return;
        }

        Schema::table('projects', function (Blueprint $table): void {
            if (! Schema::hasColumn('projects', 'work_order_id')) {
                $table->unsignedBigInteger('work_order_id')->nullable()->after('code');
            }

            if (! Schema::hasColumn('projects', 'erb_id')) {
                $table->unsignedBigInteger('erb_id')->nullable()->after('work_order_id');
            }
        });
    }

    /**
     * Drop a foreign key or index when the database still has it.
     */
    private function dropConstraint(string $name): void
    {
        try {
            Schema::table('projects', function (Blueprint $table) use ($name): void {
                if (str_ends_with($name, '_foreign')) {
                    $table->dropForeign($name);
                } else {
                    $table->dropUnique($name);
                }
            });
        } catch (Throwable) {
            // The constraint was already removed by an earlier migration.
        }
    }
};
