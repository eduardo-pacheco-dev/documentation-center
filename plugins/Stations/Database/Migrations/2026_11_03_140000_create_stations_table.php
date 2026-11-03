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
        Schema::create('stations', function (Blueprint $table) {
            $table->id();
            $table->string('site_id', 100)->index();
            $table->string('endereco_id', 100)->nullable();
            $table->string('tipo_de_elemento', 100)->nullable();
            $table->string('tecnologia', 50)->nullable();
            $table->string('tipo_de_conexao', 100)->nullable();
            $table->string('classificacao', 100)->nullable();
            $table->string('detentor_area', 150)->nullable();
            $table->string('tipo_de_infra', 100)->nullable();
            $table->string('tipo_de_ev', 100)->nullable();
            $table->string('tipo_de_logradouro', 50)->nullable();
            $table->string('logradouro')->nullable();
            $table->string('numero', 30)->nullable();
            $table->string('complemento', 150)->nullable();
            $table->string('bairro', 150)->nullable();
            $table->string('municipio', 150)->nullable();
            $table->string('estado', 2)->nullable();
            $table->string('cep', 20)->nullable();
            $table->string('regional', 100)->nullable();
            $table->string('latitude', 30)->nullable();
            $table->string('longitude', 30)->nullable();
            $table->string('status', 50)->nullable()->index();
            $table->string('tipo_da_torre', 100)->nullable();
            $table->string('station_id', 100)->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stations');
    }
};
