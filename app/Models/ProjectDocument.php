<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Document Management Terstruktur -- SRS v2.0 modul 4.10. Folder taxonomy
 * direpresentasikan lewat kolom `category` (bukan tabel folder tersendiri,
 * supaya tetap sederhana) -- subfolder fisik di disk juga mengikuti label
 * kategori ini (lihat ManageProjects::save()).
 */
#[Fillable(['project_id', 'uploaded_by', 'category', 'version', 'parent_document_id', 'is_client_visible', 'disk_path', 'original_name', 'mime_type', 'size_bytes'])]
class ProjectDocument extends Model
{
    public const CATEGORIES = [
        'daily_report' => 'Daily Report',
        'ba_temuan' => 'BA Temuan',
        'bapp_ro' => 'BAPP RO',
        'bal_bulanan' => 'BAL Bulanan',
        'drawing' => 'Drawing',
        'simlok_sika' => 'Simlok & SIKA',
        'lainnya' => 'Dokumen Lainnya',
    ];

    /**
     * Kategori yang butuh permission tambahan di luar sekadar "bisa lihat
     * proyek ini" -- matriks akses per folder (SRS 4.10). Kategori yang
     * tidak disebut di sini bisa dilihat siapa pun yang punya akses ke
     * proyeknya.
     */
    public const CATEGORY_PERMISSIONS = [
        'bal_bulanan' => 'view-contract-value',
    ];

    protected function casts(): array
    {
        return [
            'is_client_visible' => 'boolean',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function previousVersion(): BelongsTo
    {
        return $this->belongsTo(ProjectDocument::class, 'parent_document_id');
    }

    public function newerVersions(): HasMany
    {
        return $this->hasMany(ProjectDocument::class, 'parent_document_id');
    }

    /**
     * Hanya dokumen yang merupakan versi terbaru di rantainya (tidak ada
     * dokumen lain yang menjadikannya parent_document_id).
     */
    public function scopeLatestVersions(Builder $query): Builder
    {
        return $query->whereNotIn('id', function ($sub) {
            $sub->select('parent_document_id')
                ->from('project_documents')
                ->whereNotNull('parent_document_id');
        });
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? self::CATEGORIES['lainnya'];
    }

    public function isVisibleTo(User $user): bool
    {
        $permission = self::CATEGORY_PERMISSIONS[$this->category] ?? null;

        return $permission === null || $user->hasPermissionTo($permission);
    }
}
