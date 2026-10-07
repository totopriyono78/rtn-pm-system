<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class ProjectDocumentZipController extends Controller
{
    /**
     * Unduh seluruh dokumen (versi terbaru saja) dalam satu kategori/folder
     * sebagai satu file ZIP -- SRS 4.10 "download bulk per folder".
     */
    public function __invoke(Request $request, Project $project): StreamedResponse
    {
        $user = $request->user();

        abort_unless(
            Project::query()->visibleTo($user)->whereKey($project->id)->exists(),
            403,
            'Anda tidak memiliki akses ke proyek ini.'
        );

        $category = $request->string('category')->toString();
        abort_unless(array_key_exists($category, ProjectDocument::CATEGORIES), 404, 'Kategori folder tidak dikenal.');

        $permission = ProjectDocument::CATEGORY_PERMISSIONS[$category] ?? null;
        abort_if($permission && ! $user->hasPermissionTo($permission), 403, 'Folder dokumen ini dibatasi untuk role Anda.');

        $documents = $project->documents()
            ->latestVersions()
            ->where('category', $category)
            ->get();

        abort_if($documents->isEmpty(), 404, 'Belum ada dokumen di folder ini.');

        $categoryLabel = ProjectDocument::CATEGORIES[$category];
        $zipFileName = sprintf('%s - %s.zip', $project->name, $categoryLabel);
        $tmpZipPath = tempnam(sys_get_temp_dir(), 'docs_').'.zip';

        $zip = new ZipArchive();
        $zip->open($tmpZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $usedNames = [];
        foreach ($documents as $document) {
            if (! Storage::disk('local')->exists($document->disk_path)) {
                continue;
            }

            $name = $document->original_name;
            if (isset($usedNames[$name])) {
                $usedNames[$name]++;
                $name = sprintf('%s (%d)', $name, $usedNames[$name]);
            } else {
                $usedNames[$name] = 1;
            }

            $zip->addFile(Storage::disk('local')->path($document->disk_path), $name);
        }

        $zip->close();

        return response()->streamDownload(function () use ($tmpZipPath) {
            echo file_get_contents($tmpZipPath);
            unlink($tmpZipPath);
        }, $zipFileName, ['Content-Type' => 'application/zip']);
    }
}
