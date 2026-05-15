<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\SEO;
use App\Models\SEOItem;
use App\Models\NotifSEO;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SEOPeriod extends Model
{
    use HasFactory;

    protected $table = 's_e_o_periods';
    protected $guarded = [];

    /**
     * Get the seo that owns the SEOPeriod
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function seo_main(): BelongsTo
    {
        return $this->belongsTo(SEO::class, 's_e_o_id');
    }

    /**
     * Get all of the seo_items for the SEOPeriod
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function seo_items(): HasMany
    {
        return $this->hasMany(SEOItem::class, 's_e_o_period_id');
    }

    public function convertDate(int $date, int $month, int $year): Carbon
    {
        if (!checkdate($month, $date, $year)) {
            throw new InvalidArgumentException("Invalid date: {$year}-{$month}-{$date}");
        }

        return Carbon::createFromDate($year, $month, $date)->startOfDay();
    }

    public function getDisplayDateAttribute(): string
    {
        $day = (int) ($this->date ?: 1);
        $month = (int) ($this->month ?: 1);
        $year = (int) ($this->year ?: now()->year);

        if (! checkdate($month, $day, $year)) {
            $day = min($day, Carbon::createFromDate($year, $month, 1)->daysInMonth);
        }

        return Carbon::createFromDate($year, $month, $day)->format('d/m/Y');
    }

    public function getDisplayMonthAttribute(): string
    {
        $month = (int) ($this->month ?: 1);
        $year = (int) ($this->year ?: now()->year);

        return Carbon::createFromDate($year, $month, 1)->format('m/Y');
    }

    public function getSortTimestampAttribute(): int
    {
        $day = (int) ($this->date ?: 1);
        $month = (int) ($this->month ?: 1);
        $year = (int) ($this->year ?: now()->year);

        if (! checkdate($month, $day, $year)) {
            $day = min($day, Carbon::createFromDate($year, $month, 1)->daysInMonth);
        }

        return Carbon::createFromDate($year, $month, $day)->timestamp;
    }

    public function markAsPaid(): self
    {
        $this->update(['is_paid' => true]);
        return $this;
    }
	
    /**
     * Get all of the notif_seos for the SEOPeriod
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function notif_seos(): HasMany
    {
        return $this->hasMany(NotifSEO::class, 's_e_o_period_id');
    }
}
