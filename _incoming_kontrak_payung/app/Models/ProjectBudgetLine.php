<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['project_id', 'category', 'planned_amount', 'notes'])]
class ProjectBudgetLine extends Model
{
    public const CATEGORIES = [
        'material' => 'Material',
        'transportasi' => 'Transportasi',
        'akomodasi' => 'Akomodasi',
        'administrasi' => 'Administrasi',
        'lain_lain' => 'Lain-lain',
    ];

    protected function casts(): array
    {
        return [
            'planned_amount' => 'decimal:2',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
