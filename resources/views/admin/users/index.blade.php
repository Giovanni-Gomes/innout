<x-app-layout>
    @php
        renderTitle(
            "Utilizadores",
            "Funcionários (não administradores)",
            "fa-solid fa-users"
        );
    @endphp

    @include('components.messages')

    <div class="mb-3">
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus mr-2"></i>Novo utilizador
        </a>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Início</th>
                            <th>Fim</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->start_date?->format('Y-m-d') }}</td>
                                <td>{{ $user->end_date?->format('Y-m-d') ?? '—' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="post" class="d-inline" onsubmit="return confirm('Remover este utilizador?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Nenhum utilizador encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($users->hasPages())
            <div class="card-footer">
                {{ $users->withQueryString()->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
