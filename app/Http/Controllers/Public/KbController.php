<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\KbArticle;
use App\Models\KbCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class KbController extends Controller
{
    public function index()
    {
        $categories = KbCategory::query()
            ->where('is_public', true)
            ->whereNull('parent_id')
            ->orderBy('order')
            ->withCount(['articles' => fn ($q) => $q->where('is_published', true)])
            ->get();

        return view('public.kb.index', compact('categories'));
    }

    public function category(string $category)
    {
        $cat = KbCategory::query()
            ->where('slug', $category)
            ->where('is_public', true)
            ->firstOrFail();

        $articles = $cat->articles()
            ->where('is_published', true)
            ->orderBy('published_at', 'desc')
            ->get();

        return view('public.kb.category', compact('cat', 'articles'));
    }

    public function article(string $category, string $article)
    {
        $cat = KbCategory::query()
            ->where('slug', $category)
            ->where('is_public', true)
            ->firstOrFail();

        $art = KbArticle::query()
            ->where('slug', $article)
            ->where('category_id', $cat->id)
            ->where('is_published', true)
            ->firstOrFail();

        $art->increment('view_count');

        return view('public.kb.article', compact('cat', 'art'));
    }

    public function search(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $results = $q !== ''
            ? KbArticle::where('is_published', true)
                ->where(function ($qq) use ($q) {
                    $qq->where('title', 'like', "%$q%")
                       ->orWhere('content', 'like', "%$q%")
                       ->orWhere('excerpt', 'like', "%$q%");
                })
                ->with('category')
                ->limit(20)
                ->get()
            : collect();

        return view('public.kb.search', compact('q', 'results'));
    }

    public function vote(Request $request, string $category, string $article): RedirectResponse
    {
        $request->validate(['helpful' => 'required|boolean']);

        $cat = KbCategory::where('slug', $category)->firstOrFail();
        $art = KbArticle::where('slug', $article)->where('category_id', $cat->id)->firstOrFail();

        $existing = $art->votes()->where('voter_ip', $request->ip())->first();
        if ($existing) {
            return back();
        }

        $art->votes()->create([
            'voter_ip' => $request->ip(),
            'helpful' => (bool) $request->input('helpful'),
            'voted_at' => now(),
        ]);

        if ($request->boolean('helpful')) {
            $art->increment('helpful_count');
        } else {
            $art->increment('unhelpful_count');
        }

        return back()->with('success', 'Terima kasih atas feedbacknya.');
    }
}
