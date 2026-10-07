<?php

namespace App\Http\Controllers;

use App\Models\UserSignature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Tampilkan gambar tanda tangan (private disk) inline — dipakai untuk preview
 * di halaman Kelola Tanda Tangan dan jejak tanda tangan di dokumen (mis.
 * CustomerQuotation). Semua user yang sudah login boleh melihat, karena
 * tujuannya memang ditampilkan di dokumen bersama yang bisa dilihat pihak lain
 * dalam sistem (bukan data rahasia per-user).
 */
class UserSignatureFileController extends Controller
{
    public function __invoke(Request $request, UserSignature $userSignature): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($userSignature->file_path), 404);

        return Storage::disk('local')->response($userSignature->file_path);
    }
}
