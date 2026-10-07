<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'company', 'email', 'phone', 'subject', 'message', 'status'])]
class WebsiteContactMessage extends Model
{
    use HasFactory;

    public const STATUSES = [
        'baru' => 'Baru',
        'dihubungi' => 'Sudah Dihubungi',
        'selesai' => 'Selesai',
    ];
}
