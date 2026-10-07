<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Pengaturan profil perusahaan untuk Company Website (pilar publik). Dirancang
 * sebagai singleton (selalu baris id=1) -- gunakan WebsiteSetting::current().
 */
#[Fillable([
    'company_name', 'tagline', 'about', 'vision', 'mission',
    'address', 'phone', 'whatsapp', 'email',
    'instagram_url', 'facebook_url', 'linkedin_url',
    'logo_path', 'hero_image_path', 'hero_headline', 'hero_subheadline',
])]
class WebsiteSetting extends Model
{
    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1], [
            'company_name' => 'PT RTN',
        ]);
    }
}
