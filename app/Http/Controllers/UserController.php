<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('master.user.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:pemilik,admin,kasir',
            'status' => 'required|in:active,inactive',
        ]);

        User::create([
            'nama' => $request->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'status' => $request->status,
        ]);

        return redirect()->route('master.user.index')->with('success', 'User berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $user = User::where('id_user', $id)->firstOrFail(); 

        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id_user . ',id_user',
            'role' => 'required|in:pemilik,admin,kasir',
            'status' => 'required|in:active,inactive',
        ]);

        $data = [
            'nama' => $request->nama,
            'email' => $request->email,
            'role' => $request->role,
            'status' => $request->status,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('master.user.index')->with('success', 'Data user berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $user = User::where('id_user', $id)->firstOrFail();
        $user->delete();

        return redirect()->route('master.user.index')->with('success', 'User berhasil dihapus!');
    }
}