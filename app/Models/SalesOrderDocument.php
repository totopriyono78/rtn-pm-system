<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['sales_order_id', 'uploaded_by', 'category', 'disk_path', 'original_name', 'mime_type', 'size_bytes'])]
class SalesOrderDocument extends Model
{
    public const CATEGORIES = [
        'datasheet' => 'Datasheet Pompa',
        'drawing' => 'Gambar Teknis / Drawing',
        'quotation' => 'Surat Penawaran Harga',
        'lainnya' => 'Dokumen Lainnya',
    ];

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? self::CATEGORIES['lainnya'];
    }
}
