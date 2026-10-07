<?php

namespace App\Livewire\Finance;

use App\Models\CashBankAccount;
use App\Models\FixedAsset;
use App\Models\Unit;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Asset Management -- bagian terakhir Finance & Accounting (SRS 4.14).
 * Khusus Administrator (`manage-fixed-assets`), konsisten dengan pola
 * Cash & Bank/Payroll/GL -- data akuntansi agregat perusahaan.
 */
#[Layout('layouts.app')]
class ManageFixedAssets extends Component
{
    use WithPagination;

    public string $statusFilter = '';

    public string $categoryFilter = '';

    // Form "Daftarkan Aset"
    public bool $showCreateModal = false;

    public string $name = '';

    public string $category = 'peralatan_teknis';

    public string $acquisitionDate = '';

    public string $acquisitionCost = '';

    public string $salvageValue = '0';

    public string $usefulLifeYears = '4';

    public string $unitId = '';

    public string $location = '';

    public string $notes = '';

    public bool $recordPurchase = false;

    public string $cashBankAccountId = '';

    // Modal pelepasan
    public bool $showDisposeModal = false;

    public ?int $disposingId = null;

    public string $disposeStatus = 'dijual';

    public string $disposalNotes = '';

    // Modal depresiasi
    public bool $showDepreciationModal = false;

    public string $depreciationPeriod = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-fixed-assets'), 403);
    }

    public function render()
    {
        $assets = FixedAsset::with(['unit', 'creator'])
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->categoryFilter, fn ($q) => $q->where('category', $this->categoryFilter))
            ->latest()
            ->paginate(10);

        return view('livewire.finance.manage-fixed-assets', [
            'assets' => $assets,
            'units' => Unit::orderBy('name')->get(),
            'cashBankAccounts' => CashBankAccount::where('is_active', true)->orderBy('name')->get(),
            'totalBookValue' => FixedAsset::where('status', 'aktif')->get()->sum('book_value'),
            'totalAcquisitionCost' => FixedAsset::where('status', 'aktif')->sum('acquisition_cost'),
        ]);
    }

    public function openCreate(): void
    {
        $this->reset([
            'name', 'category', 'acquisitionDate', 'acquisitionCost', 'salvageValue',
            'usefulLifeYears', 'unitId', 'location', 'notes', 'recordPurchase', 'cashBankAccountId',
        ]);
        $this->category = 'peralatan_teknis';
        $this->acquisitionDate = now()->format('Y-m-d');
        $this->salvageValue = '0';
        $this->usefulLifeYears = '4';
        $this->resetErrorBag();
        $this->showCreateModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(array_keys(FixedAsset::CATEGORIES))],
            'acquisitionDate' => ['required', 'date'],
            'acquisitionCost' => ['required', 'numeric', 'min:1'],
            'salvageValue' => ['required', 'numeric', 'min:0'],
            'usefulLifeYears' => ['required', 'integer', 'min:1', 'max:50'],
            'unitId' => ['nullable', Rule::exists('units', 'id')],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'cashBankAccountId' => [$this->recordPurchase ? 'required' : 'nullable', Rule::exists('cash_bank_accounts', 'id')],
        ], [], [
            'name' => 'Nama aset',
            'acquisitionDate' => 'Tanggal perolehan',
            'acquisitionCost' => 'Harga perolehan',
            'salvageValue' => 'Nilai residu',
            'usefulLifeYears' => 'Umur ekonomis (tahun)',
            'cashBankAccountId' => 'Akun Kas/Bank',
        ]);

        if ((float) $validated['salvageValue'] >= (float) $validated['acquisitionCost']) {
            $this->addError('salvageValue', 'Nilai residu harus lebih kecil dari harga perolehan.');

            return;
        }

        FixedAsset::createWithAcquisition(
            [
                'name' => $validated['name'],
                'category' => $validated['category'],
                'acquisition_date' => $validated['acquisitionDate'],
                'acquisition_cost' => $validated['acquisitionCost'],
                'salvage_value' => $validated['salvageValue'],
                'useful_life_years' => $validated['usefulLifeYears'],
                'unit_id' => $validated['unitId'] ?: null,
                'location' => $validated['location'] ?: null,
                'notes' => $validated['notes'] ?: null,
            ],
            auth()->user(),
            $this->recordPurchase,
            $this->recordPurchase ? CashBankAccount::findOrFail($validated['cashBankAccountId']) : null
        );

        $this->showCreateModal = false;
        session()->flash('success', 'Aset tetap terdaftar.');
    }

    public function openDispose(int $id): void
    {
        $this->disposingId = $id;
        $this->disposeStatus = 'dijual';
        $this->disposalNotes = '';
        $this->resetErrorBag();
        $this->showDisposeModal = true;
    }

    public function saveDispose(): void
    {
        $validated = $this->validate([
            'disposeStatus' => ['required', Rule::in(['dijual', 'rusak', 'dihapuskan'])],
            'disposalNotes' => ['nullable', 'string', 'max:500'],
        ], [], [
            'disposeStatus' => 'Status pelepasan',
        ]);

        FixedAsset::findOrFail($this->disposingId)->dispose(auth()->user(), $validated['disposeStatus'], $validated['disposalNotes'] ?: null);

        $this->showDisposeModal = false;
        session()->flash('success', 'Aset ditandai dilepaskan.');
    }

    public function openDepreciation(): void
    {
        $this->depreciationPeriod = now()->format('Y-m');
        $this->resetErrorBag();
        $this->showDepreciationModal = true;
    }

    public function runDepreciation(): void
    {
        $this->validate(['depreciationPeriod' => ['required', 'date_format:Y-m']], [], ['depreciationPeriod' => 'Periode']);

        $entry = FixedAsset::runMonthlyDepreciation(auth()->user(), $this->depreciationPeriod);

        $this->showDepreciationModal = false;
        session()->flash('success', $entry
            ? "Depresiasi periode {$this->depreciationPeriod} berhasil dijalankan, jurnal {$entry->entry_number} dibuat."
            : "Tidak ada aset yang perlu didepresiasi untuk periode {$this->depreciationPeriod}.");
    }
}
