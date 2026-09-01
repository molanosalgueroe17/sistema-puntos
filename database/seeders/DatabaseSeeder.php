<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. CREAR EL USUARIO ADMINISTRADOR PARA EL LOGIN
        // Si el usuario ya existe, lo actualiza; si no, lo crea de forma segura
        User::updateOrCreate(
            ['email' => 'admin@sistemapuntos.com'], // Tu correo de ingreso
            [
                'name' => 'Administrador Puntos',
                'password' => Hash::make('admin12345'), // Tu contraseña segura
            ]
        );

        // 2. LLAMAR AUTOMÁTICAMENTE AL IMPORTADOR DE LAS 30.000 FILAS
        $this->call([
            ImportarActividadesSeeder::class,
        ]);
    }
}
