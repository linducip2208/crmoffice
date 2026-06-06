<?php

namespace App\Filament\Admin\Resources\Surveys\Pages;

use App\Filament\Admin\Resources\Surveys\SurveyResource;
use App\Models\Survey;
use App\Models\SurveyAnswer;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;

class ViewSurveyResults extends Page
{
    protected static string $resource = SurveyResource::class;

    protected string $view = 'filament.surveys.results';

    public Survey $record;

    public function mount(int|Survey $record): void
    {
        $this->record = $record instanceof Survey
            ? $record->load(['questions', 'responses'])
            : Survey::with(['questions', 'responses.answers'])->findOrFail($record);
    }

    public function getTitle(): string
    {
        return 'Hasil: ' . $this->record->title;
    }

    public function getHeading(): string
    {
        return 'Hasil Survey: ' . $this->record->title;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('edit')
                ->label('Edit Survey')
                ->url(SurveyResource::getUrl('edit', ['record' => $this->record]))
                ->icon('heroicon-o-pencil-square'),

            Action::make('back')
                ->label('Back to Surveys')
                ->url(SurveyResource::getUrl('index'))
                ->color('gray')
                ->icon('heroicon-o-arrow-left'),
        ];
    }

    public function getSubheading(): ?string
    {
        $count = $this->record->responses->count();

        return "{$count} respons diterima" . ($count > 0 ? ' — ringkasan per pertanyaan' : '');
    }

    public function results(): array
    {
        $survey = $this->record;
        $totalResponses = $survey->responses->count();

        if ($totalResponses === 0) {
            return [];
        }

        $results = [];

        foreach ($survey->questions as $question) {
            $answers = SurveyAnswer::whereIn('response_id', $survey->responses->pluck('id'))
                ->where('question_id', $question->id)
                ->get();

            $entry = [
                'id'               => $question->id,
                'question'         => $question->question,
                'type'             => $question->type,
                'is_required'      => $question->is_required,
                'total_responses'  => $answers->count(),
                'distribution'     => [],
                'average'          => null,
                'nps_score'        => null,
                'word_cloud'       => [],
            ];

            if (in_array($question->type, ['text', 'textarea'])) {
                $texts = $answers->pluck('answer')->filter()->values();
                $entry['distribution'] = $texts->countBy()->sortDesc()->take(20)->toArray();
                $entry['word_cloud'] = $texts->take(50)->toArray();
                $entry['total_responses'] = $texts->count();
            } elseif (in_array($question->type, ['single_choice', 'select'])) {
                $counts = [];
                foreach ($answers as $ans) {
                    $val = $ans->answer;
                    if ($val !== null && $val !== '') {
                        $counts[$val] = ($counts[$val] ?? 0) + 1;
                    }
                }
                arsort($counts);
                $distribution = [];
                foreach ($counts as $opt => $cnt) {
                    $distribution[] = [
                        'option'     => $opt,
                        'count'      => $cnt,
                        'percentage' => round(($cnt / $answers->count()) * 100, 1),
                    ];
                }
                $entry['distribution'] = $distribution;
            } elseif ($question->type === 'multiple_choice') {
                $counts = [];
                foreach ($answers as $ans) {
                    $vals = array_filter(array_map('trim', explode(',', (string) $ans->answer)));
                    foreach ($vals as $v) {
                        $counts[$v] = ($counts[$v] ?? 0) + 1;
                    }
                }
                arsort($counts);
                $distribution = [];
                foreach ($counts as $opt => $cnt) {
                    $distribution[] = [
                        'option'     => $opt,
                        'count'      => $cnt,
                        'percentage' => round(($cnt / $answers->count()) * 100, 1),
                    ];
                }
                $entry['distribution'] = $distribution;
            } elseif ($question->type === 'rating') {
                $numerics = $answers->pluck('answer')->map(fn ($v) => (int) $v)->filter(fn ($v) => $v >= 1 && $v <= 5);
                if ($numerics->isNotEmpty()) {
                    $entry['average'] = round($numerics->avg(), 2);
                }
                $counts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
                foreach ($numerics as $v) {
                    $counts[$v] = ($counts[$v] ?? 0) + 1;
                }
                $distribution = [];
                for ($i = 1; $i <= 5; $i++) {
                    $distribution[] = [
                        'option'     => $i,
                        'stars'      => str_repeat('★', $i) . str_repeat('☆', 5 - $i),
                        'count'      => $counts[$i],
                        'percentage' => $numerics->count() ? round(($counts[$i] / $numerics->count()) * 100, 1) : 0,
                    ];
                }
                $entry['distribution'] = $distribution;
            } elseif ($question->type === 'nps') {
                $numerics = $answers->pluck('answer')->map(fn ($v) => (int) $v)->filter(fn ($v) => $v >= 0 && $v <= 10);
                $total = $numerics->count();
                if ($total > 0) {
                    $detractors = $numerics->filter(fn ($v) => $v <= 6)->count();
                    $passives   = $numerics->filter(fn ($v) => $v >= 7 && $v <= 8)->count();
                    $promoters  = $numerics->filter(fn ($v) => $v >= 9)->count();
                    $entry['average']   = round($numerics->avg(), 2);
                    $entry['nps_score'] = (int) round((($promoters - $detractors) / $total) * 100);
                    $entry['detractors'] = $detractors;
                    $entry['passives']   = $passives;
                    $entry['promoters']  = $promoters;
                    $entry['detractor_pct'] = round(($detractors / $total) * 100, 1);
                    $entry['passive_pct']   = round(($passives / $total) * 100, 1);
                    $entry['promoter_pct']  = round(($promoters / $total) * 100, 1);
                }

                $counts = array_fill(0, 11, 0);
                foreach ($numerics as $v) {
                    $counts[$v] = ($counts[$v] ?? 0) + 1;
                }
                $distribution = [];
                for ($i = 0; $i <= 10; $i++) {
                    $distribution[] = [
                        'option'     => $i,
                        'count'      => $counts[$i],
                        'percentage' => $total ? round(($counts[$i] / $total) * 100, 1) : 0,
                        'zone'       => $i <= 6 ? 'detractor' : ($i <= 8 ? 'passive' : 'promoter'),
                    ];
                }
                $entry['distribution'] = $distribution;
            }

            $results[] = $entry;
        }

        return $results;
    }
}
