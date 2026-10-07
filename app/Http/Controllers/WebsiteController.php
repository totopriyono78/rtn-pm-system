<?php

namespace App\Http\Controllers;

use App\Models\WebsiteContactMessage;
use App\Models\WebsitePortfolioItem;
use App\Models\WebsiteProduct;
use App\Models\WebsiteService;
use App\Models\WebsiteSetting;
use App\Models\WebsiteSlide;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Company Website (pilar publik, tanpa login) per SRS v2.0 bab 4.2.
 * Terpisah total dari Internal Portal: tidak ada middleware auth di sini.
 */
class WebsiteController extends Controller
{
    public function home(): View
    {
        $settings = WebsiteSetting::current();

        return view('website.home', [
            'settings' => $settings,
            'slides' => WebsiteSlide::published()->ordered()->get(),
            'services' => WebsiteService::published()->ordered()->get(),
            'products' => WebsiteProduct::published()->ordered()->limit(4)->get(),
            'portfolioItems' => WebsitePortfolioItem::published()->ordered()->limit(3)->get(),
        ]);
    }

    public function about(): View
    {
        return view('website.about', [
            'settings' => WebsiteSetting::current(),
        ]);
    }

    public function products(): View
    {
        return view('website.products', [
            'settings' => WebsiteSetting::current(),
            'products' => WebsiteProduct::published()->ordered()->get(),
        ]);
    }

    public function services(): View
    {
        $services = WebsiteService::published()->ordered()->get();

        return view('website.services', [
            'settings' => WebsiteSetting::current(),
            'servicesByDivision' => $services->groupBy('division'),
        ]);
    }

    public function portfolio(): View
    {
        return view('website.portfolio', [
            'settings' => WebsiteSetting::current(),
            'portfolioItems' => WebsitePortfolioItem::published()->ordered()->get(),
        ]);
    }

    public function contact(): View
    {
        return view('website.contact', [
            'settings' => WebsiteSetting::current(),
        ]);
    }

    public function submitContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            // Honeypot sederhana anti-spam bot -- field tersembunyi di form, harus kosong.
            'website' => ['max:0'],
        ]);

        WebsiteContactMessage::create([
            'name' => $validated['name'],
            'company' => $validated['company'] ?? null,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'subject' => $validated['subject'] ?? null,
            'message' => $validated['message'],
        ]);

        return redirect()->route('website.contact')->with('contact_success', true);
    }
}
