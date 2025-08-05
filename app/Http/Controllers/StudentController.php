<?php

namespace App\Http\Controllers;

use App\Formatter;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::with('user')->get();
        return Formatter::apiResponse(200, 'Daftar siswa', $students);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'nisn' => 'required|string|unique:students',
            'kelas' => 'required|string|max:10',
            'user_id' => 'required|string|exists:users,sqlid'
        ]);

        $student = Student::create([
            'sqlid' => Str::uuid(),
            'name' => $request->name,
            'nisn' => $request->nisn,
            'kelas' => $request->kelas,
            'user_id' => $request->user_id,
        ]);

        return Formatter::apiResponse(201, 'Siswa ditambahkan', $student);
    }

    public function show($id)
    {
        $student = Student::with('user', 'bills')->find($id);
        if (!$student) return Formatter::apiResponse(404, 'Siswa tidak ditemukan');
        return Formatter::apiResponse(200, 'Detail siswa', $student);
    }

    public function update(Request $request, $id)
    {
        $student = Student::find($id);
        if (!$student) return Formatter::apiResponse(404, 'Tidak ditemukan');

        $request->validate([
            'name' => 'string|max:100',
            'nisn' => 'string|unique:students,nisn,' . $id . ',sqlid',
            'kelas' => 'string|max:10',
            'user_id' => 'string|exists:users,sqlid'
        ]);

        $student->update($request->only(['name', 'nisn', 'kelas', 'user_id']));

        return Formatter::apiResponse(200, 'Siswa diperbarui', $student);
    }

    public function destroy($id)
    {
        $student = Student::find($id);
        if (!$student) return Formatter::apiResponse(404, 'Tidak ditemukan');

        $student->delete();
        return Formatter::apiResponse(200, 'Siswa dihapus');
    }
}
