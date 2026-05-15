<?php

namespace App\Models;

use App\Models\SEOPeriod;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SEOItem extends Model
{
    use HasFactory;

    protected $table = 's_e_o_items';
    protected $guarded = [];

    /**
     * Get the seo_period that owns the SEOItem
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function seo_period(): BelongsTo
    {
        return $this->belongsTo(SEOPeriod::class, 's_e_o_period_id');
    }

    public function getMediaExtensionAttribute(): string
    {
        return strtolower(pathinfo((string) $this->media_url, PATHINFO_EXTENSION));
    }

    public function getMediaKindAttribute(): string
    {
        $extension = $this->media_extension;

        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'], true)) {
            return 'image';
        }

        if ($extension === 'pdf') {
            return 'pdf';
        }

        if (in_array($extension, ['mp4', 'webm', 'mov', 'avi'], true)) {
            return 'video';
        }

        return 'file';
    }

    public function getMediaDisplayNameAttribute(): string
    {
        return basename((string) $this->media_url) ?: '-';
    }

    public function getResolvedMediaUrlAttribute(): ?string
    {
        $mediaUrl = trim((string) $this->media_url);

        if ($mediaUrl === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $mediaUrl)) {
            return $mediaUrl;
        }

        if (str_starts_with($mediaUrl, '//')) {
            return 'https:' . $mediaUrl;
        }

        $baseUrl = rtrim((string) config('services.seo_media_base_url'), '/');
        if ($baseUrl !== '') {
            return $baseUrl . '/' . ltrim($mediaUrl, '/');
        }

        if (str_starts_with($mediaUrl, 'storage/')) {
            return url($mediaUrl);
        }

        if (str_starts_with($mediaUrl, '/')) {
            return url($mediaUrl);
        }

        return url(Storage::url($mediaUrl));
    }

    public function getMediaExistsLocallyAttribute(): bool
    {
        $mediaUrl = trim((string) $this->media_url);

        return $mediaUrl !== ''
            && ! preg_match('#^(https?:)?//#i', $mediaUrl)
            && Storage::disk('public')->exists(ltrim($mediaUrl, '/'));
    }
}
