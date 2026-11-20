<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = '%'.$request->string('q')->toString().'%';
                $query->where(fn ($builder) => $builder->where('name', 'like', $term)->orWhere('email', 'like', $term));
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'search' => $request->string('q')->toString(),
        ]);
    }

    public function create(): View
    {
        return view('users.create', [
            'user' => new User(['role' => UserRole::Kasir]),
            'roles' => UserRole::options(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::query()->create($request->validated());

        Log::notice('Pengguna baru dibuat.', [
            'actor_id' => $request->user()->id,
            'user_id' => $user->id,
            'role' => $user->role->value,
        ]);

        return redirect()
            ->route('users.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        return view('users.edit', [
            'user' => $user,
            'roles' => UserRole::options(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        if ($request->user()->is($user) && $data['role'] !== UserRole::Owner->value) {
            return back()->withInput()->with('error', 'Anda tidak dapat mengubah role akun sendiri.');
        }

        if ($user->isOwner() && $data['role'] !== UserRole::Owner->value && User::query()->where('role', UserRole::Owner->value)->count() <= 1) {
            return back()->withInput()->with('error', 'Minimal harus ada satu akun Owner.');
        }

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $oldRole = $user->role->value;
        $user->update($data);

        Log::notice('Data pengguna diperbarui.', [
            'actor_id' => $request->user()->id,
            'user_id' => $user->id,
            'role_before' => $oldRole,
            'role_after' => $user->role->value,
            'password_changed' => isset($data['password']),
        ]);

        return redirect()
            ->route('users.index')
            ->with('success', 'Pengguna berhasil diperbarui.');
    }
}
