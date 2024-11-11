<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('doctores', function (Blueprint $table) {
            $table->id('id_doctor');
            $table->foreignId('usuario_id')
                ->nullable()
                ->constrained('usuarios', 'id_usuario')
                ->onDelete('set null');
            $table->string('especialidad', 100);
            $table->string('telefono', 15)->nullable();
            $table->timestamp('fecha_registro')->useCurrent();
            $table->timestamps();

            // Añadir restricción de unicidad en usuario_id
            $table->unique('usuario_id');
        });
        // Insertar datos de prueba en la tabla de doctores
        DB::table('doctores')->insert([
            [
                'usuario_id' => 2, // ID del usuario 'doctor1'
                'especialidad' => 'Cardiología',
                'telefono' => '1122334455',
            ],
            [
                'usuario_id' => 3, // ID del usuario 'doctor2'
                'especialidad' => 'Pediatría',
                'telefono' => '5544332211',
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctores');
    }
};
