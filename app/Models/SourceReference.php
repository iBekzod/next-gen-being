<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SourceReference extends Model
{
    use HasFactory;

    protected $table = 'source_references';

    protected $fillable = [
        'post_id',
        'collected_content_id',
        'title',
        'url',
        'author',
        'published_at',
        'accessed_at',
        'domain',
        'citation_style',
        'position_in_post',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'accessed_at' => 'datetime',
    ];

    // Relationships
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function collectedContent()
    {
        return $this->belongsTo(CollectedContent::class, 'collected_content_id');
    }

    // Scopes
    public function scopeByPost($query, $postId)
    {
        return $query->where('post_id', $postId);
    }

    public function scopeByDomain($query, $domain)
    {
        return $query->where('domain', $domain);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('position_in_post');
    }

    public function scopeRecent($query)
    {
        return $query->orderByDesc('published_at');
    }

    // Methods
    public function getDomain(): string
    {
        if ($this->domain) {
            return $this->domain;
        }

        $parsed = parse_url($this->url);
        return $parsed['host'] ?? 'unknown';
    }

    public function formatCitation(): string
    {
        return match($this->citation_style) {
            'apa' => $this->formatAPA(),
            'chicago' => $this->formatChicago(),
            'harvard' => $this->formatHarvard(),
            'inline' => $this->formatInline(),
            default => $this->formatInline(),
        };
    }

    private function formatAPA(): string
    {
        $author = $this->author ?? 'Unknown';
        $year = $this->published_at?->year ?? 'n.d.';
        return "{$author} ({$year}). {$this->title}. Retrieved from {$this->url}";
    }

    private function formatChicago(): string
    {
        $author = $this->author ?? 'Unknown';
        $date = $this->published_at?->format('M d, Y') ?? 'n.d.';
        return "{$author}. \"{$this->title}.\" Accessed {$date}. {$this->url}";
    }

    private function formatHarvard(): string
    {
        $author = $this->author ?? 'Unknown';
        $year = $this->published_at?->year ?? 'n.d.';
        return "{$author}, {$year}. {$this->title}. Available at: {$this->url}";
    }

    private function formatInline(): string
    {
        return "[{$this->title}]({$this->url})";
    }

    public function getSourceName(): string
    {
        return $this->collectedContent?->source->name ?? $this->getDomain();
    }

    public function isRecent(int $days = 7): bool
    {
        if (!$this->published_at) {
            return false;
        }

        return $this->published_at->isAfter(now()->subDays($days));
    }

    public function getAccessedAgo(): string
    {
        if (!$this->accessed_at) {
            return 'Not accessed';
        }

        return $this->accessed_at->diffForHumans();
    }
}
