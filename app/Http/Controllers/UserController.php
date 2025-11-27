<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Formatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::select('id', 'name', 'email', 'role', 'number', 'created_at');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        return Formatter::apiResponse(200, 'Daftar pengguna', $query->paginate(15));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'role'     => ['required', Rule::in(['admin', 'parents'])],
            'number'   => 'nullable|string|max:20',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return Formatter::apiResponse(422, 'Validasi gagal', $validator->errors());
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'role'     => $request->role,
            'number'   => $request->number,
            'password' => Hash::make($request->password),
        ]);

        $user->makeHidden(['password']);

        return Formatter::apiResponse(201, 'Pengguna berhasil dibuat.', $user);
    }

    public function show($id)
    {
        $user = User::select('id', 'name', 'email', 'role', 'number', 'created_at', 'updated_at')
            ->find($id);

        if (!$user) {
            return Formatter::apiResponse(404, 'Pengguna tidak ditemukan.');
        }

        return Formatter::apiResponse(200, 'Detail pengguna', $user);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return Formatter::apiResponse(404, 'Pengguna tidak ditemukan.');
        }

        $validator = Validator::make($request->all(), [
            'name'     => 'sometimes|required|string|max:100',
            'email'    => ['sometimes','required','email', Rule::unique('users')->ignore($id)],
            'role'     => ['sometimes','required', Rule::in(['admin', 'parents'])],
            'number'   => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return Formatter::apiResponse(422, 'Validasi gagal', $validator->errors());
        }

        $data = $request->only(['name', 'email', 'role', 'number']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        $user->makeHidden(['password']);

        return Formatter::apiResponse(200, 'Pengguna berhasil diperbarui.', $user);
    }

    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return Formatter::apiResponse(404, 'Pengguna tidak ditemukan.');
        }

        $user->delete();

        return Formatter::apiResponse(200, 'Pengguna berhasil dihapus.');
    }
}
