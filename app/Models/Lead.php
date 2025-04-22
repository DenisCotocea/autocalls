<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Campaign;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = ['campaign_id', 'name', 'email', 'phone_number'];

    public function campaign(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
}
