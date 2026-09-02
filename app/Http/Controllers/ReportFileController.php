<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ReportFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportFileController extends Controller
{
    /**
     * Unduh berkas laporan teknisi (private disk). Hanya bisa diakses oleh
     * pemilik laporan, atau user dengan permission view-reports yang proyeknya
     * berada dalam scope region/akses miliknya.
     */
    public function __invoke(Request $request, ReportFile $reportFile): StreamedResponse
    {
        $user = $request->user();
        $report = $reportFile->report()->with('activity.project')->firstOrFail();
        $project = $report->activity->project;

        $isOwner = $report->user_id === $user->id;
        $canViewReports = $user->hasPermissionTo('view-reports');
        $withinScope = Project::query()->visibleTo($user)->whereKey($project->id)->exists();

        abort_unless($isOwner || ($canViewReports && $withinScope), 403, 'Anda tidak memiliki akses ke berkas ini.');

        abort_unless(Storage::disk('local')->exists($reportFile->disk_path), 404);

        return Storage::disk('local')->download($reportFile->disk_path, $reportFile->original_name);
    }
}
