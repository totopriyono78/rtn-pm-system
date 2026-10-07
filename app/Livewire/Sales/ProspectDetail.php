<?php

namespace App\Livewire\Sales;

use App\Models\Customer;
use App\Models\Prospect;
use App\Models\ProspectActivity;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ProspectDetail extends Component
{
    public Prospect $prospect;

    // --- edit PIC ---
    public bool $showAssignModal = false;

    public string $assignedTo = '';

    // --- log aktivitas manual ---
    public bool $showActivityModal = false;

    public string $activityType = 'call';

    public string $activityNotes = '';

    public string $activityDate = '';

    // --- won ---
    public bool $showWinModal = false;

    public string $winExistingCustomerId = '';

    public string $winNote = '';

    // --- lost ---
    public bool $showLoseModal = false;

    public string $lostReason = '';

    public function mount(Prospect $prospect): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-prospects'), 403);
        $this->prospect = $prospect;
    }

    public function render()
    {
        $this->prospect->load(['assignee', 'creator', 'customer', 'activities.user', 'salesOrders']);

        return view('livewire.sales.prospect-detail', [
            'marketingUsers' => User::role('Marketing')->orderBy('name')->get(),
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    // ===== Pipeline =====

    public function moveToStage(string $stage): void
    {
        $this->prospect->moveToStage($stage);
        session()->flash('success', 'Prospect dipindah ke tahap "'.\App\Models\Prospect::STATUSES[$stage].'".');
    }

    public function openWinModal(): void
    {
        $this->reset(['winExistingCustomerId', 'winNote']);
        $this->showWinModal = true;
    }

    public function win(): void
    {
        $this->validate([
            'winExistingCustomerId' => ['nullable', Rule::exists('customers', 'id')],
            'winNote' => ['nullable', 'string'],
        ]);

        $customer = $this->prospect->win($this->winExistingCustomerId ?: null, $this->winNote ?: null);

        $this->showWinModal = false;
        session()->flash('success', 'Prospect dimenangkan & dikonversi menjadi Customer "'.$customer->name.'". Silakan buat Sales Order untuk melanjutkan.');
    }

    public function openLoseModal(): void
    {
        $this->reset(['lostReason']);
        $this->showLoseModal = true;
    }

    public function lose(): void
    {
        $this->validate([
            'lostReason' => ['required', 'string', 'max:500'],
        ]);

        $this->prospect->lose($this->lostReason);

        $this->showLoseModal = false;
        session()->flash('success', 'Prospect ditandai sebagai Lost.');
    }

    // ===== PIC =====

    public function openAssignModal(): void
    {
        $this->assignedTo = (string) ($this->prospect->assigned_to ?? '');
        $this->showAssignModal = true;
    }

    public function saveAssign(): void
    {
        $this->validate([
            'assignedTo' => ['nullable', Rule::exists('users', 'id')],
        ]);

        $this->prospect->update(['assigned_to' => $this->assignedTo ?: null]);
        $this->showAssignModal = false;
        session()->flash('success', 'PIC Marketing diperbarui.');
    }

    // ===== Activity log =====

    public function openActivityModal(): void
    {
        $this->reset(['activityNotes']);
        $this->activityType = 'call';
        $this->activityDate = now()->format('Y-m-d\TH:i');
        $this->showActivityModal = true;
    }

    public function saveActivity(): void
    {
        $this->validate([
            'activityType' => ['required', Rule::in(['call', 'meeting', 'email', 'note'])],
            'activityNotes' => ['required', 'string'],
            'activityDate' => ['required', 'date'],
        ]);

        ProspectActivity::create([
            'prospect_id' => $this->prospect->id,
            'user_id' => auth()->id(),
            'type' => $this->activityType,
            'notes' => $this->activityNotes,
            'activity_date' => $this->activityDate,
        ]);

        $this->showActivityModal = false;
        session()->flash('success', 'Aktivitas dicatat.');
    }
}
