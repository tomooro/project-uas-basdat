<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\User;

// --- CONTROLLERS ---
// Owner
use App\Http\Controllers\Owner\DashboardController;
use App\Http\Controllers\Owner\KaryawanController;
use App\Http\Controllers\Owner\HistoryController; 
use App\Http\Controllers\Owner\CabangController; // <-- Tambahkan Import CabangController
// use App\Http\Controllers\Owner\AnalyticsController; // [UTS HOLD]

// Umum
use App\Http\Controllers\LayananController;

// Kasir
use App\Http\Controllers\Kasir\DashboardController as KasirDashboardController;
use App\Http\Controllers\Kasir\PesananController;
use App\Http\Controllers\Kasir\PembayaranController;
use App\Http\Controllers\Kasir\ProfileController;
use App\Http\Controllers\Kasir\KasirHistoryController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => view('welcome'));

// =======================
// AUTHENTICATION (Login & Register Logic Tetap)
// =======================
Route::get('/login', fn () => view('auth.login'))->name('login');
Route::get('/register', fn () => view('auth.register'))->name('register');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

// LOGIN Logic Tetap
Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email'      => ['required','email'],
        'password'   => ['required'],
    ]);

    // DUMMY UNTUK PEMILIK (Bypass Logic)
    $dummyEmail = 'admin@laundrykita.com';
    $dummyPassword = 'admin123';
    
    if ($credentials['email'] === $dummyEmail && $credentials['password'] === $dummyPassword) {
        $user = User::where('email', $dummyEmail)->first();
        if (!$user) {
            $user = User::create([
                'name' => 'Admin Pemilik',
                'email' => $dummyEmail,
                'password' => bcrypt($dummyPassword),
                'role' => 'pemilik',
                'phone' => null,
            ]);
        }
        Auth::login($user);
        $request->session()->regenerate();
        return redirect('/pemilik');
    }

    // AUTH NORMAL
    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        $role = Auth::user()->role;
        return $role === 'pemilik' ? redirect('/pemilik')
             : ($role === 'kasir' ? redirect('/kasir') : redirect('/user'));
    }

    return back()->with('error', 'Email atau password salah!');
})->name('login.post');

// REGISTER Logic Tetap
Route::post('/register', function (Request $r) {
    $data = $r->validate([
        'name'       => ['required','string','max:100'],
        'email'      => ['required','email','unique:users,email'],
        'phone'      => ['nullable','string','max:25'],
        'password'   => ['required','min:5','confirmed'],
        'role'       => ['nullable', Rule::in(['user','kasir','pemilik'])],
        'owner_code' => ['nullable','string'],
    ]);

    $role = 'user';

    // Jika pemilik login dan mendaftarkan kasir
    if (Auth::check() && Auth::user()->role === 'pemilik' && ($data['role'] ?? null) === 'kasir') {
        $role = 'kasir';
    }

    // Logic Owner Code
    $invite = env('OWNER_INVITE_CODE');
    if (!empty($data['owner_code'])) {
        if ($invite && hash_equals($invite, $data['owner_code'])) {
            $role = 'pemilik';
        } else {
            return back()->withInput()->with('error','Kode pemilik salah.');
        }
    }

    $user = User::create([
        'name'     => $data['name'],
        'email'    => $data['email'],
        'phone'    => $data['phone'] ?? null,
        'password' => $data['password'],
        'role'     => $role,
    ]);

    // Redirect jika pemilik mendaftarkan karyawan
    if (Auth::check() && Auth::user()->role === 'pemilik' && $role === 'kasir') {
        return redirect()->route('pemilik.karyawan')->with('ok','Kasir berhasil didaftarkan.');
    }

    Auth::login($user);
    return $role === 'pemilik' ? redirect('/pemilik')
             : ($role === 'kasir' ? redirect('/kasir') : redirect('/user'));
})->name('register.post');


// =======================
// AREA KASIR (Route Tetap)
// =======================
Route::prefix('kasir')->middleware(['web', 'auth', 'role.session:kasir'])->name('kasir.')->group(function () {
    
    // Dashboard
    Route::get('/', [KasirDashboardController::class, 'index'])->name('dashboard');

    // Pesanan
    Route::get('/pesanan-aktif', [PesananController::class, 'index'])->name('pesanan.index');
    Route::get('/pesanan-baru', [PesananController::class, 'create'])->name('pesanan.create');
    Route::post('/pesanan-baru', [PesananController::class, 'store'])->name('pesanan.store');
    
    // ROUTE UPDATE STATUS
    Route::put('/pesanan/{id}/update-status', [PesananController::class, 'updateStatus'])->name('pesanan.updateStatus');
        
    Route::delete('/pesanan/{pesanan}', [PesananController::class, 'destroy'])->name('pesanan.destroy');
    Route::get('/pesanan/{pesanan}/tag', [PesananController::class, 'cetakTag'])->name('pesanan.cetakTag');
    
    // Pembayaran
    Route::get('/pembayaran', [PembayaranController::class, 'index'])->name('pembayaran.index');
    Route::post('/pembayaran/{id}/proses', [PembayaranController::class, 'proses'])->name('pembayaran.proses');
    Route::patch('/pembayaran/{id}/lunas', [PembayaranController::class, 'markPaid'])->name('pembayaran.markPaid');

    // Profil
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profil.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profil.update');

    // History
    Route::get('/history', [KasirHistoryController::class, 'index'])->name('history');
    Route::get('/history/{pesanan}', [KasirHistoryController::class, 'show'])->name('history.show');
});


// =======================
// AREA PEMILIK (Owner)
// =======================
Route::middleware(['web', 'auth', 'role.session:pemilik'])
    ->prefix('pemilik')
    ->name('pemilik.')
    ->group(function () {

        // 1. Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // 2. KELOLA CABANG (Lengkap CRUD)
        // Menampilkan list & modal
        Route::get('/cabang', [CabangController::class, 'index'])->name('cabang.index');
        // Menyimpan data baru
        Route::post('/cabang', [CabangController::class, 'store'])->name('cabang.store');
        // Update data (Edit) -> INI YANG KAMU TAMBAH
        Route::put('/cabang/{id}', [CabangController::class, 'update'])->name('cabang.update');
        // Hapus data -> INI YANG KAMU TAMBAH
        Route::delete('/cabang/{id}', [CabangController::class, 'destroy'])->name('cabang.destroy');

        // 3. Analitik (Contoh route view biasa)
        Route::get('/analitik', function () {
            return view('role.pemilik.analitik');
        })->name('analitik');


    // 2. KARYAWAN (Menggunakan Route Resource untuk Menghindari Duplikasi)
    Route::resource('karyawan', KaryawanController::class)->names('karyawan');
    
    // Tambahan: Reset Password Karyawan (Karena ini bukan standar resource, dibuat terpisah)
    // Perhatikan: Menggunakan {karyawan} (nama Model di Controller)
    Route::post('karyawan/{user}/reset-password', [KaryawanController::class, 'resetPassword'])->name('karyawan.reset');
    
    // CATATAN: Semua route Karyawan di bawah ini telah dihapus karena duplikasi 
    // dengan Route::resource('karyawan', ...) di atas.
    /* Route::get('/karyawan', ...)->name('karyawan'); 
    Route::post('/karyawan', ...)->name('karyawan.store'); 
    Route::put('/karyawan/{user}', ...)->name('karyawan.update');
    Route::delete('/karyawan/{user}', ...)->name('karyawan.destroy');
    Route::post('/karyawan/{user}/reset-password', ...)->name('karyawan.reset');
    */
    
    // 3. LAYANAN
    // CATATAN: Karena LayananController tidak ada di namespace Owner, 
    // pastikan controller tersebut diimpor dengan benar (LayananController::class)
    Route::get('/layanan', [LayananController::class, 'index'])->name('layanan.index');
    Route::post('/layanan', [LayananController::class, 'store'])->name('layanan.store');
    Route::put('/layanan/{id}', [LayananController::class, 'update'])->name('layanan.update');
    Route::delete('/layanan/{id}', [LayananController::class, 'destroy'])->name('layanan.destroy');
    Route::get('/layanan/history', [LayananController::class, 'history'])->name('layanan.history');

    // 4. HISTORY PESANAN
    Route::get('/history', [HistoryController::class, 'index'])->name('history');
    Route::get('/history/export', [HistoryController::class, 'exportCsv'])->name('history.export');
    Route::get('/history/{pesanan}', [HistoryController::class, 'show'])->name('history.show');
    Route::get('/history/{pesanan}/struk', [HistoryController::class, 'printReceipt'])->name('history.struk');
    Route::get('/history/{pesanan}/tag', [HistoryController::class, 'printTag'])->name('history.tag');

    // Analitik (HOLD)
    // Route::get('/analitik',      [AnalyticsController::class, 'index'])->name('analitik');
    // Route::get('/analitik/data', [AnalyticsController::class, 'data'])->name('analitik.data');
});

// Route User (Placeholder)
// Route::get('/user', fn () => view('role.user.user'))->middleware('role.session:user');