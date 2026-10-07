<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'activity_id', 'user_id', 'scheduled_date', 'notes',
    'status', 'created_by', 'approved_by', 'approved_at', 'rejection_reason',
])]
class Assignment extends Model
{
    use HasFactory;

    /**
     * Status approval usulan penugasan. 'diajukan' dipakai saat dibuat oleh
     * pemegang permission create-assignments saja (Lead Technician) dan
     * belum disetujui -- TIDAK terlihat oleh teknisi bersangkutan sampai
     * disetujui (lihat Assignment::scopeApproved()).
     */
    public const STATUSES = [
        'diajukan' => 'Menunggu Approval',
        'disetujui' => 'Disetujui',
        'ditolak' => 'Ditolak',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Dipakai di semua query sisi teknisi (Jadwal Saya, Presensi, Submit
     * Laporan, widget dashboard) supaya usulan yang masih 'diajukan' atau
     * sudah 'ditolak' tidak pernah muncul sebagai penugasan aktif.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'disetujui');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function attendance(): HasOne
    {
        return $this->hasOne(Attendance::class);
    }
}
