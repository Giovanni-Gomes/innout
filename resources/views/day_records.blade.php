<x-app-layout>
    @php
        renderTitle(
            "Registrar Ponto",
            "Mantenhao seu ponto consistente!",
            "fa-solid fa-check"
        );
    @endphp
    @include('components.messages')
    <div class="card">
        <div class="card-header">
            <h3><?= $today ?></h3>
            <p class="mb-0">Os batimentos efetuados hoje</p>
        </div>
        <div class="card-body">
            <div class="d-flex m-5 justify-content-around">
                <span class="record">Entrada 1: {{ $workingHours->time1 ?? '---' }}</span>
                <span class="record">Saída 1: {{ $workingHours->time2 ?? '---' }}</span>
            </div>
            <div class="d-flex m-5 justify-content-around">
                <span class="record">Entrada 2: {{ $workingHours->time3 ?? '---' }}</span>
                <span class="record">Saída 2: {{ $workingHours->time4 ?? '---' }}</span>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-center">
            <a href="point" class="btn btn-success btn-lg">
                <i class="fa-regular fa-circle-check"></i>
                Bater o Ponto
            </a>
        </div>
    </div>

    @if($allowDevTimeSimulation ?? false)
        <form class="mt-5" action="point" method="get">
            @csrf
            <div class="input-group no-border">
                <input type="text" name="forcedTime" class="form-control" placeholder="Informe a hora para simular o batimento (apenas desenvolvimento)">
                <button type="submit" class="btn btn-danger ml-3">
                    Simular Ponto
                </button>
            </div>
        </form>
    @endif
</x-app-layout>
