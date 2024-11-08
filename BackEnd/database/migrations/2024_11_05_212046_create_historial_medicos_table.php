<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('historial_medico', function (Blueprint $table) {
            $table->id('id_historial');
            $table->foreignId('paciente_id')->nullable()->constrained('pacientes', 'id_paciente')->onDelete('set null');
            $table->foreignId('doctor_id')->nullable()->constrained('doctores', 'id_doctor')->onDelete('set null');
            $table->date('fecha');
            $table->text('diagnostico')->nullable();
            $table->text('tratamiento')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_medicos');
    }
};
