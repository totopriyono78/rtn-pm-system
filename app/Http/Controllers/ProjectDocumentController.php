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
     * Unduh dokumen proyek (private disk). Hanya bisa diakses oleh user yang
     * proyeknya berada dalam scope region/akses miliknya (sama seperti aturan
     * akses detail proyek).
     */
    public function __invoke(Request $request, ProjectDocument $projectDocument): StreamedResponse
    {
        $user = $request->user();

        $withinScope = Project::query()->visibleTo($user)->whereKey($projectDocument->project_id)->exists();

        abort_unless($withinScope, 403, 'Anda tidak memiliki akses ke dokumen ini.');

        abort_unless(Storage::disk('local')->exists($projectDocument->disk_path), 404);

        return Storage::disk('local')->download($projectDocument->disk_path, $projectDocument->original_name);
    }
}
