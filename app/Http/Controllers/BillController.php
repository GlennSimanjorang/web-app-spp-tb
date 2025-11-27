<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
// use App\Formatter; // 🛑 Hapus atau komenkan helper ini

class BillController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // 🛑 [FIXED EAGER LOADING] Pastikan 'name' adalah nama kolom yang benar
        $query = Bill::with([
            // Tambahkan foreign key 'student_id' untuk memastikan relasi tidak putus saat membatasi kolom
            // Asumsi: kolom nama siswa adalah 'name'
            'student:id,nisn,name,class_id', 
            'paymentCategory:id,name', 
            'academicYear:id,year'
        ]);

        // 🛑 [FIXED PERFORMA KRITIS] Filter hanya untuk parents
        //if ($user && $user->role === 'parents') {
            // whereHas sangat lambat. Jika memungkinkan, gunakan relasi langsung.
            // Biarkan whereHas ini untuk sekarang, tapi ini adalah titik terlemah.
            //$query->whereHas('student.user', fn($q) => $q->where('id', $user->id));
        //}

        // Filter data (sama)
        if ($request->filled('search')) {
            $query->where('month_year', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        // Ambil data yang sudah dipaginasi
        $bills = $query->latest()->paginate(10);

        // 🛑 [FIXED KRITIS] Ganti ke format response()->json standar
        return response()->json([
            'success' => true,
            'message' => 'Data tagihan ditemukan.',
            'data' => $bills
        ], 200);
    }

    public function show(Bill $bill)
    {
        $this->authorize('view', $bill);

        $bill->load(['payments', 'student', 'paymentCategory']);

        // 🛑 [FIXED] Ganti ke format response()->json standar
        return response()->json([
            'success' => true,
            'message' => 'Detail tagihan ditemukan.',
            'data' => $bill
        ], 200);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Bill::class);

        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'payment_categories_id' => 'required|exists:payment_categories,id',
            'academic_years_id' => 'required|exists:academic_years,id',
            'month_year' => 'required|string|max:20',
            'due_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            // 🛑 [FIXED] Ganti ke format response()->json standar
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        $bill = Bill::create([
            ...$validator->validated(),
            'total_paid' => 0,
            'status' => 'unpaid'
        ]);

        // 🛑 [FIXED] Ganti ke format response()->json standar
        return response()->json([
            'success' => true,
            'message' => 'Tagihan berhasil dibuat.',
            'data' => $bill
        ], 201);
    }

    public function update(Request $request, Bill $bill)
    {
        $this->authorize('update', $bill);

        $validator = Validator::make($request->all(), [
            'month_year' => 'required|string|max:20',
            'due_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            // 🛑 [FIXED] Ganti ke format response()->json standar
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        $bill->update($validator->validated());

        // 🛑 [FIXED] Ganti ke format response()->json standar
        return response()->json([
            'success' => true,
            'message' => 'Tagihan diperbarui.',
            'data' => $bill
        ], 200);
    }

    public function destroy(Bill $bill)
    {
        $this->authorize('delete', $bill);

        $bill->delete();

        // 🛑 [FIXED] Ganti ke format response()->json standar
        return response()->json([
            'success' => true,
            'message' => 'Tagihan dihapus.'
        ], 200);
    }
}