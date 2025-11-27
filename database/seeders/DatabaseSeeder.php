<?php

namespace Database\Seeders;

<<<<<<< HEAD
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
=======
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\PaymentCategory;
use App\Models\Bill;
use App\Models\Payment;
use App\Models\Notification;
use App\Models\DueDateAlert;
use Carbon\Carbon;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Admin
        $admin = User::create([
            'name' => 'Admin Sekolah',
            'email' => 'admin@sekolah.com',
            'role' => 'admin',
            'password' => Hash::make('password'),
        ]);

        // 2. Orang Tua & Siswa
        $parent1 = User::create([
            'name' => 'Budi Santoso (Ortu)',
            'email' => 'budi@ortu.com',
            'role' => 'parents',
            'password' => Hash::make('password'),
        ]);

        $student1 = Student::create([
            'name' => 'Andi Pratama',
            'nisn' => '1234567890',
            'kelas' => 'XII IPA 1',
            'user_id' => $parent1->id,
        ]);

        $parent2 = User::create([
            'name' => 'Rina Wijaya (Ortu)',
            'email' => 'rina@ortu.com',
            'role' => 'parents',
            'password' => Hash::make('password'),
        ]);

        $student2 = Student::create([
            'name' => 'Siti Nurhaliza',
            'nisn' => '1234567891',
            'kelas' => 'XI IPS 2',
            'user_id' => $parent2->id,
        ]);

        // 3. Tahun Ajaran Aktif
        $academicYear = AcademicYear::create([
            'school_years' => '2024/2025',
            'start_date' => '2024-07-01',
            'end_date' => '2025-06-30',
            'is_active' => true,
        ]);

        // 4. Kategori Pembayaran
        $spp = PaymentCategory::create([
            'name' => 'SPP Bulanan',
            'amount' => 500000,
            'frequency' => 'month',
        ]);

        $uangGedung = PaymentCategory::create([
            'name' => 'Uang Gedung',
            'amount' => 5000000,
            'frequency' => 'once',
        ]);

        $ujian = PaymentCategory::create([
            'name' => 'Ujian Akhir',
            'amount' => 150000,
            'frequency' => 'year',
        ]);

        // 5. Tagihan untuk Siswa 1
        $bill1 = Bill::create([
            'month_year' => 'Agustus 2024',
            'due_date' => '2024-08-10',
            'amount' => $spp->amount,
            'status' => 'unpaid',
            'payment_categories_id' => $spp->id,
            'student_id' => $student1->id,
            'academic_years_id' => $academicYear->id,
        ]);

        $bill2 = Bill::create([
            'month_year' => 'September 2024',
            'due_date' => '2024-09-10',
            'amount' => $spp->amount,
            'total_paid' => 0,
            'status' => 'unpaid',
            'payment_categories_id' => $spp->id,
            'student_id' => $student1->id,
            'academic_years_id' => $academicYear->id,
        ]);

        $bill3 = Bill::create([
            'month_year' => 'Uang Gedung',
            'due_date' => '2024-07-30',
            'amount' => $uangGedung->amount,
            'total_paid' => 0,
            'status' => 'unpaid',
            'payment_categories_id' => $uangGedung->id,
            'student_id' => $student1->id,
            'academic_years_id' => $academicYear->id,
        ]);

        // Tagihan untuk Siswa 2
        $bill4 = Bill::create([
            'month_year' => 'Agustus 2024',
            'due_date' => '2024-08-10',
            'amount' => $spp->amount,
            'total_paid' => 0,
            'status' => 'unpaid',
            'payment_categories_id' => $spp->id,
            'student_id' => $student2->id,
            'academic_years_id' => $academicYear->id,
        ]);

        // 6. Pembayaran Sukses (Cash)
        Payment::create([
            'payment_date' => now()->subDays(5),
            'amount_paid' => 500000,
            'payment_method' => 'cash',
            'reference_number' => 'CASH-001',
            'receipt_of_payment' => null,
            'status' => 'success',
            'processed_by' => $admin->id,
            'bill_id' => $bill1->id,
        ]);

        // Update status bill
        $bill1->update(['total_paid' => 500000, 'status' => 'paid']);

        // 7. Pembayaran Gagal (Midtrans)
        Payment::create([
            'payment_date' => now(),
            'amount_paid' => 500000,
            'payment_method' => 'virtual_account',
            'status' => 'failed',
            'midtrans_order_id' => 'PAY-' . $bill2->id . '-123456',
            'midtrans_transaction_id' => 'trx-abc123',
            'midtrans_payment_type' => 'bank_transfer',
            'midtrans_va_number' => '888123456789',
            'midtrans_fraud_status' => 'reject',
            'processed_by' => $admin->id,
            'bill_id' => $bill2->id,
        ]);

        // 8. Notifikasi
        Notification::create([
            'title' => 'Pembayaran Berhasil 🎉',
            'message' => "Tagihan Agustus 2024 atas nama Andi Pratama telah dilunasi.",
            'type' => 'payment_success',
            'is_read' => false,
            'user_id' => $parent1->id,
            'bill_id' => $bill1->id,
        ]);

        Notification::create([
            'title' => 'Jatuh Tempo Mendekati ⏳',
            'message' => "Tagihan September 2024 akan jatuh tempo pada 10 September.",
            'type' => 'payment_reminder',
            'is_read' => true,
            'user_id' => $parent1->id,
            'bill_id' => $bill2->id,
        ]);

        // 9. Due Date Alert
        DueDateAlert::create([
            'alert_type' => 'upcoming',
            'alert_date' => Carbon::parse('2024-09-05'),
            'is_processed' => false,
            'bill_id' => $bill2->id,
        ]);

        DueDateAlert::create([
            'alert_type' => 'overdue',
            'alert_date' => Carbon::parse('2024-08-15'),
            'is_processed' => true,
            'bill_id' => $bill4->id,
        ]);

        $this->command->info('✅ Database seeder berhasil dijalankan!');
>>>>>>> 48ceca89c80cd3cb95c2541bb2833327718bb572
    }
}
