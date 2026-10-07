<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Audit Trail (SRS 4.21, bagian dari Notification & Audit Trail, keputusan
 * scope eksplisit user 2026-10-06: "Transaksi finansial & approval" --
 * BUKAN audit CRUD menyeluruh ke semua modul, BUKAN sekadar log
 * login/security). Mencatat siapa melakukan apa pada transaksi finansial
 * & workflow approval kunci: Penawaran Customer, Penugasan Teknisi,
 * Kasbon, Anggaran, Piutang Lain-lain, Invoice, Payroll, Aset Tetap,
 * Purchase Order, dan Jurnal Umum -- lihat App\Support\Audit::log() untuk
 * helper penulisnya dan titik-titik pemanggilan di masing-masing model.
 *
 * Append-only/immutable: TIDAK ADA kolom updated_at (lihat migration) dan
 * TIDAK ADA method update/delete di model ini -- memperketat pola
 * "no destructive delete" record finansial yang dipakai di seluruh app
 * jadi "tidak ada edit sama sekali", karena ini log audit.
 */
#[Fillable(['user_id', 'action', 'auditable_type', 'auditable_id', 'description', 'changes', 'ip_address'])]
class AuditLog extends Model
{
    const UPDATED_AT = null;

    public const ACTIONS = [
        'created' => 'Dibuat',
        'submitted' => 'Diajukan',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
        'cancelled' => 'Dibatalkan',
        'disbursed' => 'Dicairkan',
        'given' => 'Diberikan',
        'payment_recorded' => 'Pembayaran Dicatat',
        'sent' => 'Dikirim',
        'paid' => 'Dibayar/Lunas',
        'finalized' => 'Difinalisasi',
        'posted' => 'Diposting',
        'disposed' => 'Dilepaskan',
        'customer_decision' => 'Keputusan Customer Dicatat',
    ];

    protected function casts(): array
    {
        return [
            'changes' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function actionLabel(): string
    {
        return self::ACTIONS[$this->action] ?? ucfirst($this->action);
    }

    /**
     * Label modul dari auditable_type (nama class penuh -> label singkat
     * Indonesia) untuk filter & tampilan tabel di halaman Audit Trail.
     */
    public function moduleLabel(): string
    {
        return match ($this->auditable_type) {
            'App\\Models\\CustomerQuotation' => 'Penawaran Customer',
            'App\\Models\\Assignment' => 'Penugasan Teknisi',
            'App\\Models\\CashAdvance' => 'Kasbon',
            'App\\Models\\Budget' => 'Anggaran',
            'App\\Models\\OtherReceivable' => 'Piutang Lain-lain',
            'App\\Models\\Invoice' => 'Invoice',
            'App\\Models\\PayrollRun' => 'Payroll',
            'App\\Models\\FixedAsset' => 'Aset Tetap',
            'App\\Models\\PurchaseOrder' => 'Purchase Order',
            'App\\Models\\JournalEntry' => 'Jurnal Umum',
            default => (string) $this->auditable_type,
        };
    }
}
