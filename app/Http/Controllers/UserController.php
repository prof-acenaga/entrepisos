<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use MongoDB\Client;

class UserController extends Controller
{
    /**
     * Obtiene todos los usuarios de la base de datos con filtros opcionales de búsqueda parcial.
     * Permite filtrar por edad exacta, rango de edad, y otros campos con búsqueda parcial (like).
     * También permite ordenar los resultados por una columna específica y en orden ascendente o descendente.
     * Devuelve los usuarios encontrados en formato JSON con estado 200 si tiene éxito, o un mensaje de error con estado 404 si no se encuentran recursos.
     * 
     * Respuestas:
     * - 200 OK: Si se encuentran usuarios, devuelve un JSON con los datos.
     * - 404 Not Found: Si no se encuentran usuarios que coincidan con los filtros.
     */
    public function index(Request $request)
    {
        // Inicia la consulta base, excluyendo los usuarios donde removed es true
        $query = User::where('removed', '!=', true);

        // Filtra por parámetros recibidos en la consulta
        $filters = $request->only(['age', 'minAge', 'maxAge']);
        
        // Filtro por 'age' exacto
        if (isset($filters['age'])) {
            $query->where('age', '=', (int) $filters['age']);
        }

        // Filtro por rango de edad si 'minAge' o 'maxAge' están presentes
        if (isset($filters['minAge']) && isset($filters['maxAge'])) {
            $query->whereBetween('age', [(int) $filters['minAge'], (int) $filters['maxAge']]);
        } elseif (isset($filters['minAge'])) {
            $query->where('age', '>=', (int) $filters['minAge']);
        } elseif (isset($filters['maxAge'])) {
            $query->where('age', '<=', (int) $filters['maxAge']);
        }

        // Filtra por otros campos con búsqueda parcial 'like'
        foreach ($request->except(['age', 'minAge', 'maxAge', 'removed', 'sortBy', 'order', 'page', 'search']) as $key => $value) {
            $query->where($key, 'like', "%{$value}%");
        }

        if ($request->has('search')) {
            $query->where(function($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('email', 'like', '%' . $request->search . '%')
                      ->orWhere('surname', 'like', '%' . $request->search . '%');
            });
        }

        // Ordenar resultados si se especifican 'sortBy' y 'order'
        if ($request->has('sortBy')) {
            $sortBy = $request->query('sortBy');
            $order = $request->query('order', 'asc');
            // 'asc' es el valor predeterminado
            // Asegurarse de que el orden sea válido
            if (in_array($order, ['asc', 'desc'])) {
                $query->orderBy($sortBy, $order);
            }
        } else {
            $query->orderBy('name', 'asc');
        }

        $users = $query->get();

        return $users->isEmpty()
            ? response()->json(['mensaje' => 'Recursos no encontrados.'], 404)
            : response()->json($users, 200);
    }

    /**
     * Obtiene un usuario por ID.
     * Devuelve estado 200 si tiene éxito, o 404 si no encuentra el recurso.
     */
    public function view($id)
    {
        $user = User::findOrFail($id);

        if (!$user) {
            return response()->json(['mensaje' => 'Recurso no encontrado'], 404);
        }

        return response()->json($user, 200);
    }

    /**
     * Agrega un nuevo usuario a la base de datos.
     * Devuelve estado 201 si tiene éxito, o 400 en caso de error.
     */
    public function createProcess(Request $request)
    {
        $request->validate([
            'email'=> 'required|email|unique:usuarios,email',
            'dni'=> 'required|unique:usuarios,dni',
            'name' => 'required|string|max:255',
            'surname'=> 'required|string|max:255',
            'age' => 'required|integer|min:18',
        ]);

        try{
            $user = User::create($request->only(['email', 'dni', 'name', 'surname', 'age', ]));

            return response()->json($user, 201);
        } catch(\Exception $e) {
            return response()->json(['mensaje' => 'No se pudo agregar el usuario. Error: '. $e->getMessage()], 400);
        }
    }

    /**
     * Elimina un usuario de manera lógica por ID.
     * Devuelve estado 204 si tiene éxito, o 404 si no encuentra el recurso.
     */
    public function remove($id)
    {
        $user = User::findOrFail($id);

        if (!$user) {
            return response()->json(['mensaje' => 'No se pudo eliminar'], 404);
        }

        $user->removed = true;
        $user->save();

        return response()->json(['mensaje' => 'Usuario eliminado'], 204);
    }

    /**
     * Actualiza el usuario buscado por ID con nuevos datos.
     * Devuelve estado 204 si tiene éxito, o 404 si no encuentra el recurso.
     */
    public function editProcess(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if (!$user) {
            return response()->json(['mensaje' => 'Recurso no encontrado'], 404);
        }

        $user->update($request->all());
        return response()->json($user, 204);
    }
}
