<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Jejak tanda tangan digital generik (polymorphic) — bisa dipasang ke
 * dokumen apa pun (CustomerQuotation, PurchaseOrder, dsb) tanpa perlu kolom
 * TTD terpisah di tiap tabel dokumen. Simpan juga IP + waktu sebagai jejak
 * audit minimal (bukan pengganti sertifikat elektronik pihak ketiga).
 */
#[Fillable(['signable_type', 'signable_id', 'user_id', 'user_signature_id', 'role_label', 'signed_at', 'ip_address'])]
class DocumentSignature extends Model
{
    protected function casts(): array
    {
        return [
            'signed_at' => 'datetime',
        ];
    }

    public function signable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function userSignature(): BelongsTo
    {
        return $this->belongsTo(UserSignature::class);
    }
}
