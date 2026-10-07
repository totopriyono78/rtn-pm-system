<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Support\Audit;
use App\Support\Notifier;

/**
 * CustomerQuotation ("Penawaran"): disusun Project Controller dari item
 * ReleaseOrder + harga vendor (referensi, dari RFQ costing) + item tambahan.
 * Alur status: draft -> submitted (menunggu approval Direktur) -> approved
 * (Direktur setuju, dianggap terkirim ke customer) / rejected (Direktur
 * tolak internal) -> lalu hasil keputusan customer dicatat manual lewat
 * recordCustomerDecision() (customer_accepted / customer_rejected), karena
 * itu terjadi di luar sistem (email/meeting).
 */
#[Fillable(['release_order_id', 'code', 'revision_no', 'status', 'total', 'notes', 'created_by'])]
class CustomerQuotation extends Model
{
    use HasFactory;

    public const STATUSES = [
        'draft' => 'Draft',
        'submitted' => 'Menunggu Approval Direktur',
        'approved' => 'Disetujui — Terkirim ke Customer',
        'rejected' => 'Ditolak Direktur',
        'customer_accepted' => 'Diterima Customer (PO Terbit)',
        'customer_rejected' => 'Ditolak Customer',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'customer_decision_at' => 'datetime',
        ];
    }

    public function releaseOrder(): BelongsTo
    {
        return $this->belongsTo(ReleaseOrder::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CustomerQuotationItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function customerPurchaseOrder(): HasOne
    {
        return $this->hasOne(CustomerPurchaseOrder::class);
    }

    public function signatures(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(DocumentSignature::class, 'signable');
    }

    /**
     * Update 2026-10-07 -- keputusan client: "nomor penawarannya harus baru"
     * setiap kali direvisi. Sebelumnya base nomor diturunkan dari
     * $ro->ro_number (format lama: PNW-{ro_number}-R{revisi}), jadi dua
     * revisi dari RO yang sama cuma beda suffix -R{n} -- itu dianggap client
     * BUKAN nomor baru. Sekarang setiap panggilan menghasilkan nomor urut
     * global baru (format: PNW-{tahun}-{urut 4 digit}, meniru pola
     * Contract::generateNumberSuggestion()), independen dari RO/revisi.
     * $ro & $revisionNo tetap diterima (dan tetap disimpan ke kolom
     * release_order_id/revision_no di pemanggil) supaya histori penelusuran
     * ke RO & revisi sebelumnya tidak hilang -- cuma tidak lagi menentukan
     * bentuk kode-nya.
     */
    public static function generateCode(ReleaseOrder $ro, int $revisionNo): string
    {
        $year = now()->format('Y');
        $count = static::whereYear('created_at', $year)->count() + 1;

        return sprintf('PNW-%s-%04d', $year, $count);
    }

    public function recalcTotal(): void
    {
        $this->total = $this->items()->sum('subtotal');
        $this->save();
    }

    public function submitForApproval(User $user): void
    {
        abort_unless($this->status === 'draft', 400, 'Hanya penawaran berstatus draft yang dapat diajukan.');
        abort_if($this->items->isEmpty(), 400, 'Tambahkan minimal satu item penawaran terlebih dahulu.');

        $this->status = 'submitted';
        $this->submitted_at = now();
        $this->save();

        Audit::log($this, 'submitted', "Penawaran {$this->code} diajukan oleh {$user->name}, menunggu approval Direktur.");
        Notifier::permission('approve-quotation', 'Penawaran Menunggu Approval', "Penawaran {$this->code} (Rp ".number_format((float) $this->total, 0, ',', '.').") menunggu persetujuan Anda.", route('contracts.quotations.show', $this));
    }

    /**
     * Direktur menyetujui penawaran secara internal — dianggap siap/terkirim
     * ke customer. Kalau kontrak payung sudah punya plafon (max_value),
     * penawaran yang membuat proyeksi total pemakaian melebihi plafon TETAP
     * boleh disetujui (customer yang minta pekerjaan lewat RO, keputusan
     * bisnis ada di Direktur) tapi diberi peringatan di UI — bukan diblok
     * keras seperti budget procurement, karena ini pendapatan bukan belanja.
     */
    public function approve(User $approver): void
    {
        abort_unless($this->status === 'submitted', 400, 'Hanya penawaran menunggu approval yang dapat disetujui.');

        $this->status = 'approved';
        $this->approved_by = $approver->id;
        $this->approved_at = now();
        $this->save();

        $this->releaseOrder->update(['status' => 'dalam_penawaran']);

        Audit::log($this, 'approved', "Penawaran {$this->code} disetujui oleh {$approver->name}.");
        Notifier::user($this->creator, 'Penawaran Disetujui', "Penawaran {$this->code} telah disetujui dan dianggap terkirim ke customer.", route('contracts.quotations.show', $this));
    }

    public function reject(User $approver, ?string $note = null): void
    {
        abort_unless($this->status === 'submitted', 400, 'Hanya penawaran menunggu approval yang dapat ditolak.');

        $this->status = 'rejected';
        $this->approved_by = $approver->id;
        $this->approved_at = now();
        if ($note) {
            $this->notes = trim(($this->notes ? $this->notes."\n" : '')."Ditolak Direktur: {$note}");
        }
        $this->save();

        Audit::log($this, 'rejected', "Penawaran {$this->code} ditolak oleh {$approver->name}.".($note ? " Alasan: {$note}" : ''));
        Notifier::user($this->creator, 'Penawaran Ditolak', "Penawaran {$this->code} ditolak Direktur.".($note ? " Alasan: {$note}" : ''), route('contracts.quotations.show', $this));
    }

    /**
     * Catat hasil keputusan customer (diterima/ditolak) — dicatat manual
     * oleh Project Controller karena keputusannya datang dari luar sistem
     * (email/meeting), bukan aksi customer di aplikasi.
     */
    public function recordCustomerDecision(bool $accepted, ?string $note = null): void
    {
        abort_unless($this->status === 'approved', 400, 'Hanya penawaran yang sudah disetujui Direktur dan terkirim ke customer yang bisa dicatat keputusannya.');

        $this->status = $accepted ? 'customer_accepted' : 'customer_rejected';
        $this->customer_decision_at = now();
        $this->customer_decision_note = $note;
        $this->save();

        Audit::log($this, 'customer_decision', "Keputusan customer untuk penawaran {$this->code}: ".($accepted ? 'Diterima' : 'Ditolak').($note ? " -- {$note}" : ''));

        if (! $accepted) {
            // RO tetap "dalam_penawaran" supaya PC bisa buat revisi penawaran baru untuk RO yang sama.
            return;
        }
    }

    /**
     * Nomor revisi berikutnya yang harus dipakai kalau PC membuat penawaran
     * baru untuk Release Order yang sama (mis. setelah customer_rejected).
     */
    public static function nextRevisionNo(ReleaseOrder $ro): int
    {
        return (int) $ro->quotations()->max('revision_no') + 1;
    }
}
