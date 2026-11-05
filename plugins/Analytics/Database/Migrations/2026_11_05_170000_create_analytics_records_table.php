<?php

declare(strict_types=1);

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
        Schema::create('analytics_records', function (Blueprint $table): void {
            $table->id();
            $table->string('oc', 100)->index();
            $table->string('os', 100)->unique();
            $table->date('data_criacao')->nullable()->index();
            $table->string('fornecedor', 120)->index();
            $table->string('fornecedor_fiscalizacao', 120)->nullable();
            $table->string('tipo_projeto', 120)->nullable();
            $table->string('etapa_processo', 120)->nullable()->index();
            $table->string('tipo_elemento', 120)->nullable();
            $table->text('endereco_atendimento')->nullable();
            $table->string('cidade', 120)->nullable();
            $table->char('estado', 2)->nullable();
            $table->string('node_name', 120)->index();
            $table->string('localizacao', 120)->nullable();
            $table->string('operador', 120)->nullable();
            $table->string('status', 255)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_records');
    }
};
