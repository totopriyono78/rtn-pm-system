<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectDocumentController extends Controller
{
    /**
     * Unduh (atau tampilkan inline lewat ?inline=1, untuk preview PDF/gambar
     * tanpa terunduh) dokumen proyek dari private disk. Hanya bisa diakses
     * oleh user yang proyeknya berada dalam scope region/akses miliknya, dan
     * yang punya akses ke kategori/folder dokumen ini (matriks akses --
     * SRS 4.10).
     */
    public function __invoke(Request $request, ProjectDocument $projectDocument): StreamedResponse
    {
        $user = $request->user();

        $withinScope = Project::query()->visibleTo($user)->whereKey($projectDocument->project_id)->exists();

        abort_unless($withinScope, 403, 'Anda tidak memiliki akses ke dokumen ini.');
        abort_unless($projectDocument->isVisibleTo($user), 403, 'Folder dokumen ini dibatasi untuk role Anda.');
        abort_unless(Storage::disk('local')->exists($projectDocument->disk_path), 404);

        if ($request->boolean('inline')) {
            return Storage::disk('local')->response($projectDocument->disk_path, $projectDocument->original_name);
        }

        return Storage::disk('local')->download($projectDocument->disk_path, $projectDocument->original_name);
    }
}
