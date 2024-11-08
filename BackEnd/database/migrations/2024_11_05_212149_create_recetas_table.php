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
        Schema::create('recetas', function (Blueprint $table) {
            $table->id('id_receta');
            $table->foreignId('consulta_id')->nullable()->constrained('consultas', 'id_consulta')->onDelete('set null');
            $table->foreignId('medicamento_id')->nullable()->constrained('medicamentos', 'id_medicamento')->onDelete('set null');
            $table->string('dosis_prescrita', 100)->nullable();
            $table->string('duracion', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recetas');
    }
};
