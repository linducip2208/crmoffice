<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class KbArticle extends Model
{
    use HasFactory, Searchable, SoftDeletes;

    protected $table = 'kb_articles';

    protected $fillable = [
        'category_id', 'title', 'slug', 'excerpt', 'content',
        'is_published', 'view_count', 'helpful_count', 'unhelpful_count',
        'author_id', 'published_at', 'meta_title', 'meta_description',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'content' => strip_tags((string) $this->content),
            'category_id' => $this->category_id,
            'is_published' => (bool) $this->is_published,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return (bool) $this->is_published;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(KbCategory::class, 'category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(\App\Models\KbArticleVote::class, 'article_id');
    }
}
