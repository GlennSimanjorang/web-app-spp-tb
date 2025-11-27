<?php

namespace App\Http\Controllers;

use App\Formatter;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class StudentController extends Controller
{
    public function index()
    {
        $students = Student::with('user')->get();
        return Formatter::apiResponse(200, 'Daftar siswa', $students);
    }

    public function myStudents()
    {
        \Log::info('🔍 myStudents accessed', [
            'auth_id' => Auth::id(),
            'user' => Auth::user()?->toArray(), // Log seluruh user
            'role_type' => gettype(Auth::user()?->role),
            'role_length' => strlen(Auth::user()?->role),
            'role_debug' => '"' . Auth::user()?->role . '"', // Lihat spasi?
        ]);

        $user = Auth::user();

        if (!$user) {
            \Log::warning('🚫 Unauthorized: no user authenticated');
            return Formatter::apiResponse(401, 'Unauthorized');
        }

        if ($user->role !== 'parents') {
            \Log::warning('🚫 Access denied: wrong role', [
                'given_role' => $user->role,
                'expected' => 'parents',
                'roles_match' => $user->role === 'parents' ? 'yes' : 'no'
            ]);
            return Formatter::apiResponse(403, 'You are not authorized to access this page.');
        }

        $students = Student::where('user_id', $user->id)->get();

        if ($students->isEmpty()) {
            \Log::info('📭 No students found for user', ['user_id' => $user->id]);
            return Formatter::apiResponse(404, 'Anda belum memiliki siswa terdaftar.');
        }

        \Log::info('✅ Success: returning students', ['count' => $students->count()]);

        return Formatter::apiResponse(200, 'Daftar siswa Anda', $students);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required|string|max:100',
            'nisn' => 'required|string|unique:students',
            'kelas' => 'required|string|max:10',
            'user_id' => 'required|string|exists:users,id'
        ]);
        if ($validator->fails()) {
            return Formatter::apiResponse(422, 'Validasi gagal', $validator->errors());
        }

        $student = Student::create([
            'id' => Str::uuid(),
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

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:100',
            'nisn' => 'string|unique:students,nisn,' . $id . ',id',
            'kelas' => 'string|max:10',
            'user_id' => 'string|exists:users,id'
        ]);

        if ($validator->fails()) {
            return Formatter::apiResponse(404, 'Validasi gagal', $validator->errors());
        }

        $student->update($request->only(['name', 'nisn', 'kelas', 'user_id']));
    }

    public function destroy($id)
    {
        $student = Student::find($id);
        if (!$student) return Formatter::apiResponse(404, 'Tidak ditemukan');

        $student->delete();
        return Formatter::apiResponse(200, 'Siswa dihapus');
    }
}
