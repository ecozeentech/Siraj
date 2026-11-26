<?php

namespace App\Models\Community;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostMedia extends BaseModel
{
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    protected $table = 'post_media';
}
