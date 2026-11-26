<?php

namespace App\Models\Community;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends BaseModel
{
    public function owner(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'owner_id');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(GroupMember::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    protected $table = 'groups';

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
