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
        Schema::create('citas', function (Blueprint $table) {
            $table->id('id_cita');
            $table->foreignId('paciente_id')->nullable()->constrained('pacientes', 'id_paciente')->onDelete('set null');
            $table->foreignId('doctor_id')->nullable()->constrained('doctores', 'id_doctor')->onDelete('set null');
            $table->date('fecha_cita');
            $table->time('hora_cita');
            $table->string('motivo_consulta', 255)->nullable();
            $table->enum('estado', ['Pendiente', 'Completada', 'Cancelada'])->default('Pendiente');
            $table->decimal('monto_consulta', 10, 2)->nullable()->comment('Costo de la consulta');
            $table->boolean('pagada')->default(false)->comment('Indica si la consulta ha sido pagada');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
