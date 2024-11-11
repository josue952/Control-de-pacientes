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
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id('id_paciente'); // unsignedBigInteger
            $table->foreignId('usuario_id')
                ->nullable()
                ->constrained('usuarios', 'id_usuario')
                ->onDelete('set null');
            $table->date('fecha_nacimiento');
            $table->enum('genero', ['Masculino', 'Femenino']);
            $table->integer('edad');
            $table->string('direccion', 255)->nullable();
            $table->string('telefono', 15)->nullable();
            $table->timestamp('fecha_registro')->useCurrent();
            $table->timestamps();

            // Añadir restricción de unicidad en usuario_id
            $table->unique('usuario_id');
        });
        // Insertar datos de prueba en la tabla de pacientes
        DB::table('pacientes')->insert([
            [
                'usuario_id' => 4, // ID del usuario 'paciente1'
                'fecha_nacimiento' => '1990-05-12',
                'genero' => 'Masculino',
                'edad' => 33,
                'direccion' => 'Calle Falsa 123',
                'telefono' => '1234567890',
            ],
            [
                'usuario_id' => 5, // ID del usuario 'paciente2'
                'fecha_nacimiento' => '1985-08-23',
                'genero' => 'Femenino',
                'edad' => 38,
                'direccion' => 'Avenida Siempre Viva 742',
                'telefono' => '0987654321',
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pacientes');
    }
};
