<?php

// Data identitas perusahaan untuk kop surat pada dokumen cetak (mis. Surat
// Penawaran Harga). Perbarui nilainya lewat file .env — tidak perlu mengubah
// kode aplikasi.
return [
    'name' => env('COMPANY_NAME', 'PT RTN'),
    'address' => env('COMPANY_ADDRESS', 'Alamat perusahaan belum diisi. Perbarui COMPANY_ADDRESS pada file .env.'),
    'phone' => env('COMPANY_PHONE', '-'),
    'email' => env('COMPANY_EMAIL', '-'),

    // Penanda tangan default pada dokumen penawaran (biasanya Direktur atau
    // penanggung jawab Purchasing). Bisa dikosongkan agar kolom tanda tangan
    // dibiarkan blangko untuk diisi manual.
    'signatory_name' => env('COMPANY_SIGNATORY_NAME', ''),
    'signatory_title' => env('COMPANY_SIGNATORY_TITLE', 'Direktur'),
];
