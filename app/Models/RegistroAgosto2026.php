<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistroAgosto2026 extends Model
{
    protected $table = 'registro_agosto_2026';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'FECHA' => 'date',
            'FECHA_CIERRE' => 'date',
        ];
    }
}
