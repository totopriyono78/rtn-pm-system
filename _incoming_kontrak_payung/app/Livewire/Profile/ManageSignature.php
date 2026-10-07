<?php

namespace App\Livewire\Profile;

use App\Models\UserSignature;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Tanda tangan digital in-house (tanpa pihak ketiga): user menyimpan hasil
 * gambar canvas atau upload file, dipakai berulang sebagai signature default
 * saat menandatangani dokumen (lihat DocumentSignature / CustomerQuotation).
 * Ini paraf digital internal untuk kebutuhan operasional, BUKAN tanda tangan
 * elektronik bersertifikat pihak ketiga.
 */
#[Layout('layouts.app')]
class ManageSignature extends Component
{
    use WithFileUploads;

    public bool $showDrawModal = false;

    /** @var mixed */
    public $uploadFile;

    public function render()
    {
        return view('livewire.profile.manage-signature', [
            'signatures' => UserSignature::where('user_id', Auth::id())->latest()->get(),
        ]);
    }

    public function saveDrawnSignature(string $dataUrl): void
    {
        abort_unless(str_starts_with($dataUrl, 'data:image/png;base64,'), 400, 'Format gambar tidak valid.');

        $base64 = substr($dataUrl, strlen('data:image/png;base64,'));
        $binary = base64_decode($base64, true);
        abort_if($binary === false, 400, 'Gagal memproses gambar tanda tangan.');

        $path = 'signatures/'.Auth::id().'/'.Str::random(20).'.png';
        Storage::disk('local')->put($path, $binary);

        $this->storeAsDefault('drawn', $path);
        $this->showDrawModal = false;
        session()->flash('success', 'Tanda tangan berhasil disimpan sebagai default.');
    }

    public function saveUploadedSignature(): void
    {
        $this->validate([
            'uploadFile' => ['required', 'image', 'max:2048'],
        ]);

        $path = $this->uploadFile->store('signatures/'.Auth::id(), 'local');
        $this->storeAsDefault('uploaded', $path);
        $this->reset('uploadFile');
        session()->flash('success', 'Tanda tangan berhasil diunggah sebagai default.');
    }

    public function setDefault(int $id): void
    {
        $signature = UserSignature::where('user_id', Auth::id())->findOrFail($id);
        UserSignature::where('user_id', Auth::id())->update(['is_default' => false]);
        $signature->update(['is_default' => true]);
        session()->flash('success', 'Signature default diperbarui.');
    }

    public function delete(int $id): void
    {
        $signature = UserSignature::where('user_id', Auth::id())->findOrFail($id);
        Storage::disk('local')->delete($signature->file_path);
        $signature->delete();
        session()->flash('success', 'Signature dihapus.');
    }

    private function storeAsDefault(string $type, string $path): void
    {
        UserSignature::where('user_id', Auth::id())->update(['is_default' => false]);

        UserSignature::create([
            'user_id' => Auth::id(),
            'type' => $type,
            'file_path' => $path,
            'is_default' => true,
        ]);
    }
}
