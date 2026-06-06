<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KbArticleVote extends Model
{
    public $timestamps = false;

    protected $fillable = ['article_id', 'voter_ip', 'helpful', 'voted_at'];

    protected $casts = [
        'helpful' => 'boolean',
        'voted_at' => 'datetime',
    ];
}
