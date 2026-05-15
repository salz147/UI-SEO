<?php

namespace Database\Seeders;

use App\Models\SEO;
use App\Models\SEOPeriod;
use App\Models\SEOItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SEOSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan user tersedia untuk data SEO
        $user1 = User::firstOrCreate(
            ['email' => 'seo@example.com'],
            ['name' => 'SEO User', 'password' => bcrypt('password')]
        );

        $user2 = User::firstOrCreate(
            ['email' => 'seo2@example.com'],
            ['name' => 'SEO User 2', 'password' => bcrypt('password')]
        );

        // Seed test data untuk SEO
        $seo = SEO::create([
            'conversation_id' => null,
            'user_id' => $user1->id,
            'domain' => 'example-seo.com',
            'month_actives' => 0,
            'month_bill_at' => Carbon::parse('2024-01-15')->toDateString(),
            'package' => 'premium',
            'bill_amount' => 500000,
            'is_active' => true,
            'notes' => 'Test SEO Data',
        ]);

        // Create 3 months of periods
        for ($i = 0; $i < 3; $i++) {
            $month = Carbon::now()->addMonths($i);
            
            $period = SEOPeriod::create([
                's_e_o_id' => $seo->id,
                'month' => $month->month,
                'year' => $month->year,
                'month_active' => $i + 1,
                'status' => $i === 0 ? 'active' : 'pending',
                'start_date' => $month->startOfMonth()->toDateString(),
                'end_date' => $month->endOfMonth()->toDateString(),
                'is_paid' => $i === 0,
                'paid_date' => $i === 0 ? Carbon::now()->toDateString() : null,
            ]);

            // Create sample items for each period
            $keywords = [
                'jasa seo jakarta',
                'seo profesional',
                'optimasi website',
                'ranking google',
                'digital marketing',
            ];

            foreach ($keywords as $index => $keyword) {
                SEOItem::create([
                    'seo_period_id' => $period->id,
                    'keyword' => $keyword,
                    'title' => ucwords($keyword) . ' - Layanan Terpercaya',
                    'description' => 'Layanan ' . $keyword . ' berkualitas tinggi untuk meningkatkan ranking website Anda',
                    'media_type' => ['text', 'image', 'video'][array_rand([0, 1, 2])],
                    'media_url' => 'https://example.com/article-' . str_replace(' ', '-', $keyword),
                    'position' => $index + 1,
                    'traffic' => rand(100, 500),
                    'status' => 'approved',
                ]);
            }
        }

        // Create second SEO for testing
        $seo2 = SEO::create([
            'conversation_id' => null,
            'user_id' => $user2->id,
            'domain' => 'another-seo.com',
            'month_actives' => 0,
            'month_bill_at' => Carbon::parse('2024-02-20')->toDateString(),
            'package' => 'basic',
            'bill_amount' => 250000,
            'is_active' => true,
            'notes' => 'Test SEO Data 2',
        ]);

        // Create one period for second SEO
        $period2 = SEOPeriod::create([
            's_e_o_id' => $seo2->id,
            'month' => Carbon::now()->month,
            'year' => Carbon::now()->year,
            'month_active' => 1,
            'status' => 'active',
            'start_date' => Carbon::now()->startOfMonth()->toDateString(),
            'end_date' => Carbon::now()->endOfMonth()->toDateString(),
            'is_paid' => false,
        ]);

        // Add items to second period
        for ($i = 0; $i < 3; $i++) {
            SEOItem::create([
                'seo_period_id' => $period2->id,
                'keyword' => 'keyword test ' . ($i + 1),
                'title' => 'Test Keyword ' . ($i + 1),
                'description' => 'Test description for keyword ' . ($i + 1),
                'media_type' => 'text',
                'position' => $i + 1,
                'traffic' => rand(50, 200),
                'status' => 'pending',
            ]);
        }

        $this->command->info('SEO test data seeded successfully!');
    }
}
