<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReaderLocation extends Model
{
    protected $table = 'reader_locations';

    protected $fillable = [
        'post_id',
        'ip_address',
        'country_code',
        'country_name',
        'state_province',
        'city',
        'latitude',
        'longitude',
        'timezone',
        'isp',
        'reader_count',
        'last_seen_at',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'last_seen_at' => 'datetime',
    ];

    // Relationships
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    // Scopes
    public function scopeByPost($query, int $postId)
    {
        return $query->where('post_id', $postId);
    }

    public function scopeByCountry($query, string $countryCode)
    {
        return $query->where('country_code', $countryCode);
    }

    public function scopeWithCoordinates($query)
    {
        return $query->whereNotNull('latitude')->whereNotNull('longitude');
    }

    public function scopeTopCountries($query, int $limit = 10)
    {
        return $query->orderByDesc('reader_count')->limit($limit);
    }

    // Methods
    public function getLocationString(): string
    {
        $parts = array_filter([
            $this->city,
            $this->state_province,
            $this->country_name,
        ]);

        return implode(', ', $parts) ?: 'Unknown Location';
    }

    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function incrementReaderCount(): void
    {
        $this->increment('reader_count');
        $this->update(['last_seen_at' => now()]);
    }

    public static function getGeoJsonData($postId): array
    {
        $locations = self::byPost($postId)
            ->withCoordinates()
            ->get();

        $features = $locations->map(function ($location) {
            return [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [$location->longitude, $location->latitude],
                ],
                'properties' => [
                    'id' => $location->id,
                    'country' => $location->country_name,
                    'city' => $location->city,
                    'readers' => $location->reader_count,
                    'location' => $location->getLocationString(),
                ],
            ];
        })->toArray();

        return [
            'type' => 'FeatureCollection',
            'features' => $features,
        ];
    }
}
