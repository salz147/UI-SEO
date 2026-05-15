<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use App\Models\SEO;
use App\Models\User;
use App\Models\SEOItem;
use App\Models\SEOPeriod;
use App\Models\Conversation;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;

class SEOService
{
    /**
     * Get all marketings (Tim Closing)
     */
    public function getMarketings()
    {
        return User::whereHas('department', function ($query) {
                $query->where('nama', 'Tim Closing');
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * Get all SEO team members
     */
    public function getSEOTeam()
    {
        return User::whereHas('department', function ($query) {
                $query->where('nama', 'Tim Seo');
            })
            ->orderBy('name')
            ->get();
    }

    public function getConversationsByMarketing(int $marketingId)
    {
        return Conversation::query()
            ->where('user_id', $marketingId)
            ->select(['id', 'nama', 'judul', 'hp'])
            ->orderByDesc('updated_at')
            ->limit(300)
            ->get();
    }

    /**
     * Get package names from existing SEO data.
     */
    public function getPackageOptions(): array
    {
        $defaultPackages = [
            'SEO Web Dalam Bahasa Indonesia',
            'SEO Web Dalam Bahasa Inggris',
            'SEO Web Luar Bahasa Indonesia',
            'SEO Web Luar Bahasa Inggris',
        ];

        $packages = SEO::query()
            ->whereNotNull('package')
            ->distinct()
            ->orderBy('package')
            ->pluck('package')
            ->map(fn ($package) => $this->normalizePackageName($package))
            ->filter()
            ->values()
            ->all();

        return collect($defaultPackages)
            ->merge($packages)
            ->unique()
            ->values()
            ->all();
    }

    private function normalizePackageName(?string $package): ?string
    {
        $package = trim((string) $package);

        if ($package === '') {
            return null;
        }

        $normalized = mb_strtolower($package);
        $knownPackages = [
            'seo web dalam bahasa indonesia' => 'SEO Web Dalam Bahasa Indonesia',
            'seo web dalam bahasa inggris' => 'SEO Web Dalam Bahasa Inggris',
            'seo web luar bahasa indonesia' => 'SEO Web Luar Bahasa Indonesia',
            'seo web luar bahasa inggris' => 'SEO Web Luar Bahasa Inggris',
        ];

        return $knownPackages[$normalized] ?? $package;
    }

    /**
     * Filter conversations by marketing team
     */
    public function filterConversationByMarketing(int $marketingId)
    {
        $marketing = User::with('conversations')->findOrFail($marketingId);
        return $marketing->conversations;
    }

    /**
     * Store new SEO
     */
    public function storeSEO(array $data): SEO
    {
        try {
            return DB::transaction(function() use($data) {
                $seoData = $data;
                $monthReserved = $seoData['month_reserved'] ?? 0;
                $startingMonth = $seoData['starting_month'] ?? null;
                
                unset($seoData['month_reserved']);
                unset($seoData['starting_month']);

                // Create SEO record
                $seo = SEO::create($seoData);

                if ($monthReserved <= 0) {
                    return $seo;
                }

                // Create periods based on reserved months
                $billDate = $data['month_bill_at'] ?? now()->toDateString();
                $billDay = (int) Carbon::parse($billDate)->format('d');

                if ($startingMonth) {
                    $start = Carbon::createFromFormat('Y-m', $startingMonth)->startOfMonth();
                } else {
                    $start = Carbon::parse($billDate)->startOfMonth();
                }

                $start = $start->day(min($billDay, $start->daysInMonth));

                $billMonth = (int) Carbon::parse($billDate)->format('m');
                for ($i = 0; $i < $monthReserved; $i++) {
                    $date = $start->copy()->addMonths($i);
                    $billNow = $billMonth === $date->month;

                    SEOPeriod::create([
                        's_e_o_id' => $seo->id,
                        'month' => $date->month,
                        'year' => $date->year,
                        'month_active' => $i + 1,
                        'date' => $billDay,
                        'is_followed_up' => false,
                        'is_billing_schedule' => $billNow,
                        'is_paid' => false,
                    ]);
                }

                return $seo;
            });
        } catch (Exception $e) {
            Log::error('Error storing SEO: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update SEO
     */
    public function updateSEO(int $id, array $data): SEO
    {
        try {
            $seo = SEO::findOrFail($id);
            $seo->update($data);
            return $seo;
        } catch (Exception $e) {
            Log::error('Error updating SEO: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete SEO (soft delete)
     */
    public function deleteSEO(int $id): bool
    {
        try {
            $seo = SEO::findOrFail($id);
            $seo->update(['is_active' => false]);
            return true;
        } catch (Exception $e) {
            Log::error('Error deleting SEO: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Store SEO Period
     */
    public function storeSEOPeriod(array $data): SEOPeriod
    {
        try {
            return DB::transaction(function() use($data) {
                $period = SEOPeriod::create($data);
                
                Log::info('SEO Period created', ['period_id' => $period->id]);
                
                return $period;
            });
        } catch (Exception $e) {
            Log::error('Error storing SEO Period: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update SEO Period
     */
    public function updateSEOPeriod(int $id, array $data): SEOPeriod
    {
        try {
            $period = SEOPeriod::findOrFail($id);

            if (! empty($data['is_billing_schedule'])) {
                SEOPeriod::where('s_e_o_id', $period->s_e_o_id)
                    ->where('id', '!=', $period->id)
                    ->update(['is_billing_schedule' => false]);
            }

            $period->update($data);
            return $period;
        } catch (Exception $e) {
            Log::error('Error updating SEO Period: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete SEO Period
     */
    public function deleteSEOPeriod(int $id): bool
    {
        try {
            $period = SEOPeriod::findOrFail($id);
            $period->delete();
            return true;
        } catch (Exception $e) {
            Log::error('Error deleting SEO Period: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Store SEO Item
     */
    public function storeSEOItem(array $data): SEOItem
    {
        try {
            return SEOItem::create($data);
        } catch (Exception $e) {
            Log::error('Error storing SEO Item: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Bulk store SEO Items
     */
    public function storeSEOItems(int $periodId, array $items): array
    {
        try {
            return DB::transaction(function() use($periodId, $items) {
                $createdItems = [];
                
                foreach ($items as $item) {
                    $item['s_e_o_period_id'] = $periodId;
                    unset($item['seo_period_id']);
                    $createdItems[] = SEOItem::create($item);
                }
                
                return $createdItems;
            });
        } catch (Exception $e) {
            Log::error('Error storing SEO Items: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get active SEO period
     */
    public function getActiveSEOPeriod(int $seoId): ?SEOPeriod
    {
        return SEOPeriod::where('s_e_o_id', $seoId)
            ->where('is_billing_schedule', true)
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->first();
    }

    /**
     * Get SEO with relationships
     */
    public function getSEOWithPeriods(int $id): ?SEO
    {
        return SEO::with(['seo_periods.seo_items', 'user', 'conversation'])
            ->findOrFail($id);
    }

    /**
     * Mark period as paid
     */
    public function markPeriodAsPaid(int $periodId): bool
    {
        try {
            $period = SEOPeriod::findOrFail($periodId);
            $period->markAsPaid();
            return true;
        } catch (Exception $e) {
            Log::error('Error marking period as paid: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get statistics
     */
    public function getStatistics(array $hiddenDomains = [])
    {
        $excludeHiddenSEO = function ($query) use ($hiddenDomains) {
            $query->when($hiddenDomains, fn ($query) => $query->whereNotIn('domain', $hiddenDomains));
        };

        return [
            'total_seo' => SEO::where('is_active', true)
                ->when($hiddenDomains, fn ($query) => $query->whereNotIn('domain', $hiddenDomains))
                ->count(),
            'active_periods' => SEOPeriod::where('is_billing_schedule', true)
                ->when($hiddenDomains, fn ($query) => $query->whereHas('seo_main', $excludeHiddenSEO))
                ->count(),
            'paid_periods' => SEOPeriod::where('is_paid', true)
                ->when($hiddenDomains, fn ($query) => $query->whereHas('seo_main', $excludeHiddenSEO))
                ->count(),
            'total_items' => SEOItem::query()
                ->when($hiddenDomains, fn ($query) => $query->whereHas('seo_period.seo_main', $excludeHiddenSEO))
                ->count(),
            'approved_items' => SEOItem::query()
                ->when($hiddenDomains, fn ($query) => $query->whereHas('seo_period.seo_main', $excludeHiddenSEO))
                ->count(),
        ];
    }

    public function getOperationalSummary(array $hiddenDomains = [], ?int $seoUserId = null): array
    {
        $dashboard = $this->getDashboardData($hiddenDomains, $seoUserId);

        return [
            'total_seo' => $dashboard['total_seo'],
            'needs_review' => $dashboard['counts']['needs_review'],
            'report_pending' => $dashboard['counts']['report_pending'],
            'active_now' => $dashboard['counts']['active_now'],
        ];
    }

    public function getDashboardData(array $hiddenDomains = [], ?int $seoUserId = null): array
    {
        $today = now()->startOfDay();
        $nextSevenDays = $today->copy()->addDays(7);
        $cutoff = $this->getHistoricalCutoffDate();

        $seoCount = $this->baseSEOQuery($hiddenDomains, $seoUserId)->count();
        $periods = $this->loadDashboardPeriods($hiddenDomains, $seoUserId);

        $activeNow = $periods
            ->filter(fn (SEOPeriod $period) => (bool) $period->is_billing_schedule)
            ->sortByDesc('sort_timestamp')
            ->values();

        $needsReview = $periods
            ->filter(fn (SEOPeriod $period) => $period->dashboard_date->lt($today)
                && ! $period->is_paid
                && (! $cutoff || $period->dashboard_date->gt($cutoff)))
            ->sortBy('sort_timestamp')
            ->values();

        $reportPending = $periods
            ->filter(fn (SEOPeriod $period) => $period->dashboard_date->lte($today)
                && ! $period->is_followed_up
                && (! $cutoff || $period->dashboard_date->gt($cutoff)))
            ->sortBy('sort_timestamp')
            ->values();

        $dueSoon = $periods
            ->filter(fn (SEOPeriod $period) => $period->dashboard_date->betweenIncluded($today, $nextSevenDays) && ! $period->is_billing_schedule)
            ->sortBy('sort_timestamp')
            ->values();

        $seoTeamSummary = $periods
            ->groupBy(fn (SEOPeriod $period) => $period->seo_main?->user?->name ?? 'Belum Ada Tim SEO')
            ->map(function (Collection $group, string $teamName) use ($today) {
                return [
                    'team_name' => $teamName,
                    'clients' => $group->pluck('seo_main.id')->filter()->unique()->count(),
                    'active' => $group->where('is_billing_schedule', true)->count(),
                    'needs_review' => $group->filter(fn (SEOPeriod $period) => $period->dashboard_date->lt($today) && ! $period->is_paid)->count(),
                    'report_pending' => $group->filter(fn (SEOPeriod $period) => $period->dashboard_date->lte($today) && ! $period->is_followed_up)->count(),
                ];
            })
            ->sortByDesc('needs_review')
            ->values();

        return [
            'total_seo' => $seoCount,
            'counts' => [
                'active_now' => $activeNow->count(),
                'needs_review' => $needsReview->count(),
                'report_pending' => $reportPending->count(),
                'due_soon' => $dueSoon->count(),
            ],
            'active_now' => $activeNow->take(6),
            'needs_review' => $needsReview->take(8),
            'report_pending' => $reportPending->take(8),
            'due_soon' => $dueSoon->take(8),
            'seo_team_summary' => $seoTeamSummary->take(10),
        ];
    }

    public function addSEOPeriods(SEO $seo, array $data): array
    {
        try {
            return DB::transaction(function () use ($seo, $data) {
                $start = Carbon::parse($data['start_date'])->startOfDay();
                $amount = (int) $data['month_reserved'];
                $makeFirstActive = (bool) ($data['is_billing_schedule'] ?? false);
                $isPaid = (bool) ($data['is_paid'] ?? false);
                $lastMonthActive = (int) $seo->seo_periods()->max('month_active');
                $created = [];

                if ($makeFirstActive) {
                    $seo->seo_periods()->update(['is_billing_schedule' => false]);
                }

                for ($i = 0; $i < $amount; $i++) {
                    $periodDate = $start->copy()->addMonths($i);

                    $created[] = SEOPeriod::create([
                        's_e_o_id' => $seo->id,
                        'month_active' => $lastMonthActive + $i + 1,
                        'date' => $periodDate->day,
                        'month' => $periodDate->month,
                        'year' => $periodDate->year,
                        'is_followed_up' => false,
                        'is_billing_schedule' => $makeFirstActive && $i === 0,
                        'is_paid' => $isPaid,
                    ]);
                }

                return $created;
            });
        } catch (Exception $e) {
            Log::error('Error adding SEO periods: ' . $e->getMessage());
            throw $e;
        }
    }

    private function baseSEOQuery(array $hiddenDomains = [], ?int $seoUserId = null)
    {
        return SEO::query()
            ->where('is_active', true)
            ->when($seoUserId, fn ($query) => $query->where('user_id', $seoUserId))
            ->when($hiddenDomains, fn ($query) => $query->whereNotIn('domain', $hiddenDomains));
    }

    private function getHistoricalCutoffDate(): ?\Carbon\Carbon
    {
        $cutoff = env('SEO_HISTORICAL_CUTOFF', '2026-04-30');

        if (! $cutoff) {
            return null;
        }

        try {
            return Carbon::parse($cutoff)->endOfDay();
        } catch (Exception $e) {
            return null;
        }
    }

    private function loadDashboardPeriods(array $hiddenDomains = [], ?int $seoUserId = null): Collection
    {
        return SEOPeriod::with(['seo_main.user', 'seo_main.conversation.marketing', 'seo_items'])
            ->whereHas('seo_main', function ($query) use ($hiddenDomains, $seoUserId) {
                $query->where('is_active', true)
                    ->when($seoUserId, fn ($query) => $query->where('user_id', $seoUserId))
                    ->when($hiddenDomains, fn ($query) => $query->whereNotIn('domain', $hiddenDomains));
            })
            ->get()
            ->map(function (SEOPeriod $period) {
                $period->dashboard_date = Carbon::createFromTimestamp($period->sort_timestamp)->startOfDay();

                return $period;
            });
    }
}
