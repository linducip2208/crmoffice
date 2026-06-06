<?php

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;

test('blog flow: create category, create post, publish', function () {
    $author = User::factory()->create(['name' => 'Blog Author']);

    $category = BlogCategory::create([
        'name' => 'CRM Tips',
        'slug' => 'crm-tips',
        'description' => 'Tips and tricks for CRM',
    ]);

    expect($category->name)->toBe('CRM Tips');
    expect($category->slug)->toBe('crm-tips');

    $post = BlogPost::create([
        'title' => '5 Ways to Improve Your Sales Pipeline',
        'slug' => 'improve-sales-pipeline',
        'content' => '<p>Here are 5 proven ways to improve your sales pipeline...</p>',
        'excerpt' => 'Learn how to optimize your sales pipeline',
        'category_id' => $category->id,
        'author_id' => $author->id,
        'is_published' => true,
        'published_at' => now(),
        'meta_title' => 'Improve Sales Pipeline',
        'meta_description' => '5 proven ways to improve your sales pipeline',
    ]);

    expect($post->title)->toBe('5 Ways to Improve Your Sales Pipeline');
    expect($post->is_published)->toBeTrue();
    expect($post->category_id)->toBe($category->id);
    expect($post->author_id)->toBe($author->id);
});

test('blog flow: published scope returns only published posts', function () {
    $author = User::factory()->create();
    $category = BlogCategory::create(['name' => 'News', 'slug' => 'news']);

    BlogPost::create([
        'title' => 'Published Post',
        'slug' => 'published-post',
        'content' => 'Content A',
        'excerpt' => 'Excerpt A',
        'category_id' => $category->id,
        'author_id' => $author->id,
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);

    BlogPost::create([
        'title' => 'Draft Post',
        'slug' => 'draft-post',
        'content' => 'Content B',
        'excerpt' => 'Excerpt B',
        'category_id' => $category->id,
        'author_id' => $author->id,
        'is_published' => false,
        'published_at' => null,
    ]);

    BlogPost::create([
        'title' => 'Future Post',
        'slug' => 'future-post',
        'content' => 'Content C',
        'excerpt' => 'Excerpt C',
        'category_id' => $category->id,
        'author_id' => $author->id,
        'is_published' => true,
        'published_at' => now()->addDay(),
    ]);

    $published = BlogPost::published()->get();

    expect($published)->toHaveCount(1);
    expect($published->first()->title)->toBe('Published Post');
});

test('blog flow: post relationships are correct', function () {
    $author = User::factory()->create(['name' => 'John Author']);
    $category = BlogCategory::create(['name' => 'Guides', 'slug' => 'guides']);

    $post = BlogPost::create([
        'title' => 'CRM Setup Guide',
        'slug' => 'crm-setup-guide',
        'content' => '<p>Step by step guide...</p>',
        'excerpt' => 'Setup your CRM in 10 minutes',
        'category_id' => $category->id,
        'author_id' => $author->id,
        'is_published' => true,
        'published_at' => now(),
    ]);

    expect($post->category->name)->toBe('Guides');
    expect($post->author->name)->toBe('John Author');
    expect($category->posts()->count())->toBe(1);
});
