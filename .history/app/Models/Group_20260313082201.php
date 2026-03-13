<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    protected $fillable = ['name', 'slug', 'logo_path', 'type', 'sort_order'];

    public function videos(): HasMany
    {
        return $this->hasMany(Video::class);
    }
}
