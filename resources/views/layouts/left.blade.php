<aside class="sidebar">
    <nav class="menu mt-3">
        <ul class="nav-list">
            <li class="nav-item">
                <a href="{{ route('dashboard') }}">
                    <i class="fa-solid fa-check mr-2"></i>
                    Registar Ponto
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('monthly_report') }}">
                    <i class="fa-regular fa-calendar-days mr-2"></i>
                    Relatório Mensal
                </a>
            </li>
            @if(auth()->user()->is_admin)
            <li class="nav-item">
                <a href="{{ route('admin.managerial.index') }}">
                    <i class="fa-solid fa-chart-line mr-2"></i>
                    Relatório Gerencial
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.users.index') }}">
                    <i class="fa-solid fa-users mr-2"></i>
                    Usuários
                </a>
            </li>
            @endif
        </ul>
    </nav>
    <div class="sidebar-widgets">
        <div class="sidebar-widget">
            <i class="fa-regular fa-hourglass icon text-indigo-700"></i>
            <div class="info">
                <span class="main text-primary" <?= $activeClock == 'workedInterval' ? 'active-clock' : '' ?>>
                    <?= $workedInterval ?>
                </span>
                <span class="label text-muted">Horas Trabalhadas</span>
            </div>
        </div>
        <div class="division my-3"></div>
        <div class="sidebar-widget">
            <i class="fa-regular fa-clock icon text-red-600"></i>
            <div class="info">
                <span class="main text-danger" <?= $activeClock == 'exitTime' ? 'active-clock' : '' ?>>
                    <?= $exitTime ?>
                </span>
                <span class="label text-muted">Hora de Saída</span>
            </div>
        </div>
    </div>
</aside>