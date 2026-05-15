<?php

namespace App\Models;

use App\Models\User;
use App\Models\SEOPeriod;
use App\Models\Conversation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SEO extends Model
{
    use HasFactory;

    protected $table = 's_e_o_s';
    protected $guarded = [];

    /**
     * Get the conversation that owns the SEO
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Get all of the seo_periods for the SEO
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function seo_periods(): HasMany
    {
        return $this->hasMany(SEOPeriod::class, 's_e_o_id');
    }

    /**
     * Get the user that owns the SEO
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
