<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\SafetyTalk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SafetyTalkPhotoController extends Controller
{
    /**
     * Unduh foto Safety Talk (private disk) -- aturan akses sama seperti
     * ReportFileController: pemilik catatan, atau pemegang view-reports yang
     * proyeknya berada dalam scope region/akses miliknya.
     */
    public function __invoke(Request $request, SafetyTalk $safetyTalk): StreamedResponse
    {
        $user = $request->user();
        $safetyTalk->loadMissing('activity.project');
        $project = $safetyTalk->activity->project;

        abort_unless($safetyTalk->photo_disk_path, 404);

        $isOwner = $safetyTalk->conducted_by === $user->id;
        $canViewReports = $user->hasPermissionTo('view-reports');
        $withinScope = Project::query()->visibleTo($user)->whereKey($project->id)->exists();

        abort_unless($isOwner || ($canViewReports && $withinScope), 403, 'Anda tidak memiliki akses ke berkas ini.');

        abort_unless(Storage::disk('local')->exists($safetyTalk->photo_disk_path), 404);

        return Storage::disk('local')->download($safetyTalk->photo_disk_path, $safetyTalk->photo_original_name);
    }
}
