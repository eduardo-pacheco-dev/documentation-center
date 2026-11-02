<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('colaboradores', function (Blueprint $table) {
            $table->string('contract_regime', 30)->nullable()->after('name');
            $table->string('regional', 50)->nullable()->after('contract_regime');
            $table->string('uf', 2)->nullable()->after('regional');
            $table->string('pis', 20)->nullable()->after('uf');
            $table->string('cnpj', 20)->nullable()->after('document');
            $table->string('rg', 30)->nullable()->after('cnpj');
            $table->string('rg_issuer', 50)->nullable()->after('rg');
            $table->date('birth_date')->nullable()->after('rg_issuer');
            $table->string('mother_name')->nullable()->after('birth_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('colaboradores', function (Blueprint $table) {
            $table->dropColumn([
                'contract_regime',
                'regional',
                'uf',
                'pis',
                'cnpj',
                'rg',
                'rg_issuer',
                'birth_date',
                'mother_name',
            ]);
        });
    }
};
