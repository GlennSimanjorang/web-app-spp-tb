<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Formatter;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\DueDateAlert;

class BillController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Bill::with(['student', 'paymentCategory', 'academicYear']);

        if ($user->role === 'parents') {
            $query->whereHas('student.user', fn($q) => $q->where('id', $user->id));
        }

        $query->whereIn('status', ['unpaid', 'partial']);

        if ($request->filled('search')) {
            $query->where('month_year', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        $bills = $query->latest()->paginate(10);
        return Formatter::apiResponse(200, 'Data tagihan ditemukan.', $bills);
    }

    public function mybills(Request $request)
    {
        $user = Auth::user();

        // Hanya parents yang boleh akses
        if ($user->role !== 'parents') {
            return Formatter::apiResponse(403, 'You are not authorized to access this page.');
        }

        // Bangun query: tagihan milik siswa yang diasuh oleh user ini
        $query = Bill::with(['student', 'paymentCategory', 'academicYear'])
            ->whereHas('student.user', function ($q) use ($user) {
                $q->where('id', $user->id);
            });

        // Filter default: hanya yang belum lunas (opsional, sesuai kebutuhan)
        $query->whereIn('status', ['unpaid', 'partial']);

        // Filter opsional: pencarian berdasarkan bulan/tahun
        if ($request->filled('search')) {
            $query->where('month_year', 'like', '%' . $request->search . '%');
        }

        // Status bisa di-override via query (misal: ?status=unpaid)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bills = $query->latest()->paginate(10);

        return Formatter::apiResponse(200, 'Tagihan Anda ditemukan.', $bills);
    }

    public function show(Bill $bill)
    {
        $this->authorize('view', $bill);

        $bill->load(['payments', 'student', 'paymentCategory']);

        return Formatter::apiResponse(200, 'Detail tagihan ditemukan.', $bill);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'payment_categories_id' => 'required|exists:payment_categories,id',
            'academic_years_id' => 'required|exists:academic_years,id',
            'due_date' => 'nullable|date', // ❌ tidak wajib
        ]);

        if ($validator->fails()) {
            return Formatter::apiResponse(422, 'Validasi gagal.', $validator->errors());
        }

        $category = \App\Models\PaymentCategory::findOrFail($request->payment_categories_id);
        $academicYear = \App\Models\AcademicYear::findOrFail($request->academic_years_id);

        // ✅ Cegah duplikasi untuk 'once'
        if ($category->frequency === 'once') {
            $existing = Bill::where('student_id', $request->student_id)
                ->where('payment_categories_id', $request->payment_categories_id)
                ->where('academic_years_id', $request->academic_years_id)
                ->exists();

            if ($existing) {
                return Formatter::apiResponse(422, 'Tagihan ini sudah ada dan tidak bisa dibuat ulang (frekuensi sekali).');
            }
        }

        // ✅ Generate due_date otomatis jika tidak dikirim
        $dueDate = null;
        if ($request->filled('due_date')) {
            $dueDate = $request->due_date;
        } else {
            if ($category->frequency === 'month') {
                $dueDate = Carbon::now()->addMonth()->day(10);
            } elseif ($category->frequency === 'year') {
                // Akhir tahun ajaran
                $dueDate = Carbon::parse($academicYear->end_date)->day(30);
            } else { 
                $dueDate = Carbon::parse($academicYear->start_date)->day(30);
            }
        }

        // ✅ Format month_year berdasarkan due_date
        $monthYear = match ($category->frequency) {
            'month' => Carbon::parse($dueDate)->format('F Y'),
            'year' => Carbon::parse($dueDate)->format('Y'),
            default => $category->name, // atau 'Once'
        };

        // ✅ Ambil amount dari kategori
        $amount = $category->amount;

        $bill = Bill::create([
            'student_id' => $request->student_id,
            'payment_categories_id' => $request->payment_categories_id,
            'academic_years_id' => $request->academic_years_id,
            'month_year' => $monthYear,
            'due_date' => $dueDate,
            'amount' => $amount,
            'total_paid' => 0,
            'status' => 'unpaid',
        ]);

        DueDateAlert::create([
            'bill_id' => $bill->id,
            'alert_date' => $bill->due_date,
            'alert_type' => 'due',
            'is_processed' => false,
        ]);

        // Buat alert pengingat 3 hari sebelumnya
        DueDateAlert::create([
            'bill_id' => $bill->id,
            'alert_date' => \Carbon\Carbon::parse($bill->due_date)->subDays(3),
            'alert_type' => 'upcoming',
            'is_processed' => false,
        ]);

        return Formatter::apiResponse(201, 'Tagihan berhasil dibuat.', $bill);
    }

    public function generateMonthlyBills(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'payment_categories_id' => 'required|exists:payment_categories,id',
            'academic_years_id' => 'required|exists:academic_years,id',
        ]);

        if ($validator->fails()) {
            return Formatter::apiResponse(422, 'Validasi gagal.', $validator->errors());
        }

        $academicYear = \App\Models\AcademicYear::findOrFail($request->academic_years_id);

        // Parse tanggal mulai & akhir
        $start = Carbon::parse($academicYear->start_date);
        $end = Carbon::parse($academicYear->end_date);

        // Generate daftar bulan
        $months = [];
        $current = $start->copy();

        while ($current->lte($end)) {
            $months[] = $current->format('F Y'); // "July 2024"
            $current->addMonth();
        }

        // Ambil data kategori
        $category = \App\Models\PaymentCategory::findOrFail($request->payment_categories_id);

        // Validasi: pastikan kategori frekuensinya bulanan
        if ($category->frequency !== 'month') {
            return Formatter::apiResponse(422, 'Kategori ini bukan tagihan bulanan.');
        }

        $createdBills = [];
        DB::beginTransaction();

        try {
            foreach ($months as $monthYear) {
                // Cek apakah tagihan ini sudah ada (hindari duplikat)
                $exists = Bill::where('student_id', $request->student_id)
                    ->where('payment_categories_id', $request->payment_categories_id)
                    ->where('month_year', $monthYear)
                    ->exists();

                if ($exists) {
                    continue; // lewati jika sudah ada
                }

                // Hitung due date (misal: tanggal 10 tiap bulan)
                $dueDate = Carbon::parse($monthYear)->day(10);

                $bill = Bill::create([
                    'student_id' => $request->student_id,
                    'payment_categories_id' => $request->payment_categories_id,
                    'academic_years_id' => $academicYear->id,
                    'month_year' => $monthYear,
                    'due_date' => $dueDate,
                    'amount' => $category->amount,
                    'total_paid' => 0,
                    'status' => 'unpaid',
                ]);

                DueDateAlert::create([
                    'bill_id' => $bill->id,
                    'alert_date' => $dueDate,
                    'alert_type' => 'due',
                    'is_processed' => false,
                ]);

                // Alert pengingat 3 hari sebelum
                DueDateAlert::create([
                    'bill_id' => $bill->id,
                    'alert_date' => $dueDate->copy()->subDays(3),
                    'alert_type' => 'upcoming',
                    'is_processed' => false,
                ]);

                $createdBills[] = $bill;
            }

            DB::commit();

            return Formatter::apiResponse(
                201,
                'Tagihan bulanan berhasil dibuat untuk periode ' . $academicYear->school_years,
                $createdBills
            );
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Bulk Bill Creation Failed: ' . $e->getMessage());
            return Formatter::apiResponse(500, 'Gagal membuat tagihan massal.');
        }
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
            return Formatter::apiResponse(422, 'Validasi gagal.', $validator->errors());
        }

        $bill->update($validator->validated());

        return Formatter::apiResponse(200, 'Tagihan diperbarui.', $bill);
    }

    

    public function destroy(Bill $bill)
    {
        $this->authorize('delete', $bill);

        $bill->delete();

        return Formatter::apiResponse(200, 'Tagihan dihapus.');
    }
}
