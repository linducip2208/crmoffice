@include('errors._layout', [
    'code' => '419',
    'heading' => 'Sesi kedaluwarsa.',
    'message' => 'Token CSRF kamu sudah expired. Refresh halaman lalu coba lagi — datanya masih aman, cuma perlu re-submit.',
])
