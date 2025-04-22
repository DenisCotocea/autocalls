<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    public function leads(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Lead::class);
    }
}
