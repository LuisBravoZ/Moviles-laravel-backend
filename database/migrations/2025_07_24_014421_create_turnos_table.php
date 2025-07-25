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
        Schema::create('turnos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nutricionista_id');
            $table->date('fecha');
            $table->time('hora');
            $table->unsignedBigInteger('paciente_id')->nullable(); // null si no está reservado
            $table->enum('estado', ['disponible', 'reservado', 'cancelado'])->default('disponible');
            $table->timestamps();

            $table->foreign('nutricionista_id')->references('id')->on('users');
            $table->foreign('paciente_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turnos');
    }
};
