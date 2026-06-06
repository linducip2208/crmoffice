@include('errors._layout', [
    'code' => '429',
    'heading' => 'Terlalu banyak request.',
    'message' => 'Server membatasi request untuk menjaga performa. Coba lagi dalam beberapa menit.',
])
