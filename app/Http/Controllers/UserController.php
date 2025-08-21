<?php

namespace App\Http\Controllers;

use App\Formatter;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index()
    {
        $users = User::where('role', 'parent')->simplePaginate(5);
        return Formatter::apiResponse(200, 'Daftar orang tua', $users);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'phone_number' => 'required|string|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6'
        ]);

        $user = User::create([
            'id' => Str::uuid(),    
            'name' => $request->name,
            'role' => 'parent',
            'phone_number' => $request->phone_number,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return Formatter::apiResponse(201, 'Orang tua berhasil dibuat', $user);
    }

    public function show($id)
    {
        $user = User::where('id', $id)->where('role', 'parent')->first();
        if (!$user) return Formatter::apiResponse(404, 'User tidak ditemukan');
        return Formatter::apiResponse(200, 'Detail user', $user);
    }

    public function update(Request $request, $id)
    {
        $user = User::where('id', $id)->where('role', 'parent')->first();
        if (!$user) return Formatter::apiResponse(404, 'User tidak ditemukan');

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:100',
            'phone_number' => 'string|unique:users,phone_number,' . $user->id . ',id',
            'email' => 'email|unique:users,email,' . $user->id . ',id',
            'password' => 'nullable|string|min:6'
        ]);

        $data = $request->only(['name', 'phone_number', 'email']);
        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return Formatter::apiResponse(200, 'User diperbarui', $user);
    }

    public function destroy($id)
    {
        $user = User::where('id', $id)->where('role', 'parent')->first();
        if (!$user) return Formatter::apiResponse(404, 'User tidak ditemukan');

        $user->delete();
        return Formatter::apiResponse(200, 'User dihapus');
    }
}
