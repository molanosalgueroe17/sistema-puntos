<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\PuntoPorCodigo;
use App\Models\RegistroAgosto2026;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class EmpleadoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('empleado.index', [
            'catalogoSubtipos' => $this->catalogoSubtipos(),
        ]);
    }

    public function buscar(Request $request)
    {
        $validated = $request->validate([
            'id_tarjet' => ['required', 'string', 'max:255'],
        ]);

        $columnaImportada = 'id_tarjet;city;full_name;position';
        $empleado = Empleado::where($columnaImportada, 'like', $validated['id_tarjet'].';%')->first();
        $datos = $empleado === null
            ? null
            : array_combine(
                ['id_tarjet', 'city', 'full_name', 'position'],
                str_getcsv($empleado->getRawOriginal($columnaImportada), ';')
            );

        if ($datos !== null) {
            $datos['full_name'] = str_replace('BELTR?N', 'BELTRÁN', $datos['full_name']);
            $datos['position'] = str_replace('?', 'Ó', $datos['position']);
        }

        $hoy = Carbon::today();
        $inicioMes = $hoy->copy()->startOfMonth();
        $actividades = RegistroAgosto2026::where('CEDULA', $validated['id_tarjet'])
            ->whereBetween('FECHA', [$inicioMes->toDateString(), $hoy->toDateString()])
            ->orderBy('FECHA')
            ->get();
        $puntosPorCodigo = PuntoPorCodigo::query()->pluck(';;;')
            ->mapWithKeys(function (string $fila): array {
                $campos = str_getcsv($fila, ';');

                return isset($campos[0], $campos[2]) && is_numeric(trim($campos[2]))
                    ? [trim($campos[0]) => (int) trim($campos[2])]
                    : [];
            });
        $catalogoSubtipos = $this->catalogoSubtipos($puntosPorCodigo);
        $totalPuntos = (int) $actividades->sum(function ($actividad) use ($puntosPorCodigo): int {
            return (int) $actividad->CANTIDAD_ACTIVIDAD * $puntosPorCodigo->get(trim($actividad->CODIGO), 0);
        });
        $puntosPorSubtipo = $actividades->groupBy('SUBTIPO_TRABAJO')
            ->map(function ($filas, string $subtipo) use ($puntosPorCodigo): array {
                $codigo = trim((string) $filas->first()->CODIGO);

                return [
                    'subtipo' => $subtipo,
                    'codigo' => $codigo,
                    'puntos' => $puntosPorCodigo->get($codigo, 0),
                    'actividades' => (int) $filas->sum('CANTIDAD_ACTIVIDAD'),
                ];
            })->values();
        $diasActivos = $actividades->pluck('FECHA')
            ->filter()
            ->map(fn (Carbon $fecha): string => $fecha->toDateString())
            ->unique()
            ->count();
        $fechaCorte = $actividades->max('FECHA');
        $diasHabiles = $this->diasHabilesDelMes($hoy);
        $diasTranscurridos = $this->diasHabilesHasta($hoy);
        $promedioDiario = $diasActivos > 0 ? round($totalPuntos / $diasActivos, 1) : 0;
        $puntosProyectados = $diasTranscurridos > 0
            ? (int) round($totalPuntos / $diasTranscurridos * $diasHabiles)
            : 0;
        $resumen = [
            'total_puntos' => $totalPuntos,
            'promedio_diario' => $promedioDiario,
            'puntos_proyectados' => $puntosProyectados,
            'dias_habiles' => $diasHabiles,
            'dias_liquidados' => $diasActivos,
            'dias_por_liquidar' => max($diasHabiles - $diasActivos, 0),
            'fecha_corte' => $fechaCorte,
            'fecha_hoy' => $hoy,
            'mes_actual' => $hoy->translatedFormat('F Y'),
            'dias_transcurridos' => $diasTranscurridos,
        ];

        return view('empleado.index', compact('datos', 'resumen', 'puntosPorSubtipo', 'catalogoSubtipos'));
    }

    private function catalogoSubtipos(?Collection $puntosPorCodigo = null): Collection
    {
        $puntosPorCodigo ??= PuntoPorCodigo::query()->pluck(';;;')
            ->mapWithKeys(function (string $fila): array {
                $campos = str_getcsv($fila, ';');

                return isset($campos[0], $campos[2]) && is_numeric(trim($campos[2]))
                    ? [trim($campos[0]) => (int) trim($campos[2])]
                    : [];
            });

        $catalogo = RegistroAgosto2026::query()
            ->selectRaw('MIN(SUBTIPO_TRABAJO) AS SUBTIPO_TRABAJO, CODIGO')
            ->whereNotNull('SUBTIPO_TRABAJO')
            ->where('SUBTIPO_TRABAJO', '!=', '')
            ->groupBy('CODIGO')
            ->orderBy('SUBTIPO_TRABAJO')
            ->get()
            ->map(function ($fila) use ($puntosPorCodigo): array {
                $codigo = trim((string) $fila->CODIGO);

                return [
                    'subtipo' => $fila->SUBTIPO_TRABAJO,
                    'codigo' => $codigo,
                    'puntos' => $puntosPorCodigo->get($codigo, 0),
                ];
            })
            ->filter(fn (array $fila) => $puntosPorCodigo->has($fila['codigo']) && $fila['puntos'] > 0)
            ->values();

        return $catalogo
            ->groupBy('subtipo')
            ->filter(fn (Collection $filas): bool => $filas->pluck('puntos')->unique()->count() > 1)
            ->flatten(1)
            ->values();
    }

    private function diasHabilesDelMes(Carbon $fecha): int
    {
        return $this->contarDiasLaborables($fecha->copy()->startOfMonth(), $fecha->copy()->endOfMonth());
    }

    private function diasHabilesHasta(Carbon $fecha): int
    {
        return $this->contarDiasLaborables($fecha->copy()->startOfMonth(), $fecha);
    }

    private function contarDiasLaborables(Carbon $inicio, Carbon $fin): int
    {
        $dias = 0;

        while ($inicio->lte($fin)) {
            if ($this->esDiaLaborable($inicio)) {
                $dias++;
            }

            $inicio->addDay();
        }

        return $dias;
    }

    private function esDiaLaborable(Carbon $fecha): bool
    {
        return ! $fecha->isSunday() && ! in_array(
            $fecha->toDateString(),
            $this->festivosColombia($fecha->year),
            true
        );
    }

    private function festivosColombia(int $ano): array
    {
        $festivosFijos = [
            "$ano-01-01",
            "$ano-05-01",
            "$ano-07-20",
            "$ano-08-07",
            "$ano-12-08",
            "$ano-12-25",
        ];
        $pascua = Carbon::create($ano, 3, 21)->addDays(easter_days($ano));
        $festivosMovibles = [
            Carbon::create($ano, 1, 6),
            Carbon::create($ano, 3, 19),
            Carbon::create($ano, 6, 29),
            Carbon::create($ano, 8, 15),
            Carbon::create($ano, 10, 12),
            Carbon::create($ano, 11, 1),
            Carbon::create($ano, 11, 11),
            $pascua->copy()->addDays(43),
            $pascua->copy()->addDays(64),
            $pascua->copy()->addDays(71),
        ];

        return array_merge(
            $festivosFijos,
            array_map(function (Carbon $fecha): string {
                return ($fecha->isMonday() ? $fecha : $fecha->next(Carbon::MONDAY))->toDateString();
            }, $festivosMovibles)
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('empleado.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        // $datosEmpleado = request()->all();
        $datosEmpleado = request()->except('_token');

        return response()->json($datosEmpleado);
    }

    /**
     * Display the specified resource.
     */
    public function show(Empleado $empleado)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Empleado $empleado)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Empleado $empleado)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Empleado $empleado)
    {
        //
    }
}
