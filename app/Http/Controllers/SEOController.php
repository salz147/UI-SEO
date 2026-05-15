<?php

namespace App\Http\Controllers;

use App\Models\SEO;
use App\Models\SEOItem;
use App\Models\SEOPeriod;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\SEOService;
use App\Http\Requests\SEOStoreRequest;
use App\Http\Requests\SEOUpdateRequest;
use App\Http\Requests\SEOPeriodStoreRequest;
use App\Http\Requests\SEOPeriodUpdateRequest;
use App\Http\Requests\SEOItemStoreRequest;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;

class SEOController extends Controller
{
    protected SEOService $seoService;

    public function __construct(SEOService $seoService)
    {
        $this->seoService = $seoService;
    }

    /**
     * Display listing of SEO records
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $role = $user->role ?? null;
        $search = trim((string) $request->query('search', ''));
        $focus = trim((string) $request->query('focus', ''));
        $hiddenDomains = array_merge($this->getHiddenDomains(), $this->getUserHiddenDomains());
        $seoUserId = $role === 'seo' ? $user->id : null;

        $seos = $this->buildSEOIndexQuery($search, $focus, $hiddenDomains, $seoUserId)
            ->paginate(15)
            ->withQueryString();

        $summary = $this->seoService->getOperationalSummary($hiddenDomains, $seoUserId);

        return view('seo.index', compact('seos', 'summary', 'search', 'focus'));
    }

    public function logging(Request $request): View
    {
        abort_unless(app()->environment('local') || ((auth()->user()->role ?? null) === 'superadmin'), 403);

        $logDirectory = storage_path('logs');
        $logFiles = collect(File::files($logDirectory))
            ->filter(fn ($file) => strtolower($file->getExtension()) === 'log')
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->values()
            ->map(fn ($file) => [
                'name' => $file->getFilename(),
                'path' => $file->getPathname(),
                'size' => $file->getSize(),
                'modified' => Carbon::createFromTimestamp($file->getMTime()),
            ]);

        $requestedFile = basename((string) $request->query('file', ''));
        $selectedLog = $logFiles->firstWhere('name', $requestedFile) ?? $logFiles->first();
        $lineCount = max(50, min(2000, (int) $request->query('lines', 500)));
        $logContent = $selectedLog ? $this->readLastLogLines($selectedLog['path'], $lineCount) : '';

        return view('seo.logging', compact('logFiles', 'selectedLog', 'lineCount', 'logContent'));
    }

    /**
     * Show form for creating new SEO
     */
    public function create(): View
    {
        $marketings = $this->seoService->getMarketings();
        $timSeos = $this->seoService->getSEOTeam();
        $packageOptions = $this->seoService->getPackageOptions();
        
        return view('seo.create', compact('marketings', 'timSeos', 'packageOptions'));
    }

    /**
     * Store new SEO record
     */
    public function store(SEOStoreRequest $request): RedirectResponse
    {
        try {
            $this->seoService->storeSEO($request->validated());
            return redirect()->route('seo.index')
                ->with('success', 'Data SEO berhasil disimpan');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * Show form for editing SEO
     */
    public function edit(SEO $seo): View
    {
        abort_unless($this->canAccessSeo($seo) || $this->canPreviewSeoEditLocally(), 403);

        $seo->load(['seo_periods.seo_items', 'user', 'conversation.marketing']);
        $timSeos = $this->seoService->getSEOTeam();
        $packageOptions = $this->seoService->getPackageOptions();
        $marketings = $this->seoService->getMarketings();
        
        return view('seo.edit', compact('seo', 'timSeos', 'packageOptions', 'marketings'));
    }

    /**
     * Update SEO record
     */
    public function update(SEOUpdateRequest $request, SEO $seo): RedirectResponse
    {
        abort_unless($this->canAccessSeo($seo), 403);

        try {
            $this->seoService->updateSEO($seo->id, $request->validated());
            return redirect()->route('seo.index')
                ->with('success', 'Data SEO berhasil diupdate');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengupdate data: ' . $e->getMessage());
        }
    }

    /**
     * Delete SEO record
     */
    public function destroy(SEO $seo): RedirectResponse
    {
        abort_unless($this->canAccessSeo($seo), 403);

        try {
            $this->seoService->deleteSEO($seo->id);
            return redirect()->route('seo.index')
                ->with('success', 'Data SEO berhasil dihapus');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    public function storeSEOPeriod(Request $request, SEO $seo): RedirectResponse
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'month_reserved' => 'required|integer|min:1|max:24',
            'is_billing_schedule' => 'nullable|boolean',
            'is_paid' => 'nullable|boolean',
        ]);

        try {
            $this->seoService->addSEOPeriods($seo, $validated);

            return back()->with('success', 'Periode SEO berhasil ditambahkan');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menambahkan periode: ' . $e->getMessage());
        }
    }

    public function hideLocal(SEO $seo): RedirectResponse
    {
        $domains = collect($this->getLocalHiddenDomains())
            ->push($seo->domain)
            ->filter()
            ->unique()
            ->values()
            ->all();

        File::ensureDirectoryExists(dirname($this->localHiddenDomainsPath()));
        File::put($this->localHiddenDomainsPath(), json_encode($domains, JSON_PRETTY_PRINT));

        return back()->with('success', "{$seo->domain} disembunyikan dari tampilan lokal");
    }

    /**
     * Get SEO items for a period (AJAX)
     */
    public function getSEOItems(SEOPeriod $period): JsonResponse
    {
        try {
            abort_unless($this->canAccessPeriod($period), 403);

        $items = $period->seo_items()->get();
            return response()->json([
                'success' => true,
                'data' => $items
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * View SEO items
     */
    public function viewSEOItems(SEOPeriod $period): View
    {
        abort_unless($this->canAccessPeriod($period), 403);
        $items = $period->seo_items()->paginate(10);
        
        return view('seo.view_items', compact('period', 'items'));
    }

    /**
     * Store SEO items
     */
    public function storeSEOItems(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'seo_period_id' => 'required|integer|exists:s_e_o_periods,id',
                'items' => 'required|array',
                'items.*.type' => 'required|string|max:100',
                'items.*.media_url' => 'required|string|max:100',
            ]);

            $items = $this->seoService->storeSEOItems(
                $validated['seo_period_id'],
                $validated['items']
            );

            return response()->json([
                'success' => true,
                'message' => 'Items berhasil disimpan',
                'data' => $items
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getConversationsByMarketing(int $marketing): JsonResponse
    {
        $conversations = $this->seoService->getConversationsByMarketing($marketing)
            ->map(function ($conversation) {
                return [
                    'id' => $conversation->id,
                    'label' => $conversation->nama ?: 'Tanpa Nama',
                ];
            });

        return response()->json($conversations);
    }

    public function uploadReportMedia(Request $request, SEOPeriod $period): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|string|max:100',
            'report_file' => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
        ]);

        try {
            $path = $request->file('report_file')->store('seo_items', 'public');

            SEOItem::create([
                's_e_o_period_id' => $period->id,
                'type' => $validated['type'],
                'media_url' => $path,
            ]);

            return back()->with('success', 'Media laporan berhasil ditambahkan');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal upload media laporan: ' . $e->getMessage());
        }
    }

    public function sendReport(SEOPeriod $period): RedirectResponse
    {
        $period->load(['seo_main', 'seo_items']);

        if ($period->seo_items->count() === 0) {
            return back()->with('error', 'Tambahkan minimal satu media laporan sebelum mengirim laporan');
        }

        $domain = $period->seo_main->domain ?? 'SEO';
        $periodLabel = $period->display_date;

        $period->update(['is_followed_up' => true]);

        return back()->with(
            'success',
            "Laporan {$domain} periode {$periodLabel} berhasil ditandai terkirim. Total media: {$period->seo_items->count()} file."
        );
    }

    /**
     * Delete SEO item
     */
    public function deleteSEOItem(SEOItem $item): JsonResponse
    {
        try {
            $item->delete();
            return response()->json([
                'success' => true,
                'message' => 'Item berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get SEO period for editing
     */
    public function editSEOPeriod(SEOPeriod $period): View
    {
        $period->load(['seo_main.seo_periods', 'seo_items']);

        return view('seo.edit_period', compact('period'));
    }

    /**
     * Update SEO period
     */
    public function updateSEOPeriod(SEOPeriodUpdateRequest $request, SEOPeriod $period): RedirectResponse
    {
        try {
            $validated = $request->validated();
            foreach (['is_billing_schedule', 'is_paid', 'is_followed_up'] as $field) {
                $validated[$field] = $request->boolean($field);
            }

            $this->seoService->updateSEOPeriod($period->id, $validated);
            return back()->with('success', 'Period berhasil diupdate');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengupdate period: ' . $e->getMessage());
        }
    }

    /**
     * Delete SEO period
     */
    public function deleteSEOPeriod(SEOPeriod $period): RedirectResponse
    {
        try {
            $seoId = $period->s_e_o_id;
            $this->seoService->deleteSEOPeriod($period->id);
            return redirect()->route('seo.edit', $seoId)
                ->with('success', 'Period berhasil dihapus');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus period: ' . $e->getMessage());
        }
    }

    /**
     * Get active SEO period
     */
    public function getActivePeriodSEO(SEO $seo): JsonResponse
    {
        try {
            $period = $this->seoService->getActiveSEOPeriod($seo->id);
            
            return response()->json([
                'success' => true,
                'data' => $period
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark period as paid
     */
    public function markPeriodAsPaid(SEOPeriod $period): RedirectResponse
    {
        try {
            $this->seoService->markPeriodAsPaid($period->id);
            return back()->with('success', 'Periode berhasil ditandai sebagai dibayar');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menandai periode sebagai dibayar: ' . $e->getMessage());
        }
    }

    /**
     * Get statistics
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = $this->seoService->getStatistics();
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function buildSEOIndexQuery(string $search, string $focus, array $hiddenDomains, ?int $seoUserId)
    {
        $today = now()->startOfDay()->toDateString();
        $nextSevenDays = now()->startOfDay()->addDays(7)->toDateString();

        return SEO::with(['seo_periods.seo_items', 'user', 'conversation.marketing'])
            ->where('is_active', true)
            ->when($seoUserId, fn ($query) => $query->where('user_id', $seoUserId))
            ->when($hiddenDomains, fn ($query) => $query->whereNotIn('domain', $hiddenDomains))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('domain', 'like', "%{$search}%")
                        ->orWhere('package', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('conversation', fn ($conversationQuery) => $conversationQuery->where('nama', 'like', "%{$search}%"))
                        ->orWhereHas('conversation.marketing', fn ($marketingQuery) => $marketingQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($focus === 'needs_review', function ($query) use ($today) {
                $query->whereHas('seo_periods', function ($periodQuery) use ($today) {
                    $periodQuery
                        ->whereRaw($this->periodDateExpression() . ' < ?', [$today])
                        ->where('is_paid', false);
                });
            })
            ->when($focus === 'report_pending', function ($query) use ($today) {
                $query->whereHas('seo_periods', function ($periodQuery) use ($today) {
                    $periodQuery
                        ->whereRaw($this->periodDateExpression() . ' <= ?', [$today])
                        ->where('is_followed_up', false);
                });
            })
            ->when($focus === 'active_now', fn ($query) => $query->whereHas('seo_periods', fn ($periodQuery) => $periodQuery->where('is_billing_schedule', true)))
            ->when($focus === 'due_soon', function ($query) use ($today, $nextSevenDays) {
                $query->whereHas('seo_periods', function ($periodQuery) use ($today, $nextSevenDays) {
                    $periodQuery
                        ->whereRaw($this->periodDateExpression() . ' between ? and ?', [$today, $nextSevenDays]);
                });
            })
            ->orderByDesc('id');
    }

    private function getLocalHiddenDomains(): array
    {
        $path = $this->localHiddenDomainsPath();

        if (! File::exists($path)) {
            return [];
        }

        $domains = json_decode((string) File::get($path), true);

        return is_array($domains) ? $domains : [];
    }

    private function localHiddenDomainsPath(): string
    {
        return storage_path('app/seo_hidden_domains.json');
    }

    private function getHiddenDomains(): array
    {
        return collect(explode(',', (string) env('SEO_HIDDEN_DOMAINS', '')))
            ->merge($this->getLocalHiddenDomains())
            ->map(fn ($domain) => trim($domain))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function getUserHiddenDomains(): array
    {
        $user = auth()->user();
        if (! $user) {
            return [];
        }

        $path = $this->userHiddenDomainsPath($user->id);
        if (! File::exists($path)) {
            return [];
        }

        $domains = json_decode((string) File::get($path), true);

        if (! is_array($domains)) {
            return [];
        }

        return collect($domains)
            ->map(fn ($domain) => trim($domain))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function userHiddenDomainsPath(int $userId): string
    {
        return storage_path("app/seo_hidden_domains_user_{$userId}.json");
    }

    private function canAccessSeo(SEO $seo): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        if (($user->role ?? null) === 'superadmin') {
            return true;
        }

        if (in_array($seo->domain, $this->getUserHiddenDomains(), true)) {
            return false;
        }

        if (($user->role ?? null) === 'seo') {
            return $seo->user_id === $user->id;
        }

        return true;
    }

    private function canPreviewSeoEditLocally(): bool
    {
        return app()->environment('local');
    }

    private function canAccessPeriod(SEOPeriod $period): bool
    {
        $period->loadMissing('seo_main');
        return $period->seo_main ? $this->canAccessSeo($period->seo_main) : false;
    }

    private function periodDateExpression(): string
    {
        return "STR_TO_DATE(CONCAT(`year`, '-', LPAD(`month`, 2, '0'), '-', LPAD(`date`, 2, '0')), '%Y-%m-%d')";
    }

    private function readLastLogLines(string $path, int $lineCount): string
    {
        if (! File::exists($path) || ! is_readable($path)) {
            return '';
        }

        $file = new \SplFileObject($path, 'r');
        $file->seek(PHP_INT_MAX);

        $startLine = max(0, $file->key() - $lineCount);
        $file->seek($startLine);

        $lines = [];
        while (! $file->eof()) {
            $lines[] = rtrim((string) $file->fgets(), "\r\n");
        }

        return trim(implode(PHP_EOL, $lines));
    }
}
