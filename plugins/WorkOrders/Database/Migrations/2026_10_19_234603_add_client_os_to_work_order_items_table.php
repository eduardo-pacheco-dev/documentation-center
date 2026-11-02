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
        Schema::table('work_order_items', function (Blueprint $table) {
            $table->string('client_os', 60)->nullable()->after('description');
        });

        DB::table('work_order_items')
            ->whereNotNull('work_order_client_order_id')
            ->orderBy('id')
            ->each(function (object $item): void {
                $number = DB::table('work_order_client_orders')
                    ->where('id', $item->work_order_client_order_id)
                    ->value('number');

                if ($number !== null) {
                    DB::table('work_order_items')
                        ->where('id', $item->id)
                        ->update(['client_os' => $number]);
                }
            });

        Schema::table('work_order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('work_order_client_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_order_items', function (Blueprint $table) {
            $table->foreignId('work_order_client_order_id')
                ->nullable()
                ->after('work_order_id')
                ->constrained()
                ->nullOnDelete();
        });

        Schema::table('work_order_items', function (Blueprint $table) {
            $table->dropColumn('client_os');
        });
    }
};
