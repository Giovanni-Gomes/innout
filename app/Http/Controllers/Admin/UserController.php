<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->employees()
            ->orderBy('name')
            ->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'] ?? null,
            'is_admin' => false,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Utilizador criado.');
    }

    public function edit(User $user): View
    {
        abort_if($user->is_admin, 404);

        return view('admin.users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        abort_if($user->is_admin, 404);

        $validated = $request->validated();

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
        ];

        if (! empty($validated['password'] ?? null)) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'Utilizador atualizado.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if($user->is_admin, 404);

        $user->workingHours()->delete();
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Utilizador removido.');
    }
}
