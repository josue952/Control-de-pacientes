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
        Schema::create('consultas', function (Blueprint $table) {
            $table->id('id_consulta');
            
            // Relación con la tabla Citas
            $table->foreignId('cita_id')->constrained('citas', 'id_cita')->onDelete('cascade');
            
            // Se captura el paciente y doctor automáticamente de la cita
            $table->foreignId('paciente_id')->constrained('pacientes', 'id_paciente')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('doctores', 'id_doctor')->onDelete('cascade');
        
            // Relación opcional con la tabla Examenes
            $table->foreignId('examen_id')->nullable()->constrained('examenes', 'id_examen')->onDelete('set null');
        
            // Detalles específicos de la consulta
            $table->string('diagnostico', 255);
            $table->string('enfermedad', 255)->nullable();
            $table->text('observaciones')->nullable();
            $table->text('tratamiento')->nullable();
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultas');
    }
};
