<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Doctores;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class DoctoresController extends Controller
{
    // Listar todos los doctores
    public function index()
    {
        $doctores = Doctores::with('usuario')->get();
        return response()->json($doctores);
    }

    // Crear un nuevo doctor
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate(
                [
                    'usuario_id' => 'nullable|exists:usuarios,id_usuario',
                    'especialidad' => 'required|string|max:100',
                    'telefono' => 'nullable|string|max:15',
                ],
                // Mensajes personalizados de error
                [
                    'usuario_id.exists' => 'El ID del usuario debe existir en la tabla de usuarios',
                    'especialidad.required' => 'La especialidad es requerida',
                    'especialidad.string' => 'La especialidad debe ser una cadena de texto',
                    'especialidad.max' => 'La especialidad no debe exceder los 100 caracteres',
                    'telefono.string' => 'El teléfono debe ser una cadena de texto',
                    'telefono.max' => 'El teléfono no debe exceder los 15 caracteres',
                ]
            );

            $doctor = Doctores::create($validatedData);
            return response()->json(['message' => 'Doctor creado exitosamente', 'doctor' => $doctor], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación al crear doctor: ', $e->errors());
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error al crear doctor: ' . $e->getMessage());
            return response()->json(['message' => 'Error inesperado al crear doctor'], 500);
        }
    }

    // Mostrar un doctor por ID
    public function show($id)
    {
        $doctor = Doctores::with('usuario')->find($id);
        if (!$doctor) {
            return response()->json(['error' => 'Doctor no encontrado'], 404);
        }
        return response()->json($doctor);
    }

    // Actualizar un doctor
    public function update(Request $request, $id)
    {
        $doctor = Doctores::find($id);
        if (!$doctor) {
            return response()->json(['error' => 'Doctor no encontrado'], 404);
        }

        try {
            $validatedData = $request->validate(
                [
                    'usuario_id' => 'nullable|exists:usuarios,id_usuario',
                    'especialidad' => 'required|string|max:100',
                    'telefono' => 'nullable|string|max:15',
                ],
                [
                    'usuario_id.exists' => 'El ID del usuario debe existir en la tabla de usuarios',
                    'especialidad.required' => 'La especialidad es requerida',
                    'especialidad.string' => 'La especialidad debe ser una cadena de texto',
                    'especialidad.max' => 'La especialidad no debe exceder los 100 caracteres',
                    'telefono.string' => 'El teléfono debe ser una cadena de texto',
                    'telefono.max' => 'El teléfono no debe exceder los 15 caracteres',
                ]
            );

            $doctor->update($validatedData);
            return response()->json(['message' => 'Doctor actualizado exitosamente', 'doctor' => $doctor]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación al actualizar doctor: ', $e->errors());
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error al actualizar doctor: ' . $e->getMessage());
            return response()->json(['message' => 'Error inesperado al actualizar doctor'], 500);
        }
    }

    // Eliminar un doctor
    public function destroy($id)
    {
        $doctor = Doctores::find($id);
        if (!$doctor) {
            return response()->json(['error' => 'Doctor no encontrado'], 404);
        }

        $doctor->delete();
        return response()->json(['message' => 'Doctor eliminado con éxito']);
    }
}
