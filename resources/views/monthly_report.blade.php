<x-app-layout>
    @php
        renderTitle(
            "Relatório Mensal",
            "Acompanhe seu saldo de horas",
            "fa-regular fa-calendar-days"
        );
    @endphp

    @include('components.messages')

    <form method="get" action="{{ route('monthly_report') }}" class="card mb-4">
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
        </div>
    </form>

    <div class="card mb-4">
        <div class="card-body">
            <p class="mb-1"><strong>Período:</strong> {{ sprintf('%02d/%04d', $month, $year) }}</p>
            <p class="mb-1"><strong>Dias úteis no mês:</strong> {{ $weekdays }}</p>
            <p class="mb-1"><strong>Total trabalhado:</strong> {{ getTimeStringFromSeconds($totalWorked) }}</p>
            <p class="mb-1"><strong>Esperado ({{ $weekdays }} × 8h):</strong> {{ getTimeStringFromSeconds($expectedSeconds) }}</p>
            <p class="mb-0">
                <strong>Saldo do mês:</strong>
                @if ($balanceSeconds === 0)
                    —
                @else
                    {{ $balanceSeconds > 0 ? '+' : '−' }}{{ getTimeStringFromSeconds(abs($balanceSeconds)) }}
                @endif
            </p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="mb-0">Detalhe por dia</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>E1</th>
                            <th>S1</th>
                            <th>E2</th>
                            <th>S2</th>
                            <th>Trabalhado</th>
                            <th>Saldo dia</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($days as $day)
                            @php
                                $wh = $day['working_hours'];
                            @endphp
                            <tr class="{{ $day['is_weekend'] ? 'table-secondary' : '' }}">
                                <td>{{ $day['date'] }}</td>
                                <td>{{ $wh?->time1 ?? '—' }}</td>
                                <td>{{ $wh?->time2 ?? '—' }}</td>
                                <td>{{ $wh?->time3 ?? '—' }}</td>
                                <td>{{ $wh?->time4 ?? '—' }}</td>
                                <td>
                                    @if ($wh && $wh->worked_time !== null)
                                        {{ getTimeStringFromSeconds((int) $wh->worked_time) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    @if ($wh)
                                        {{ $wh->getBalance() ?: '—' }}
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
