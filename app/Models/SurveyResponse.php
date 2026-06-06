<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveyResponse extends Model
{
    public $timestamps = false;

    protected $fillable = ['survey_id', 'contact_id', 'anonymous_token', 'ip_address', 'submitted_at'];

    protected $casts = ['submitted_at' => 'datetime'];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(SurveyAnswer::class, 'response_id');
    }
}
