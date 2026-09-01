<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImportarActividadesSeeder extends Seeder
{
    public function run(): void
    {
        $archivoPath = database_path('Datos_agosto.csv');

        if (! file_exists($archivoPath)) {
            $this->command->error('No se encontró el archivo Datos_agosto.csv en database/');

            return;
        }

        $file = fopen($archivoPath, 'r');
        fgetcsv($file, 0, ';'); // Ignorar cabeceras

        $insertarBloque = [];
        $contador = 0;

        $this->command->info('Procesando las filas de Datos_agosto.csv de forma segura...');

        while (($fila = fgetcsv($file, 0, ';')) !== false) {
            if (count($fila) >= 4) {
                $insertarBloque[] = [
                    'CODIGO' => $fila[0] ?? null,
                    'FECHA' => ! empty($fila[1]) ? date('Y-m-d', strtotime(str_replace('/', '-', $fila[1]))) : null,
                    'ALIADO' => $fila[2] ?? null,
                    'MOVIL' => $fila[3] ?? null,
                    'NOMBRE' => $fila[4] ?? null,
                    'CEDULA' => $fila[5] ?? null,
                    'ELITE' => $fila[6] ?? null,
                    'CUENTA' => $fila[7] ?? null,
                    'T_USER' => $fila[8] ?? null,
                    'IDORDEN_DE_TRABAJO' => $fila[9] ?? null,
                    'CODCIUDAD' => $fila[10] ?? null,
                    'CODNODO' => $fila[11] ?? null,
                    'AREA' => $fila[12] ?? null,
                    'CECO' => $fila[13] ?? null,
                    'CLASE' => $fila[14] ?? null,
                    'FACTURADO' => isset($fila[15]) ? intval($fila[15]) : 0,
                    'CANTIDAD_ACTIVIDAD' => isset($fila[16]) ? intval($fila[16]) : 0,
                    'COMUNIDAD' => $fila[17] ?? null,
                    'MODULO' => $fila[18] ?? null,
                    'CARPETA' => $fila[19] ?? null,
                    'TIPO_TRABAJO' => $fila[20] ?? null,
                    'SUBTIPO_TRABAJO' => $fila[21] ?? null,
                    'FECHA_CIERRE' => ! empty($fila[22]) ? date('Y-m-d', strtotime(str_replace('/', '-', $fila[22]))) : null,
                ];

                $contador++;

                if (count($insertarBloque) === 1000) {
                    DB::table('registro_agosto_2026')->insert($insertarBloque);
                    $insertarBloque = [];
                }
            }
        }

        if (count($insertarBloque) > 0) {
            DB::table('registro_agosto_2026')->insert($insertarBloque);
        }

        fclose($file);
        $this->command->info("¡Éxito total! Se importaron con precisión {$contador} filas en MySQL.");
    }
}
