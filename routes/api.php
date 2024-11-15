<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DepartmentController;
//Ruta para cuando requiere que este iniciado sesion
// Route::get('/usuarios', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::get('/usuarios', [UserController::class, 'index']);
// Ruta para mostrar un usuario específico (show)
Route::get('/usuarios/{id}', [UserController::class, 'view']);

// Ruta para procesar el formulario de creación de usuario (createProcess)
Route::post('/usuarios', [UserController::class, 'createProcess']);

// Ruta para eliminar un usuario de forma lógica (remove)
Route::delete('/usuario/{id}', [UserController::class, 'remove']);

// Ruta para procesar la edición de un usuario (editProcess)
Route::put('/usuarios/{id}/editar', [UserController::class, 'editProcess']);

//rutas departments

Route::get('/viviendas', [DepartmentController::class, 'index']);
Route::get('/viviendas/{id}', [DepartmentController::class, 'view']);
Route::post('/viviendas', [DepartmentController::class, 'createProcess']);
Route::delete('/viviendas/{id}', [DepartmentController::class, 'remove']);
Route::put('/viviendas/{id}/editar', [DepartmentController::class, 'editProcess']);
