<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    public const CACHE_MISS = '__SETTING_CACHE_MISS__';
    private const SEO_CACHE_KEYS = [
        'seo_custom_robots',
        'default_meta_image',
        'default_meta_keywords',
        'default_meta_title',
        'default_meta_description',
        'social_links',
        'social_twitter_handle',
        'site_logo',
        'site_name',
        'site_description',
        'support_email',
        'company_name',
    ];

    protected $fillable = [
        'key', 'value', 'type', 'description', 'group', 'is_public'
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    public static function cacheKey(string $key): string
    {
        return "settings." . $key;
    }

    public function getValueAttribute($value)
    {
        return match($this->type) {
            'json' => json_decode($value, true),
            'boolean' => (bool) $value,
            'integer' => (int) $value,
            default => $value,
        };
    }

    public function setValueAttribute($value)
    {
        $this->attributes['value'] = match($this->type) {
            'json' => json_encode($value),
            'boolean' => $value ? '1' : '0',
            default => (string) $value,
        };
    }

    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, $value, string $type = 'string'): self
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type]
        );
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    protected static function booted()
    {
        static::saved(function (self $setting) {
            Cache::forget(static::cacheKey($setting->key));

            if (in_array($setting->key, self::SEO_CACHE_KEYS, true)) {
                Cache::forget('seo:robots.txt');
                Cache::forget('seo:sitemap.xml');
            }
        });

        static::deleted(function (self $setting) {
            Cache::forget(static::cacheKey($setting->key));

            if (in_array($setting->key, self::SEO_CACHE_KEYS, true)) {
                Cache::forget('seo:robots.txt');
                Cache::forget('seo:sitemap.xml');
            }
        });
    }





}



