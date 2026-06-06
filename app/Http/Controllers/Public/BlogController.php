<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;

class BlogController extends Controller
{
    public function index()
    {
        $posts = BlogPost::with(['category', 'author'])
            ->published()
            ->latest('published_at')
            ->paginate(9);

        $categories = BlogCategory::withCount('posts')->orderBy('name')->get();
        $recentPosts = BlogPost::published()->latest('published_at')->limit(5)->get();

        $seoTitle = 'Blog — Tips & Update CRM untuk Bisnis Indonesia';
        $seoDescription = 'Artikel, tips, dan update seputar CRM, manajemen klien, pipeline sales, dan produktivitas bisnis. Dibuat oleh tim crmoffice.';

        return view('public.blog.index', compact('posts', 'categories', 'recentPosts', 'seoTitle', 'seoDescription'));
    }

    public function show(string $slug)
    {
        $post = BlogPost::with(['category', 'author'])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $categories = BlogCategory::withCount('posts')->orderBy('name')->get();
        $recentPosts = BlogPost::published()
            ->latest('published_at')
            ->where('id', '!=', $post->id)
            ->limit(5)
            ->get();

        $seoTitle = ($post->meta_title ?: $post->title) . ' — Blog crmoffice';
        $seoDescription = $post->meta_description ?: \Illuminate\Support\Str::limit(strip_tags($post->excerpt ?: $post->content), 160);
        $canonical = route('blog.show', $post->slug);

        return view('public.blog.show', compact('post', 'categories', 'recentPosts', 'seoTitle', 'seoDescription', 'canonical'));
    }

    public function category(string $slug)
    {
        $category = BlogCategory::where('slug', $slug)->firstOrFail();

        $posts = BlogPost::with(['category', 'author'])
            ->published()
            ->where('category_id', $category->id)
            ->latest('published_at')
            ->paginate(9);

        $categories = BlogCategory::withCount('posts')->orderBy('name')->get();
        $recentPosts = BlogPost::published()->latest('published_at')->limit(5)->get();

        $seoTitle = 'Kategori: ' . $category->name . ' — Blog crmoffice';
        $seoDescription = $category->description ?: 'Artikel dalam kategori ' . $category->name . ' seputar CRM dan manajemen bisnis.';

        return view('public.blog.category', compact('category', 'posts', 'categories', 'recentPosts', 'seoTitle', 'seoDescription'));
    }
}
