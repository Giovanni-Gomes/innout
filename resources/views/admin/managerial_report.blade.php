<x-app-layout>
    @php
        renderTitle(
            "Relatório Gerencial",
            "Visão consolidada por funcionário",
            "fa-solid fa-chart-line"
        );
    @endphp

    @include('components.messages')

    <form method="get" action="{{ route('admin.managerial.index') }}" class="card mb-4">
        <div class="card-body d-flex flex-wrap align-items-end gap-3 period-filters">
            <div class="period-filter-field">
                <label for="month" class="form-label mb-1">Mês</label>
                <select name="month" id="month" class="form-control period-filter-select">
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" @selected((int) $month === $m)>{{ $m }}</option>
                    @endfor
                </select>
            </div>
            <div class="period-filter-field">
                <label for="year" class="form-label mb-1">Ano</label>
                <select name="year" id="year" class="form-control period-filter-select period-filter-select--year">
                    @for ($y = (int) date('Y'); $y >= 2000; $y--)
                        <option value="{{ $y }}" @selected((int) $year === $y)>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a href="{{ route('admin.managerial.index', array_merge(request()->only(['year', 'month']), ['export' => 'csv'])) }}" class="btn btn-outline-secondary">Exportar CSV</a>
        </div>
    </form>

    <div class="card mb-4">
        <div class="card-header">
            <h3 class="mb-0">Ausências hoje (sem batimento)</h3>
        </div>
        <div class="card-body">
            @if (count($absentToday) === 0)
                <p class="mb-0 text-muted">Todos os funcionários ativos registaram entrada.</p>
            @else
                <ul class="mb-0">
                    @foreach ($absentToday as $name)
                        <li>{{ $name }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <p class="mb-1"><strong>Período:</strong> {{ sprintf('%02d/%04d', $month, $year) }}</p>
            <p class="mb-0"><strong>Referência:</strong> {{ $weekdays }} dias úteis × {{ getTimeStringFromSeconds($dailySeconds) }} = {{ getTimeStringFromSeconds($expectedSeconds) }} esperado por funcionário.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="mb-0">Por funcionário</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Total trabalhado</th>
                            <th>Saldo vs esperado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr>
                                <td>{{ $row['user']->name }}</td>
                                <td>{{ $row['user']->email }}</td>
                                <td>{{ getTimeStringFromSeconds($row['total_seconds']) }}</td>
                                <td>
                                    @if ($row['balance_seconds'] === 0)
                                        —
                                    @else
                                        {{ $row['balance_seconds'] > 0 ? '+' : '−' }}{{ getTimeStringFromSeconds(abs($row['balance_seconds'])) }}
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Nenhum funcionário ativo.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
