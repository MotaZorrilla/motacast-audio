<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Book\DeleteBookAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::withCount('books');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'suspended_users' => User::where('status', 'suspended')->count(),
            'admins' => User::where('role', 'admin')->count(),
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,user',
            'status' => 'required|in:active,suspended',
            'book_limit' => 'required|integer|min:-1',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', "Usuario '{$validated['name']}' creado exitosamente.");
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'role' => 'required|in:admin,user',
            'status' => 'required|in:active,suspended',
            'book_limit' => 'required|integer|min:-1',
        ]);

        // Safety check: Prevent admin from demoting or suspending himself
        if ($user->id === Auth::id()) {
            if ($validated['role'] !== 'admin') {
                return back()->with('error', 'No puedes degradar tu propia cuenta de Administrador.');
            }
            if ($validated['status'] !== 'active') {
                return back()->with('error', 'No puedes suspender tu propia cuenta activa.');
            }
        }

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', "Datos de '{$user->name}' actualizados correctamente.");
    }

    public function toggleStatus(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'No puedes suspender tu propia cuenta.');
        }

        $newStatus = $user->status === 'active' ? 'suspended' : 'active';
        $user->update(['status' => $newStatus]);

        $msg = $newStatus === 'active'
            ? "Cuenta de '{$user->name}' activada con éxito."
            : "Cuenta de '{$user->name}' suspendida.";

        return back()->with('success', $msg);
    }

    public function adjustLimit(Request $request, User $user)
    {
        $request->validate([
            'action' => 'required|in:plus1,plus5,unlimited,reset',
        ]);

        switch ($request->action) {
            case 'plus1':
                $user->book_limit = $user->book_limit === -1 ? -1 : $user->book_limit + 1;
                break;
            case 'plus5':
                $user->book_limit = $user->book_limit === -1 ? -1 : $user->book_limit + 5;
                break;
            case 'unlimited':
                $user->book_limit = -1;
                break;
            case 'reset':
                $user->book_limit = 3;
                break;
        }

        $user->save();

        return back()->with('success', "Cuota de '{$user->name}' actualizada a: ".($user->book_limit === -1 ? 'Ilimitado' : "{$user->book_limit} libros").'.');
    }

    public function destroy(User $user, DeleteBookAction $deleteBookAction)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta de Administrador.');
        }

        $userName = $user->name;

        // Cascade delete all books via DeleteBookAction
        foreach ($user->books as $book) {
            $deleteBookAction->handle($book);
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "Usuario '{$userName}' y todos sus audiolibros asociados eliminados correctamente.");
    }
}
