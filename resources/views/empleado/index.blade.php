@extends('app')

@section('content')
    <main class="relative isolate min-h-screen overflow-hidden">
        <div class="absolute inset-x-0 top-0 -z-10 h-72 bg-[#dce8e3]"></div>
        <div class="absolute -right-24 top-20 -z-10 h-72 w-72 rounded-full bg-[#f3d7a4]/60 blur-3xl"></div>

        <div class="mx-auto flex min-h-screen w-full max-w-5xl flex-col gap-10 px-5 py-8 sm:px-8 lg:px-12">
            <header class="flex items-center justify-between">
                <a href="{{ route('consulta.index') }}" class="flex items-center gap-3 text-[#243238]">
                    <span class="grid h-11 w-11 place-items-center rounded-2xl bg-[#183f45] text-lg font-bold text-[#f9f5ec] shadow-lg shadow-[#183f45]/20">SP</span>
                    <span class="text-sm font-semibold tracking-[0.18em]">SISTEMA DE PUNTOS</span>
                </a>
                <div class="flex items-center gap-3">
                    <span class="hidden rounded-full border border-[#b6cbc4] bg-[#f9f5ec]/70 px-4 py-2 text-xs font-medium text-[#45615e] sm:inline-flex">Consulta de empleados</span>
                    <img src="{{ asset('conectar-logo.svg') }}" alt="Conectar" class="h-[4.5rem] w-auto object-contain" />
                </div>
            </header>

            <section class="grid flex-1 items-start gap-8 pb-12 lg:grid-cols-[0.85fr_1.15fr] lg:items-center">
                <div class="max-w-xl space-y-6">
                    <div class="inline-flex items-center gap-2 rounded-full bg-[#f9f5ec] px-3 py-1.5 text-xs font-semibold uppercase tracking-[0.16em] text-[#c06b3e] shadow-sm">
                        <span class="h-2 w-2 rounded-full bg-[#c06b3e]"></span>
                        Acceso rápido
                    </div>
                    <div class="flex flex-wrap gap-2 text-xs font-semibold text-[#58706c]">
                        <span class="rounded-full border border-[#b6cbc4] bg-white/60 px-3 py-1.5">Hoy: {{ now()->translatedFormat('d \d\e F \d\e Y') }}</span>
                        <span class="rounded-full border border-[#b6cbc4] bg-white/60 px-3 py-1.5">Mes: {{ now()->translatedFormat('F Y') }}</span>
                    </div>
                    <div class="space-y-3">
                        <h1 class="max-w-md text-4xl font-semibold leading-tight tracking-tight text-[#183f45] sm:text-5xl">Consulta los puntos de tu equipo.</h1>
                        <p class="max-w-md text-base leading-7 text-[#58706c]">Ingresa la cédula para consultar la información registrada del empleado.</p>
                    </div>

                    <form action="{{ route('consulta.buscar') }}" method="GET" class="space-y-3 rounded-[1.75rem] border border-white/80 bg-[#f9f5ec] p-3 shadow-xl shadow-[#183f45]/10 sm:flex sm:items-end sm:gap-3 sm:space-y-0">
                        <div class="min-w-0 flex-1 px-3 py-1">
                            <label for="id_tarjet" class="text-xs font-bold uppercase tracking-[0.14em] text-[#78908b]">Cédula</label>
                            <input id="id_tarjet" name="id_tarjet" type="text" value="{{ request('id_tarjet') }}" required placeholder="Ej. 1006007188" class="mt-1 w-full border-0 bg-transparent px-0 py-2 text-lg font-medium text-[#243238] outline-none placeholder:text-[#a7b7b1] focus:ring-0">
                        </div>
                        <button type="submit" class="w-full rounded-2xl bg-[#c06b3e] px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-[#c06b3e]/25 transition hover:bg-[#a95530] focus:outline-none focus:ring-2 focus:ring-[#c06b3e] focus:ring-offset-2 sm:w-auto">Buscar</button>
                        <a href="{{ route('consulta.index') }}" class="block rounded-2xl border border-[#c8d9d3] px-5 py-3.5 text-center text-sm font-bold text-[#45615e] transition hover:border-[#183f45] hover:text-[#183f45] sm:w-auto">Limpiar</a>
                    </form>

                    @if ($errors->any())
                        <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="relative min-h-[22rem] rounded-[2rem] bg-[#183f45] p-6 text-[#f9f5ec] shadow-2xl shadow-[#183f45]/20 sm:p-8">
                    <div class="absolute right-7 top-7 h-16 w-16 rounded-full border border-[#f3d7a4]/40"></div>
                    <div class="absolute right-11 top-11 h-8 w-8 rounded-full bg-[#f3d7a4]"></div>

                    @isset($datos)
                        <div class="relative space-y-7">
                            <div class="flex items-end justify-between gap-4 border-b border-white/15 pb-5">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#a8c4bc]">Resultado</p>
                                    <h2 class="mt-2 text-2xl font-semibold">Datos encontrados</h2>
                                </div>
                                <span class="rounded-full bg-[#c06b3e] px-3 py-1 text-xs font-bold text-white">Activo</span>
                            </div>
                            <dl class="grid gap-5 sm:grid-cols-2">
                                @foreach ($datos as $campo => $valor)
                                    <div class="border-l-2 border-[#f3d7a4] pl-4">
                                        <dt class="text-xs font-bold uppercase tracking-[0.12em] text-[#a8c4bc]">{{ [
                                            'id_tarjet' => 'Cédula',
                                            'city' => 'Ciudad',
                                            'full_name' => 'Nombre completo',
                                            'position' => 'Cargo',
                                        ][$campo] ?? $campo }}</dt>
                                        <dd class="mt-1 text-base font-medium leading-6 text-[#fffaf0]">{{ $valor }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                            <div class="grid grid-cols-3 gap-2 border-t border-white/15 pt-5 sm:gap-3">
                                <div class="min-w-0 rounded-2xl bg-[#c06b3e] p-3 shadow-lg shadow-black/10 sm:p-4">
                                    <dt class="min-h-8 text-[10px] leading-4 text-[#ffe9d8] sm:text-xs">Puntos totales</dt>
                                    <dd class="mt-1 truncate text-xl font-semibold sm:text-2xl">{{ number_format($resumen['total_puntos']) }}</dd>
                                </div>
                                <div class="min-w-0 rounded-2xl bg-white/10 p-3 sm:p-4">
                                    <dt class="min-h-8 text-[10px] leading-4 text-[#a8c4bc] sm:text-xs">Promedio diario</dt>
                                    <dd class="mt-1 truncate text-xl font-semibold sm:text-2xl">{{ number_format($resumen['promedio_diario'], 1) }}</dd>
                                </div>
                                <div class="min-w-0 rounded-2xl bg-white/10 p-3 sm:p-4">
                                    <dt class="min-h-8 text-[10px] leading-4 text-[#a8c4bc] sm:text-xs">Proyección</dt>
                                    <dd class="mt-1 truncate text-xl font-semibold sm:text-2xl">{{ number_format($resumen['puntos_proyectados']) }}</dd>
                                </div>
                            </div>
                            <div class="grid grid-cols-3 gap-2 border-t border-white/15 pt-5 text-xs sm:gap-3 sm:text-sm">
                                <p class="rounded-xl border border-white/10 px-2 py-3"><span class="block text-[10px] leading-4 text-[#a8c4bc] sm:text-xs">Laborables del mes</span><strong class="mt-1 block text-lg">{{ $resumen['dias_habiles'] }}</strong></p>
                                <p class="rounded-xl border border-white/10 px-2 py-3"><span class="block text-[10px] leading-4 text-[#a8c4bc] sm:text-xs">Días trabajados</span><strong class="mt-1 block text-lg">{{ $resumen['dias_liquidados'] }}</strong></p>
                                <p class="rounded-xl border border-[#f3d7a4]/30 bg-[#f3d7a4]/10 px-2 py-3"><span class="block text-[10px] leading-4 text-[#f3d7a4] sm:text-xs">Por liquidar</span><strong class="mt-1 block text-lg text-[#f3d7a4]">{{ $resumen['dias_por_liquidar'] }}</strong></p>
                            </div>
                            <div class="grid gap-3 border-t border-white/15 pt-5 text-sm sm:grid-cols-2">
                                <p class="rounded-xl border border-white/10 px-3 py-3"><span class="block text-[10px] uppercase tracking-[0.12em] text-[#a8c4bc]">Período consultado</span><strong class="mt-1 block">{{ $resumen['mes_actual'] }}</strong></p>
                                <p class="rounded-xl border border-white/10 px-3 py-3"><span class="block text-[10px] uppercase tracking-[0.12em] text-[#a8c4bc]">Última actividad</span><strong class="mt-1 block">{{ $resumen['fecha_corte']?->format('d/m/Y') ?? 'Sin registros' }}</strong></p>
                                <p class="rounded-xl border border-white/10 px-3 py-3 sm:col-span-2"><span class="block text-[10px] uppercase tracking-[0.12em] text-[#a8c4bc]">Días laborables transcurridos</span><strong class="mt-1 block">{{ $resumen['dias_transcurridos'] }}</strong></p>
                            </div>
                            <div class="border-t border-white/15 pt-5">
                                <details class="group">
                                    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 rounded-2xl border border-white/10 bg-white/[0.06] px-4 py-3 outline-none transition hover:bg-white/10 focus:ring-2 focus:ring-[#f3d7a4] [&::-webkit-details-marker]:hidden">
                                        <span>
                                            <span class="block text-xs font-bold uppercase tracking-[0.12em] text-[#a8c4bc]">Tipos de trabajo</span>
                                            <span class="mt-1 block text-lg font-semibold">Valor por tipo de trabajo</span>
                                        </span>
                                        <span class="flex shrink-0 items-center gap-2 text-xs text-[#b7cec7]">
                                            {{ $catalogoSubtipos->count() }} códigos
                                            <span class="grid h-7 w-7 place-items-center rounded-full bg-[#f3d7a4] text-base font-bold text-[#183f45] transition group-open:rotate-180">⌄</span>
                                        </span>
                                    </summary>
                                    <div class="mt-3 overflow-hidden rounded-2xl border border-white/10">
                                        @foreach ($catalogoSubtipos as $indice => $fila)
                                            <div class="flex items-center justify-between gap-4 bg-white/[0.06] px-4 py-3 {{ $indice > 0 ? 'border-t border-white/10' : '' }}">
                                                <div class="min-w-0">
                                                    <p class="truncate text-sm font-semibold">{{ $fila['subtipo'] }}</p>
                                                    <p class="mt-0.5 text-xs text-[#b7cec7]">Código: {{ $fila['codigo'] }}</p>
                                                </div>
                                                <p class="shrink-0 text-right text-lg font-semibold {{ $fila['puntos'] > 0 ? 'text-[#f3d7a4]' : 'text-[#b7cec7]' }}">
                                                    {{ $fila['puntos'] }} <span class="text-xs font-normal">pts.</span>
                                                    @if ($fila['puntos'] === 0)
                                                        <span class="block text-[10px] font-normal leading-4 text-[#b7cec7]">Sin puntos asignados</span>
                                                    @endif
                                                </p>
                                            </div>
                                        @endforeach
                                    </div>
                                </details>
                            </div>
                        </div>
                    @elseif (request()->filled('id_tarjet'))
                        <div class="relative flex min-h-[19rem] flex-col items-center justify-center gap-4 text-center">
                            <span class="grid h-14 w-14 place-items-center rounded-full bg-[#c06b3e] text-2xl text-white">!</span>
                            <h2 class="text-2xl font-semibold">No encontramos ese registro</h2>
                            <p class="max-w-xs text-sm leading-6 text-[#b7cec7]">Verifica la cédula e intenta realizar la búsqueda nuevamente.</p>
                        </div>
                    @else
                        <div class="relative flex min-h-[19rem] flex-col justify-end gap-3">
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#a8c4bc]">Panel de consulta</p>
                            <h2 class="max-w-sm text-3xl font-semibold leading-tight">La información que necesitas, en un solo lugar.</h2>
                            <p class="max-w-sm text-sm leading-6 text-[#b7cec7]">Los datos del empleado aparecerán aquí después de realizar una búsqueda.</p>
                        </div>
                    @endisset
                </div>
            </section>
        </div>
    </main>
    <script>
        window.addEventListener('pagehide', function () {
            window.history.replaceState({}, '', @json(route('consulta.index')));
        });
    </script>
