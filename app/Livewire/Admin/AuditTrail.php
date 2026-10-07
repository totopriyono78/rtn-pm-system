<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Halaman Audit Trail (SRS 4.21, keputusan scope eksplisit user
 * 2026-10-06: "Transaksi finansial & approval"). Sengaja memakai
 * permission `view-financial-reports` yang sudah ada (Administrator &
 * Direktur) -- bukan permission baru -- konsisten pola yang dipakai
 * modul Laporan Keuangan/Jurnal/Buku Besar (lihat 3.13): audit trail
 * finansial adalah bagian dari visibilitas keuangan yang sama, bukan
 * fungsi akses terpisah.
 */
#[Layout('layouts.app')]
class AuditTrail extends Component
{
    use WithPagination;

    public string $actionFilter = '';

    public string $moduleFilter = '';

    public string $userFilter = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('view-financial-reports'), 403);
    }

    public function updating(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $logs = AuditLog::with('user')
            ->when($this->actionFilter, fn ($q) => $q->where('action', $this->actionFilter))
            ->when($this->moduleFilter, fn ($q) => $q->where('auditable_type', $this->moduleFilter))
            ->when($this->userFilter, fn ($q) => $q->where('user_id', $this->userFilter))
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->latest()
            ->paginate(20);

        return view('livewire.admin.audit-trail', [
            'logs' => $logs,
            'users' => User::orderBy('name')->get(),
            'modules' => AuditLog::query()->select('auditable_type')->distinct()->whereNotNull('auditable_type')->pluck('auditable_type'),
        ]);
    }

    public function resetFilters(): void
    {
        $this->reset(['actionFilter', 'moduleFilter', 'userFilter', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }
}
