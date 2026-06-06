<?php

namespace App\Services;

use App\Adapters\Llm\LlmAdapterFactory;
use App\Models\Lead;
use Illuminate\Support\Facades\Log;

class AiLeadScoringService
{
    public function scoreLead(Lead $lead): array
    {
        $factors = $this->ruleBasedFactors($lead);
        $score = $this->calculateScore($factors);

        $llmBoost = null;
        $llmReasoning = null;

        if ($this->isLlmAvailable()) {
            try {
                $llmResult = $this->aiDeepAnalysis($lead);
                if ($llmResult && isset($llmResult['boost'], $llmResult['reasoning'])) {
                    $llmBoost = (int) $llmResult['boost'];
                    $llmReasoning = $llmResult['reasoning'];
                    $score = max(0, min(100, $score + $llmBoost));
                }
            } catch (\Throwable $e) {
                Log::warning('AiLeadScoringService LLM analysis failed, using rule-based only', [
                    'lead_id' => $lead->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $level = match (true) {
            $score >= 70 => 'hot',
            $score >= 40 => 'warm',
            default => 'cold',
        };

        $recommendation = $this->buildRecommendation($level, $factors, $llmReasoning);

        $result = [
            'score' => $score,
            'level' => $level,
            'factors' => $factors->toArray(),
            'recommendation' => $recommendation,
            'ai_boost' => $llmBoost,
            'ai_reasoning' => $llmReasoning,
        ];

        $lead->updateQuietly([
            'lead_score' => $score,
            'lead_score_level' => $level,
            'lead_score_factors' => $result,
        ]);

        return $result;
    }

    public function scoreAll(): array
    {
        $leads = Lead::query()
            ->whereDoesntHave('status', fn ($q) => $q->where('is_won', true)->orWhere('is_lost', true))
            ->get();

        $results = [];
        $count = 0;

        foreach ($leads as $lead) {
            $results[$lead->id] = $this->scoreLead($lead);
            $count++;
        }

        return [
            'total' => $leads->count(),
            'scored' => $count,
            'hot' => count(array_filter($results, fn ($r) => ($r['level'] ?? '') === 'hot')),
            'warm' => count(array_filter($results, fn ($r) => ($r['level'] ?? '') === 'warm')),
            'cold' => count(array_filter($results, fn ($r) => ($r['level'] ?? '') === 'cold')),
            'details' => $results,
        ];
    }

    private function ruleBasedFactors(Lead $lead): \Illuminate\Support\Collection
    {
        $factors = collect();

        $factors->put('source_quality', $this->scoreSourceQuality($lead));
        $factors->put('activity_count', $this->scoreActivityCount($lead));
        $factors->put('recency', $this->scoreRecency($lead));
        $factors->put('company_presence', $this->scoreCompanyPresence($lead));
        $factors->put('email_quality', $this->scoreEmailQuality($lead));
        $factors->put('has_phone', $this->scoreHasPhone($lead));
        $factors->put('notes_depth', $this->scoreNotesDepth($lead));

        return $factors;
    }

    private function calculateScore(\Illuminate\Support\Collection $factors): int
    {
        $weights = [
            'source_quality' => 25,
            'activity_count' => 20,
            'recency' => 15,
            'company_presence' => 15,
            'email_quality' => 10,
            'has_phone' => 5,
            'notes_depth' => 10,
        ];

        $total = 0;
        foreach ($factors as $key => $value) {
            $weight = $weights[$key] ?? 10;
            $total += ($value / 100) * $weight;
        }

        return (int) round($total);
    }

    private function scoreSourceQuality(Lead $lead): int
    {
        $sourceName = strtolower($lead->source?->name ?? '');

        return match (true) {
            str_contains($sourceName, 'web') || str_contains($sourceName, 'website') => 90,
            str_contains($sourceName, 'referral') || str_contains($sourceName, 'refer') => 95,
            str_contains($sourceName, 'form') || str_contains($sourceName, 'landing') => 80,
            str_contains($sourceName, 'manual') => 55,
            str_contains($sourceName, 'import') => 40,
            str_contains($sourceName, 'email') => 65,
            str_contains($sourceName, 'linkedin') || str_contains($sourceName, 'social') => 75,
            empty($sourceName) => 50,
            default => 60,
        };
    }

    private function scoreActivityCount(Lead $lead): int
    {
        $count = $lead->activities()->count();

        return match (true) {
            $count >= 10 => 100,
            $count >= 7 => 90,
            $count >= 5 => 75,
            $count >= 3 => 60,
            $count >= 1 => 40,
            default => 15,
        };
    }

    private function scoreRecency(Lead $lead): int
    {
        if (! $lead->last_activity_at) {
            return 20;
        }

        $days = (int) $lead->last_activity_at->diffInDays(now());

        return match (true) {
            $days <= 1 => 100,
            $days <= 3 => 90,
            $days <= 7 => 70,
            $days <= 14 => 50,
            $days <= 30 => 30,
            default => 10,
        };
    }

    private function scoreCompanyPresence(Lead $lead): int
    {
        $score = 30;

        if ($lead->company && strlen($lead->company) > 2) {
            $score += 30;
        }

        if ($lead->website) {
            $score += 20;
        }

        if ($lead->address || $lead->city) {
            $score += 10;
        }

        if ($lead->estimated_value && $lead->estimated_value > 0) {
            $score += 10;
        }

        return min(100, $score);
    }

    private function scoreEmailQuality(Lead $lead): int
    {
        if (! $lead->email) {
            return 30;
        }

        $freeDomains = [
            'gmail.com', 'yahoo.com', 'outlook.com', 'hotmail.com',
            'aol.com', 'icloud.com', 'protonmail.com', 'mail.com',
        ];

        $domain = strtolower(substr(strrchr($lead->email, '@'), 1));

        if (empty($domain)) {
            return 30;
        }

        if (in_array($domain, $freeDomains)) {
            return 45;
        }

        return 85;
    }

    private function scoreHasPhone(Lead $lead): int
    {
        return $lead->phone ? 100 : 20;
    }

    private function scoreNotesDepth(Lead $lead): int
    {
        $totalWords = $lead->notes()->get()->sum(function ($note) {
            return str_word_count($note->body ?? '', 0);
        });

        $descriptionWords = str_word_count($lead->description ?? '', 0);
        $totalWords += $descriptionWords;

        return match (true) {
            $totalWords >= 200 => 100,
            $totalWords >= 100 => 80,
            $totalWords >= 50 => 60,
            $totalWords >= 20 => 40,
            $totalWords >= 5 => 20,
            default => 10,
        };
    }

    private function isLlmAvailable(): bool
    {
        return LlmAdapterFactory::active() !== null;
    }

    private function aiDeepAnalysis(Lead $lead): ?array
    {
        $adapter = LlmAdapterFactory::active();
        if (! $adapter) {
            return null;
        }

        $activities = $lead->activities()
            ->latest('occurred_at')
            ->limit(10)
            ->get()
            ->map(fn ($a) => "- [{$a->type}] {$a->subject}: {$a->description} ({$a->occurred_at?->diffForHumans()})")
            ->implode("\n");

        $notes = $lead->notes()
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($n) => "- {$n->body}")
            ->implode("\n");

        $messages = [
            ['role' => 'system', 'content' => 'You are a lead quality analyst for a CRM. Analyze the lead data and return ONLY valid JSON in this format: {"boost": -15, "reasoning": "Brief analysis in Indonesian. Explain why you adjusted the score."}. Boost is a number between -20 and +20 that adjusts an already-calculated base score. Positive = better than rule-based, negative = worse. Consider: buying signals in notes, activity quality, note sentiment, and overall conversion potential.'],
            ['role' => 'user', 'content' => "Analyze this lead:\n\nName: {$lead->name}\nCompany: {$lead->company}\nEmail: {$lead->email}\nPhone: {$lead->phone}\nSource: {$lead->source?->name}\nStatus: {$lead->status?->name}\nEstimated Value: {$lead->estimated_value}\nLast Activity: {$lead->last_activity_at?->diffForHumans()}\nDescription: {$lead->description}\n\nRecent Activities:\n{$activities}\n\nNotes:\n{$notes}"],
        ];

        try {
            $response = $adapter->chat($messages, [
                'temperature' => 0.3,
                'max_tokens' => 300,
            ]);

            $json = json_decode($response->content, true);
            if ($json && isset($json['boost'])) {
                return [
                    'boost' => max(-20, min(20, (int) $json['boost'])),
                    'reasoning' => $json['reasoning'] ?? 'AI analysis completed',
                ];
            }

            return null;
        } catch (\Throwable $e) {
            Log::warning('AiLeadScoringService AI deep analysis error: ' . $e->getMessage());

            return null;
        }
    }

    private function buildRecommendation(string $level, \Illuminate\Support\Collection $factors, ?string $aiReasoning): string
    {
        $recommendations = [
            'hot' => 'Prioritaskan follow-up dalam 24 jam. Lead ini menunjukkan sinyal beli kuat. Assign ke sales senior. Siapkan proposal dan demo produk.',
            'warm' => 'Lakukan follow-up minggu ini. Kirimkan konten edukasi dan case study yang relevan. Pantau aktivitas lead secara berkala.',
            'cold' => 'Masukkan ke nurture campaign. Kirimkan newsletter berkala dan konten edukasi. Evaluasi kembali setelah 30 hari atau setelah ada aktivitas baru.',
        ];

        $base = $recommendations[$level] ?? $recommendations['cold'];

        $weakPoints = $factors->filter(fn ($v) => $v < 50)->keys()->all();
        if ($weakPoints) {
            $base .= ' Fokus perbaiki: ' . implode(', ', $weakPoints) . '.';
        }

        if ($aiReasoning) {
            $base .= " (AI insight: {$aiReasoning})";
        }

        return $base;
    }
}
