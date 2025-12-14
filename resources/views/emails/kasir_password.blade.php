<h2>Selamat Datang di LaundryKita</h2>

<p>Halo {{ $user->name }},</p>

<p>Akun kasir Anda telah dibuat dengan detail berikut:</p>

<ul>
    <li><strong>Email:</strong> {{ $user->email }}</li>
    <li><strong>Password Sementara:</strong> {{ $password }}</li>
</ul>

<p>
Silakan login dan <strong>ganti password Anda setelah login pertama</strong>.
</p>

<p>Terima kasih,<br><strong>Manajemen LaundryKita</strong></p>
