<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SurveyResponseController extends Controller
{
    public function show(string $token)
    {
        $survey = Survey::where('public_token', $token)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()->toDateString());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()->toDateString());
            })
            ->firstOrFail();

        $survey->load(['questions' => fn ($q) => $q->orderBy('order')]);

        if (session('survey_thank_you_' . $token)) {
            return view('public.surveys.thank-you', compact('survey'));
        }

        return view('public.surveys.show', compact('survey'));
    }

    public function submit(Request $request, string $token): RedirectResponse
    {
        $survey = Survey::where('public_token', $token)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()->toDateString());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()->toDateString());
            })
            ->with('questions')
            ->firstOrFail();

        $rules = [];
        $messages = [];

        foreach ($survey->questions as $q) {
            $key = "q_{$q->id}";

            if ($q->is_required) {
                $rules[$key] = 'required';
                $messages["{$key}.required"] = "Pertanyaan '{$q->question}' wajib diisi.";
            } else {
                $rules[$key] = 'nullable';
            }

            if ($q->type === 'multiple_choice') {
                if ($q->is_required) {
                    $rules[$key] = 'required|array|min:1';
                    $messages["{$key}.required"] = "Pilih minimal satu opsi untuk '{$q->question}'.";
                } else {
                    $rules[$key] = 'nullable|array';
                }
            }

            if ($q->type === 'rating') {
                $rules[$key] = ($q->is_required ? 'required' : 'nullable') . '|integer|min:1|max:5';
            }

            if ($q->type === 'nps') {
                $rules[$key] = ($q->is_required ? 'required' : 'nullable') . '|integer|min:0|max:10';
            }
        }

        $data = $request->validate($rules, $messages);

        $response = $survey->responses()->create([
            'anonymous_token' => Str::random(40),
            'ip_address'       => $request->ip(),
            'submitted_at'     => now(),
        ]);

        foreach ($survey->questions as $q) {
            $value = $data["q_{$q->id}"] ?? null;

            if (is_array($value)) {
                $value = implode(',', $value);
            }

            $response->answers()->create([
                'question_id' => $q->id,
                'answer'      => $value !== null ? (string) $value : null,
            ]);
        }

        return redirect()
            ->route('public.surveys.show', $token)
            ->with('survey_thank_you_' . $token, true);
    }
}
