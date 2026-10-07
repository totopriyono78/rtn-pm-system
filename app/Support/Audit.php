<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

/**
 * Helper tipis untuk menulis baris Audit Trail (SRS 4.21) dari titik-titik
 * transaksi finansial & approval -- lihat App\Models\AuditLog untuk
 * cakupan & alasan append-only. Dipanggil langsung dari method model
 * (approve/reject/dst), BUKAN lewat observer/event generik, supaya
 * deskripsi tiap baris log bisa ditulis manusiawi sesuai konteks
 * masing-masing aksi (konsisten gaya penulisan deskripsi transaksi kas
 * & jurnal GL otomatis di seluruh app).
 */
class Audit
{
    public static function log(Model $subject, string $action, string $description, ?array $changes = null, $actor = null): AuditLog
    {
        $actor ??= auth()->user();

        return AuditLog::create([
            'user_id' => $actor?->id,
            'action' => $action,
            'auditable_type' => $subject->getMorphClass(),
            'auditable_id' => $subject->getKey(),
            'description' => $description,
            'changes' => $changes,
            'ip_address' => request()?->ip(),
        ]);
    }
}
