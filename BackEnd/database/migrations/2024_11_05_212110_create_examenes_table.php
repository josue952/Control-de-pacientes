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
        Schema::create('examenes', function (Blueprint $table) {
            $table->id('id_examen');
        
            // Información general del examen
            $table->string('tipo_examen', 100); // Tipo de examen, e.g., "Sangre", "Orina"
            $table->text('descripcion')->nullable(); // Descripción general del examen
            $table->date('fecha_examen')->nullable(); // Fecha de realización del examen
        
            // Resultados y observaciones del examen
            $table->json('resultados')->nullable(); // Resultados en formato JSON para flexibilidad
            $table->text('observaciones')->nullable(); // Observaciones adicionales sobre el examen
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('examenes');
    }
};
