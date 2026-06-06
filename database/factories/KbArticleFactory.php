<?php

namespace Database\Factories;

use App\Models\KbArticle;
use App\Models\KbCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class KbArticleFactory extends Factory
{
    protected $model = KbArticle::class;

    public function definition(): array
    {
        $title = ucfirst($this->faker->words(6, true));

        return [
            'category_id' => KbCategory::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.$this->faker->unique()->randomNumber(4),
            'excerpt' => $this->faker->sentence(),
            'content' => '<p>' . implode('</p><p>', $this->faker->paragraphs(6)) . '</p>',
            'is_published' => true,
            'published_at' => now()->subDays(rand(0, 60)),
            'author_id' => User::query()->inRandomOrder()->value('id'),
            'view_count' => $this->faker->numberBetween(0, 1000),
        ];
    }
}
