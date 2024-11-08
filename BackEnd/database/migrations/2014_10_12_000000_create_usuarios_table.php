<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id('id_usuario');
            $table->string('nombre_completo')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('Rol', ['Paciente', 'Doctor', 'Administrador'])->default('Paciente');
            $table->date('Fecha_registro');
            $table->rememberToken();
            $table->timestamps();
        });
        // Insertar datos de prueba
        DB::table('usuarios')->insert([
            [
                'nombre_completo' => 'administrador',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('123456'),
                'Rol' => 'Administrador',
                'Fecha_registro' => '2021-10-12',
            ],
            [
                'nombre_completo' => 'doctor1',
                'email' => 'doctor1@gmail.com',
                'password' => Hash::make('123456'),
                'Rol' => 'Doctor',
                'Fecha_registro' => '2022-02-20',
            ],
            [
                'nombre_completo' => 'doctor2',
                'email' => 'doctor2@gmail.com',
                'password' => Hash::make('123456'),
                'Rol' => 'Doctor',
                'Fecha_registro' => '2022-05-15',
            ],
            [
                'nombre_completo' => 'paciente1',
                'email' => 'paciente1@gmail.com',
                'password' => Hash::make('123456'),
                'Rol' => 'Paciente',
                'Fecha_registro' => '2023-01-10',
            ],
            [
                'nombre_completo' => 'paciente2',
                'email' => 'paciente2@gmail.com',
                'password' => Hash::make('123456'),
                'Rol' => 'Paciente',
                'Fecha_registro' => '2023-03-22',
            ],
        ]);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
