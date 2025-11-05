<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PropertyDescription extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'property_type',
        'location',
        'price',
        'key_features',
        'tone',
        'generated_description',
        'readability_score',
        'seo_score',
        'overall_score',
        'word_count',
        'character_count',
        'sentence_count',
        'average_sentence_length',
        'keyword_mentions',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'readability_score' => 'integer',
        'seo_score' => 'integer',
        'overall_score' => 'integer',
        'word_count' => 'integer',
        'character_count' => 'integer',
        'sentence_count' => 'integer',
        'average_sentence_length' => 'decimal:1',
        'keyword_mentions' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope to get recent descriptions
     */
    public function scopeRecent(Builder $query, int $limit = 20): Builder
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    /**
     * Scope to filter by property type
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('property_type', $type);
    }

    /**
     * Scope to filter by tone
     */
    public function scopeByTone(Builder $query, string $tone): Builder
    {
        return $query->where('tone', $tone);
    }

    /**
     * Scope to get high-scoring descriptions
     */
    public function scopeHighQuality(Builder $query, int $minScore = 70): Builder
    {
        return $query->where('overall_score', '>=', $minScore);
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        return '₦' . number_format($this->price, 2);
    }

    /**
     * Get short description preview
     */
    public function getShortDescriptionAttribute(): string
    {
        return strlen($this->generated_description) > 100
            ? substr($this->generated_description, 0, 100) . '...'
            : $this->generated_description;
    }

    /**
     * Get human-readable time
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get score badge color
     */
    public function getScoreBadgeColorAttribute(): string
    {
        return match(true) {
            $this->overall_score >= 80 => 'green',
            $this->overall_score >= 60 => 'blue',
            $this->overall_score >= 40 => 'yellow',
            default => 'red',
        };
    }
}
