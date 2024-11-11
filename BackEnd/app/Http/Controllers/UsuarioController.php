<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class UsuarioController extends Controller
{
    //Login del usuario
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $user = Usuario::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Credenciales incorrectas'], 401);
        }

        // Si las credenciales son correctas, generamos un token usando Sanctum (si está configurado)
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Inicio de sesión exitoso',
            'token' => $token,
            'user' => $user
        ], 200);
    }

    // Método de cierre de sesión
    public function logout(Request $request)
    {
        // Revoke the current user's token
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada exitosamente'], 200);
    }

    // Mostrar todos los usuarios
    public function index()
    {
        $usuarios = Usuario::all();
        return response()->json($usuarios);
    }

    // Crear un nuevo usuario
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate(
                [
                    'nombre_completo' => 'required|string|unique:usuarios,nombre_completo|max:255',
                    'email' => 'required|email|unique:usuarios,email',
                    'password' => 'required|string|min:6',
                    'Rol' => ['required', Rule::in(['Paciente', 'Doctor', 'Administrador'])],
                    'Fecha_registro' => 'required|date',
                ],
                // Mensajes personalizados cuando hay un error
                [
                    'nombre_completo.required' => 'El nombre completo es requerido',
                    'nombre_completo.string' => 'El nombre completo debe ser una cadena de texto',
                    'nombre_completo.unique' => 'El nombre completo ya está en uso',
                    'nombre_completo.max' => 'El nombre completo no debe exceder los 255 caracteres',
                    'email.required' => 'El email es requerido',
                    'email.email' => 'El email debe ser una dirección de correo electrónico válida',
                    'email.unique' => 'El email ya está en uso',
                    'password.required' => 'La contraseña es requerida',
                    'password.string' => 'La contraseña debe ser una cadena de texto',
                    'password.min' => 'La contraseña debe tener al menos 6 caracteres',
                    'Rol.required' => 'El rol es requerido',
                    'Rol.in' => 'El rol debe ser Paciente, Doctor o Administrador',
                    'Fecha_registro.required' => 'La fecha de registro es requerida',
                    'Fecha_registro.date' => 'La fecha de registro debe ser una fecha válida',
                ]
            );

            $validatedData['password'] = Hash::make($request->password);

            $usuario = Usuario::create($validatedData);

            return response()->json(['message' => 'Usuario creado exitosamente', 'usuario' => $usuario]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Registrar el error específico en el log
            Log::error('Error de validación al crear usuario: ', $e->errors());

            // Retornar una respuesta con el mensaje de error específico
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            // Registrar otros errores en el log
            Log::error('Error al crear usuario: ' . $e->getMessage());

            // Retornar una respuesta genérica si ocurre otro tipo de error
            return response()->json(['message' => 'Error inesperado al crear usuario'], 500);
        }
    }

    // Mostrar un usuario específico por ID o correo
    public function show($identifier)
    {
        // Intentar encontrar al usuario por ID (si $identifier es numérico) o por correo (si es texto)
        if (is_numeric($identifier)) {
            $usuario = Usuario::find($identifier);
        } else {
            $usuario = Usuario::where('email', $identifier)->first();
        }

        // Verificar si el usuario fue encontrado
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        return response()->json($usuario);
    }

    // Actualizar un usuario
    public function update(Request $request, $id)
    {
        try {
            // Encuentra el usuario por `id_usuario` en lugar de `id`
            $usuario = Usuario::where('id_usuario', $id)->first();

            if (!$usuario) {
                return response()->json(['message' => 'Usuario no encontrado'], 404);
            }

            // Validación de datos con mensajes personalizados
            $validatedData = $request->validate(
                [
                    'nombre_completo' => 'string|unique:usuarios,nombre_completo,' . $usuario->id_usuario . ',id_usuario|max:255',
                    'email' => 'email|unique:usuarios,email,' . $usuario->id_usuario . ',id_usuario',
                    'password' => 'nullable|string|min:6',
                    'Rol' => [Rule::in(['Paciente', 'Doctor', 'Administrador'])],
                    'Fecha_registro' => 'date',
                ],
                [
                    'nombre_completo.string' => 'El nombre completo debe ser una cadena de texto',
                    'nombre_completo.unique' => 'El nombre completo ya está en uso',
                    'nombre_completo.max' => 'El nombre completo no debe exceder los 255 caracteres',
                    'email.email' => 'El email debe ser una dirección de correo electrónico válida',
                    'email.unique' => 'El email ya está en uso',
                    'password.string' => 'La contraseña debe ser una cadena de texto',
                    'password.min' => 'La contraseña debe tener al menos 6 caracteres',
                    'Rol.in' => 'El rol debe ser Paciente, Doctor o Administrador',
                    'Fecha_registro.date' => 'La fecha de registro debe ser una fecha válida',
                ]
            );

            if ($request->filled('password') && strlen($request->password) >= 6) {
                $validatedData['password'] = Hash::make($request->password);
            } else {
                unset($validatedData['password']);
            }

            $usuario->update($validatedData);

            return response()->json(['message' => 'Usuario actualizado exitosamente', 'usuario' => $usuario]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación al actualizar usuario: ', $e->errors());
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error al actualizar usuario: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['message' => 'Error inesperado al actualizar usuario', 'error' => $e->getMessage()], 500);
        }
    }

    // Eliminar un usuario
    public function destroy($id)
    {
        $usuario = Usuario::find($id);

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $usuario->delete();

        return response()->json(['message' => 'Usuario eliminado exitosamente']);
    }
}
