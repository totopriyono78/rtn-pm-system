<?php

namespace App\Livewire\Website;

use App\Models\WebsiteSetting;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class ManageWebsiteProfile extends Component
{
    use WithFileUploads;

    public string $companyName = '';

    public string $tagline = '';

    public string $about = '';

    public string $vision = '';

    public string $mission = '';

    public string $address = '';

    public string $phone = '';

    public string $whatsapp = '';

    public string $email = '';

    public string $instagramUrl = '';

    public string $facebookUrl = '';

    public string $linkedinUrl = '';

    public string $heroHeadline = '';

    public string $heroSubheadline = '';

    /** @var mixed */
    public $logoUpload;

    /** @var mixed */
    public $heroImageUpload;

    public function mount(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-website'), 403);

        $settings = WebsiteSetting::current();
        $this->companyName = $settings->company_name;
        $this->tagline = (string) $settings->tagline;
        $this->about = (string) $settings->about;
        $this->vision = (string) $settings->vision;
        $this->mission = (string) $settings->mission;
        $this->address = (string) $settings->address;
        $this->phone = (string) $settings->phone;
        $this->whatsapp = (string) $settings->whatsapp;
        $this->email = (string) $settings->email;
        $this->instagramUrl = (string) $settings->instagram_url;
        $this->facebookUrl = (string) $settings->facebook_url;
        $this->linkedinUrl = (string) $settings->linkedin_url;
        $this->heroHeadline = (string) $settings->hero_headline;
        $this->heroSubheadline = (string) $settings->hero_subheadline;
    }

    public function render()
    {
        return view('livewire.website.manage-website-profile', [
            'settings' => WebsiteSetting::current(),
        ]);
    }

    public function save(): void
    {
        $this->validate([
            'companyName' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'about' => ['nullable', 'string'],
            'vision' => ['nullable', 'string'],
            'mission' => ['nullable', 'string'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'instagramUrl' => ['nullable', 'url', 'max:255'],
            'facebookUrl' => ['nullable', 'url', 'max:255'],
            'linkedinUrl' => ['nullable', 'url', 'max:255'],
            'heroHeadline' => ['nullable', 'string', 'max:255'],
            'heroSubheadline' => ['nullable', 'string', 'max:500'],
            'logoUpload' => ['nullable', 'image', 'max:2048'],
            'heroImageUpload' => ['nullable', 'image', 'max:4096'],
        ]);

        $settings = WebsiteSetting::current();

        $data = [
            'company_name' => $this->companyName,
            'tagline' => $this->tagline ?: null,
            'about' => $this->about ?: null,
            'vision' => $this->vision ?: null,
            'mission' => $this->mission ?: null,
            'address' => $this->address ?: null,
            'phone' => $this->phone ?: null,
            'whatsapp' => $this->whatsapp ?: null,
            'email' => $this->email ?: null,
            'instagram_url' => $this->instagramUrl ?: null,
            'facebook_url' => $this->facebookUrl ?: null,
            'linkedin_url' => $this->linkedinUrl ?: null,
            'hero_headline' => $this->heroHeadline ?: null,
            'hero_subheadline' => $this->heroSubheadline ?: null,
        ];

        if ($this->logoUpload) {
            if ($settings->logo_path) {
                Storage::disk('public')->delete($settings->logo_path);
            }
            $data['logo_path'] = $this->logoUpload->store('website/logo', 'public');
        }

        if ($this->heroImageUpload) {
            if ($settings->hero_image_path) {
                Storage::disk('public')->delete($settings->hero_image_path);
            }
            $data['hero_image_path'] = $this->heroImageUpload->store('website/hero', 'public');
        }

        $settings->update($data);

        $this->reset(['logoUpload', 'heroImageUpload']);
        session()->flash('success', 'Profil Company Website diperbarui.');
    }
}
