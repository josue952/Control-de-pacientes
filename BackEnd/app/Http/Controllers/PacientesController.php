<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pacientes;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class PacientesController extends Controller
{
    // Listar todos los pacientes
    public function index()
    {
        $pacientes = Pacientes::with('usuario')->get();
        return response()->json($pacientes);
    }

    // Crear un nuevo paciente
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate(
                [
                    'usuario_id' => 'nullable|exists:usuarios,id_usuario',
                    'fecha_nacimiento' => 'required|date',
                    'genero' => ['required', Rule::in(['Masculino', 'Femenino'])],
                    'edad' => 'required|integer|min:0',
                    'direccion' => 'nullable|string|max:255',
                    'telefono' => 'nullable|string|max:15',
                ],
                // Mensajes personalizados cuando hay un error
                [
                    'usuario_id.exists' => 'El ID del usuario debe existir en la tabla de usuarios',
                    'fecha_nacimiento.required' => 'La fecha de nacimiento es requerida',
                    'fecha_nacimiento.date' => 'La fecha de nacimiento debe ser una fecha válida',
                    'genero.required' => 'El género es requerido',
                    'genero.in' => 'El género debe ser Masculino o Femenino',
                    'edad.required' => 'La edad es requerida',
                    'edad.integer' => 'La edad debe ser un número entero',
                    'edad.min' => 'La edad no puede ser negativa',
                    'direccion.string' => 'La dirección debe ser una cadena de texto',
                    'direccion.max' => 'La dirección no debe exceder los 255 caracteres',
                    'telefono.string' => 'El teléfono debe ser una cadena de texto',
                    'telefono.max' => 'El teléfono no debe exceder los 15 caracteres',
                ]
            );

            $paciente = Pacientes::create($validatedData);
            return response()->json(['message' => 'Paciente creado exitosamente', 'paciente' => $paciente], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación al crear paciente: ', $e->errors());
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error al crear paciente: ' . $e->getMessage());
            return response()->json(['message' => 'Error inesperado al crear paciente'], 500);
        }
    }

    // Mostrar un paciente por ID
    public function show($id)
    {
        $paciente = Pacientes::with('usuario')->find($id);
        if (!$paciente) {
            return response()->json(['error' => 'Paciente no encontrado'], 404);
        }
        return response()->json($paciente);
    }

    // Actualizar un paciente
    public function update(Request $request, $id)
    {
        $paciente = Pacientes::find($id);
        if (!$paciente) {
            return response()->json(['error' => 'Paciente no encontrado'], 404);
        }

        try {
            $validatedData = $request->validate(
                [
                    'usuario_id' => 'nullable|exists:usuarios,id_usuario',
                    'fecha_nacimiento' => 'required|date',
                    'genero' => ['required', Rule::in(['Masculino', 'Femenino'])],
                    'edad' => 'required|integer|min:0',
                    'direccion' => 'nullable|string|max:255',
                    'telefono' => 'nullable|string|max:15',
                ],
                [
                    'usuario_id.exists' => 'El ID del usuario debe existir en la tabla de usuarios',
                    'fecha_nacimiento.required' => 'La fecha de nacimiento es requerida',
                    'fecha_nacimiento.date' => 'La fecha de nacimiento debe ser una fecha válida',
                    'genero.required' => 'El género es requerido',
                    'genero.in' => 'El género debe ser Masculino o Femenino',
                    'edad.required' => 'La edad es requerida',
                    'edad.integer' => 'La edad debe ser un número entero',
                    'edad.min' => 'La edad no puede ser negativa',
                    'direccion.string' => 'La dirección debe ser una cadena de texto',
                    'direccion.max' => 'La dirección no debe exceder los 255 caracteres',
                    'telefono.string' => 'El teléfono debe ser una cadena de texto',
                    'telefono.max' => 'El teléfono no debe exceder los 15 caracteres',
                ]
            );

            $paciente->update($validatedData);
            return response()->json(['message' => 'Paciente actualizado exitosamente', 'paciente' => $paciente]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación al actualizar paciente: ', $e->errors());
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error al actualizar paciente: ' . $e->getMessage());
            return response()->json(['message' => 'Error inesperado al actualizar paciente'], 500);
        }
    }

    // Eliminar un paciente
    public function destroy($id)
    {
        $paciente = Pacientes::find($id);
        if (!$paciente) {
            return response()->json(['error' => 'Paciente no encontrado'], 404);
        }

        $paciente->delete();
        return response()->json(['message' => 'Paciente eliminado']);
    }
}
