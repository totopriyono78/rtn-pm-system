<?php

namespace App\Livewire\Purchasing;

use App\Models\Item;
use App\Models\Project;
use App\Models\RequestForQuotation;
use App\Models\RfqItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ManageRfqs extends Component
{
    use WithPagination;

    public string $statusFilter = '';

    public bool $showModal = false;

    public string $projectId = '';

    public string $notes = '';

    /** @var array<int, array{item_id: string, qty: string}> */
    public array $lines = [];

    public function render()
    {
        $canManage = auth()->user()->hasPermissionTo('manage-purchasing');

        $rfqs = RequestForQuotation::with('project', 'creator')
            ->withCount('items')
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(10);

        return view('livewire.purchasing.manage-rfqs', [
            'rfqs' => $rfqs,
            'projects' => Project::orderBy('name')->get(),
            'items' => Item::where('is_active', true)->orderBy('name')->get(),
            'canManage' => $canManage,
        ]);
    }

    public function openCreate(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-purchasing'), 403);
        $this->reset(['projectId', 'notes']);
        $this->lines = [['item_id' => '', 'qty' => '1']];
        $this->showModal = true;
    }

    public function addLine(): void
    {
        $this->lines[] = ['item_id' => '', 'qty' => '1'];
    }

    public function removeLine(int $index): void
    {
        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);
    }

    public function save(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-purchasing'), 403);

        $this->validate([
            'projectId' => ['required', Rule::exists('projects', 'id')],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.item_id' => ['required', Rule::exists('items', 'id')],
            'lines.*.qty' => ['required', 'numeric', 'min:0.01'],
        ]);

        DB::transaction(function () {
            $rfq = RequestForQuotation::create([
                'project_id' => $this->projectId,
                'code' => RequestForQuotation::generateCode(),
                'status' => 'draft',
                'created_by' => Auth::id(),
                'notes' => $this->notes,
            ]);

            foreach ($this->lines as $line) {
                RfqItem::create([
                    'request_for_quotation_id' => $rfq->id,
                    'item_id' => $line['item_id'],
                    'qty' => $line['qty'],
                ]);
            }
        });

        $this->showModal = false;
        session()->flash('success', 'RFQ berhasil dibuat. Silakan tambahkan penawaran dari vendor di halaman detail.');
    }
}
