<?php

use App\Http\Controllers\EmpleadoController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [EmpleadoController::class, 'index'])->name('consulta.index');

/* Route::get('/empleado', function () {
    return view('empleado.index');
});
Route::get('/empleado/create',[EmpleadoController::class, 'create']);
*/
Route::get('/consulta', [EmpleadoController::class, 'index'])->name('consulta.index');
Route::get('/consulta/buscar', [EmpleadoController::class, 'buscar'])->name('consulta.buscar');

Route::resource('empleado', EmpleadoController::class);

require __DIR__.'/auth.php';
require __DIR__.'/settings.php';

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
