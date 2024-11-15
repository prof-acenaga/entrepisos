<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use MongoDB\Client;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::where('removed', '!=', true)->get();
        // return response()->json($departments);

        if ($departments->isEmpty()) {
            return response()->json(['message' => 'No departments found'], 404);
        }
    
        return response()->json($departments, 200);
    }

    /**
     * Obtiene un departamento por ID.
     * Devuelve estado 200 si tiene éxito, o 404 si no encuentra el recurso.
     */
    public function view($id)
    {
        $department = Department::find($id);
        return response()->json($department, 200);
    }

    /**
     * Agrega un nuevo departamento a la base de datos.
     * Devuelve estado 201 si tiene éxito, o 400 en caso de error.
     */
    public function createProcess(Request $request)
    {
        $request->validate([
            'location' => 'required',
            'type' => 'required',
            'district' => 'required'
        ]);

        // return $department->isEmpty()
        // ? response()->json(['mensaje' => 'Recursos no encontrados.'], 404)
        // : response()->json($users, 200);

        try{
            $department = Department::create($request->only(['location', 'type', 'district',]));
            return response()->json($department, 201);
        } catch(\Exception $e) {
            return response()->json(['mensaje' => 'No se pudo agregar el usuario. Error: '. $e->getMessage()], 400);
        }
    }

    /**
     * Elimina un departamento de manera lógica por ID.
     * Devuelve estado 204 si tiene éxito, o 404 si no encuentra el recurso.
     */
    public function remove($id)
    {
        $department = Department::findOrFail($id);

        if (!$department) {
            return response()->json(['mensaje' => 'No se pudo eliminar'], 404);
        }

        $department->removed = true;
        $department->save();

        return response()->json(['mensaje' => 'Vivienda eliminada'], 204);
    }

    /**
     * Actualiza el departamento buscado por ID con nuevos datos.
     * Devuelve estado 204 si tiene éxito, o 404 si no encuentra el recurso.
     */
    public function editProcess(Request $request, $id)
    {
        $department = Department::findOrFail($id);
        if (!$department) {
            return response()->json(['mensaje' => 'Recurso no encontrado'], 404);
        }

        $department->update($request->all());
        return response()->json($department, 204);
    }
}
