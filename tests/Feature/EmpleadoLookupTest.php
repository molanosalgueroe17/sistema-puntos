<?php

use App\Models\Empleado;
use App\Models\PuntoPorCodigo;
use App\Models\RegistroAgosto2026;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

beforeEach(function () {
    Carbon::setTestNow('2026-08-28');

    Schema::create('datos_personas', function (Blueprint $table) {
        $table->id();
        $table->string('id_tarjet;city;full_name;position');
    });
    Schema::create('puntos_por_codigo', function (Blueprint $table) {
        $table->string(';;;');
    });
    Schema::create('registro_agosto_2026', function (Blueprint $table) {
        $table->string('CODIGO');
        $table->string('FECHA');
        $table->integer('CEDULA');
        $table->integer('CANTIDAD_ACTIVIDAD');
        $table->string('SUBTIPO_TRABAJO');
        $table->integer('CUENTA')->nullable();
        $table->integer('IDORDEN_DE_TRABAJO')->nullable();
    });
});

afterEach(function () {
    Carbon::setTestNow();
});

it('displays all data for the matching card id', function () {
    Empleado::query()->insert([
        'id_tarjet;city;full_name;position' => '123;IBAGUE;Ana Lopez;Analista',
    ]);
    PuntoPorCodigo::query()->insert([
        ';;;' => 'ARTOCNR;ARREGLO;30;',
    ]);
    PuntoPorCodigo::query()->insert([
        ';;;' => 'NANCOL;SUMI ANDAMIOS;61;',
    ]);
    PuntoPorCodigo::query()->insert([
        ';;;' => 'ARTOC96;ARREGLO;40;',
    ]);
    PuntoPorCodigo::query()->insert([
        ';;;' => 'OTRO30;OTRO TIPO;30;',
    ]);
    PuntoPorCodigo::query()->insert([
        ';;;' => 'OTRO50;OTRO TIPO;50;',
    ]);
    PuntoPorCodigo::query()->insert([
        ';;;' => 'NOCODE;TRABAJO SIN PUNTOS;0;',
    ]);
    RegistroAgosto2026::query()->insert([
        'CODIGO' => 'ARTOCNR',
        'FECHA' => '2026-08-03',
        'CEDULA' => 123,
        'CANTIDAD_ACTIVIDAD' => 2,
        'SUBTIPO_TRABAJO' => 'Arreglo Bidireccional',
        'CUENTA' => 34415538,
        'IDORDEN_DE_TRABAJO' => 475840660,
    ]);
    RegistroAgosto2026::query()->insert([
        'CODIGO' => 'ARTOC96',
        'FECHA' => '2026-08-03',
        'CEDULA' => 123,
        'CANTIDAD_ACTIVIDAD' => 1,
        'SUBTIPO_TRABAJO' => 'Arreglo Bidireccional',
        'CUENTA' => 400727,
        'IDORDEN_DE_TRABAJO' => 8316069,
    ]);
    RegistroAgosto2026::query()->insert([
        'CODIGO' => 'OTRO30',
        'FECHA' => '2026-08-03',
        'CEDULA' => 123,
        'CANTIDAD_ACTIVIDAD' => 1,
        'SUBTIPO_TRABAJO' => 'Otro tipo de trabajo',
    ]);
    RegistroAgosto2026::query()->insert([
        'CODIGO' => 'OTRO50',
        'FECHA' => '2026-08-03',
        'CEDULA' => 123,
        'CANTIDAD_ACTIVIDAD' => 1,
        'SUBTIPO_TRABAJO' => 'Otro tipo de trabajo',
    ]);
    RegistroAgosto2026::query()->insert([
        'CODIGO' => 'NANCOL',
        'FECHA' => '2026-08-04',
        'CEDULA' => 999,
        'CANTIDAD_ACTIVIDAD' => 1,
        'SUBTIPO_TRABAJO' => 'Suministro de andamios',
    ]);
    RegistroAgosto2026::query()->insert([
        'CODIGO' => 'NOCODE',
        'FECHA' => '2026-08-05',
        'CEDULA' => 998,
        'CANTIDAD_ACTIVIDAD' => 1,
        'SUBTIPO_TRABAJO' => 'Trabajo sin puntos',
    ]);
    RegistroAgosto2026::query()->insert([
        'CODIGO' => 'MISSING',
        'FECHA' => '2026-08-06',
        'CEDULA' => 997,
        'CANTIDAD_ACTIVIDAD' => 1,
        'SUBTIPO_TRABAJO' => 'Trabajo sin código configurado',
    ]);

    $response = $this->get(route('consulta.buscar', ['id_tarjet' => '123']));

    $response->assertOk()
        ->assertSee('id_tarjet')
        ->assertSee('123')
        ->assertSee('Cédula')
        ->assertSee('Ciudad')
        ->assertSee('IBAGUE')
        ->assertSee('Nombre completo')
        ->assertSee('Ana Lopez')
        ->assertSee('Cargo')
        ->assertSee('Analista');
    $response->assertSee('100')
        ->assertSee('Puntos totales')
        ->assertSee('Tipos de trabajo')
        ->assertSee('Arreglo Bidireccional')
        ->assertSee('Código: ARTOCNR')
        ->assertSee('30')
        ->assertSee('pts.')
        ->assertSee('40')
        ->assertDontSee('Cuenta: 34415538')
        ->assertDontSee('Orden: 475840660')
        ->assertDontSee('Cuenta: 400727')
        ->assertDontSee('Orden: 8316069')
        ->assertSee('Limpiar')
        ->assertDontSee('Suministro de andamios')
        ->assertDontSee('Trabajo sin puntos')
        ->assertDontSee('Sin puntos asignados')
        ->assertDontSee('Trabajo sin código configurado');

    expect($response->viewData('catalogoSubtipos')->pluck('puntos')->all())
        ->toHaveCount(4)
        ->toEqualCanonicalizing([30, 40, 30, 50]);
});

it('replaces the corrupted question mark in the position', function () {
    Empleado::query()->insert([
        'id_tarjet;city;full_name;position' => 'CARD-456;IBAGUE;Ana Lopez;TECNICO CON MOTO OPERACI?N',
    ]);

    $response = $this->get(route('consulta.buscar', ['id_tarjet' => 'CARD-456']));

    $response->assertOk()
        ->assertSee('TECNICO CON MOTO OPERACIÓN')
        ->assertDontSee('TECNICO CON MOTO OPERACI?N');
});

it('corrects the corrupted accent in the employee full name', function () {
    Empleado::query()->insert([
        'id_tarjet;city;full_name;position' => '1006124287;IBAGUE;BELTR?N GUERRERO KEVIN ANDREU;TECNICO',
    ]);

    $response = $this->get(route('consulta.buscar', ['id_tarjet' => '1006124287']));

    $response->assertOk()
        ->assertSee('BELTRÁN GUERRERO KEVIN ANDREU')
        ->assertDontSee('BELTR?N GUERRERO KEVIN ANDREU');
});

it('shows a not found message when the card id does not exist', function () {
    $response = $this->get(route('consulta.buscar', ['id_tarjet' => 'UNKNOWN']));

    $response->assertOk()
        ->assertSee('No encontramos ese registro');
});

it('counts every date with a registered activity, including Sundays and holidays', function () {
    Empleado::query()->insert([
        'id_tarjet;city;full_name;position' => '123;IBAGUE;Ana Lopez;Analista',
    ]);
    RegistroAgosto2026::query()->insert([
        'CODIGO' => 'ARTOCNR',
        'FECHA' => '2026-08-03',
        'CEDULA' => 123,
        'CANTIDAD_ACTIVIDAD' => 1,
        'SUBTIPO_TRABAJO' => 'Arreglo',
    ]);
    RegistroAgosto2026::query()->insert([
        'CODIGO' => 'ARTOCNR',
        'FECHA' => '2026-08-07',
        'CEDULA' => 123,
        'CANTIDAD_ACTIVIDAD' => 1,
        'SUBTIPO_TRABAJO' => 'Arreglo',
    ]);
    RegistroAgosto2026::query()->insert([
        'CODIGO' => 'ARTOCNR',
        'FECHA' => '2026-08-09',
        'CEDULA' => 123,
        'CANTIDAD_ACTIVIDAD' => 1,
        'SUBTIPO_TRABAJO' => 'Arreglo',
    ]);

    $response = $this->get(route('consulta.buscar', ['id_tarjet' => '123']));

    $response->assertOk();
    expect($response->viewData('resumen'))
        ->dias_habiles->toBe(24)
        ->dias_liquidados->toBe(3)
        ->dias_transcurridos->toBe(22)
        ->mes_actual->toBe('agosto 2026');
});

it('shows the employee lookup page at the app root', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('Consulta los puntos de tu equipo.')
        ->assertSee('Buscar');
});
