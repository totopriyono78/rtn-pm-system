<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'address', 'npwp', 'pic_name', 'pic_phone', 'pic_email', 'is_active'])]
class Customer extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function clientUsers(): HasMany
    {
        return $this->hasMany(ClientUser::class);
    }

    /**
     * Prospect (lead CRM) yang pernah dikonversi jadi Customer ini -- lihat
     * Prospect::win(). Nullable/kosong kalau Customer ini tidak lahir dari
     * jalur CRM (dibuat manual lewat ManageCustomers seperti biasa).
     */
    public function prospects(): HasMany
    {
        return $this->hasMany(Prospect::class);
    }

    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }

    public static function generateCode(): string
    {
        $count = static::count() + 1;

        return sprintf('CUST-%04d', $count);
    }
}
