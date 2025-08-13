<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankBranch extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    /**
     * Get the bank that owns the BankBranch
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class, 'bank_id', 'id')->select(['id', 'name_en', 'name_bn']);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'district_id', 'id')->select(['id', 'parent_id', 'name_en', 'name_bn', 'type']);
    }
}
