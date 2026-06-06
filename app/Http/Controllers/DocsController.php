<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Environment\Environment;

class DocsController extends Controller
{
    private const DOCS_DIR = 'docs';

    public function index()
    {
        $docs = $this->listDocs();

        return view('docs.index', [
            'docs' => $docs,
            'currentSlug' => null,
        ]);
    }

    public function show(string $slug)
    {
        $docs = $this->listDocs();
        $doc = collect($docs)->firstWhere('slug', $slug);

        if (! $doc) {
            abort(404);
        }

        $html = $this->renderMarkdown($doc['filename']);

        return view('docs.show', [
            'docs' => $docs,
            'currentSlug' => $slug,
            'doc' => $doc,
            'html' => $html,
        ]);
    }

    private function listDocs(): array
    {
        return Cache::remember('docs.list', 60, function () {
            $path = base_path(self::DOCS_DIR);
            if (! File::isDirectory($path)) {
                return [];
            }

            return collect(File::files($path))
                ->filter(fn ($f) => str_ends_with($f->getFilename(), '.md'))
                ->map(function ($f) {
                    $filename = $f->getFilename();
                    $name = pathinfo($filename, PATHINFO_FILENAME);
                    $title = $this->extractTitle($f->getPathname()) ?: Str::headline(preg_replace('/^[0-9]+-/', '', $name));
                    $slug = Str::slug($name);

                    return [
                        'slug' => $slug,
                        'filename' => $filename,
                        'name' => $name,
                        'title' => $title,
                        'sort_key' => $name,
                    ];
                })
                ->sortBy('sort_key')
                ->values()
                ->all();
        });
    }

    private function extractTitle(string $path): ?string
    {
        $lines = file($path, FILE_IGNORE_NEW_LINES);
        foreach ($lines as $line) {
            if (preg_match('/^#\s+(.+)$/', trim($line), $m)) {
                return trim(preg_replace('/^\d+\s*—\s*/u', '', $m[1]));
            }
        }

        return null;
    }

    private function renderMarkdown(string $filename): string
    {
        return Cache::remember("docs.render:$filename", 60, function () use ($filename) {
            $path = base_path(self::DOCS_DIR . DIRECTORY_SEPARATOR . $filename);
            if (! File::exists($path)) {
                return '';
            }

            $environment = new Environment([
                'html_input' => 'allow',
                'allow_unsafe_links' => false,
                'heading_permalink' => [
                    'symbol' => '#',
                    'apply_id_to_heading' => true,
                ],
            ]);
            $environment->addExtension(new CommonMarkCoreExtension());
            $environment->addExtension(new GithubFlavoredMarkdownExtension());
            $environment->addExtension(new TableExtension());
            $environment->addExtension(new HeadingPermalinkExtension());

            $converter = new MarkdownConverter($environment);
            $md = File::get($path);

            // Rewrite intra-doc links (e.g., "./05-MODULES.md" → "/docs/05-modules")
            $md = preg_replace_callback('/\]\(\.?\/?([0-9A-Za-z\-_]+)\.md(\#[^)]+)?\)/u', function ($m) {
                $target = Str::slug($m[1]);
                $anchor = $m[2] ?? '';

                return "](/docs/{$target}{$anchor})";
            }, $md);

            return $converter->convert($md)->getContent();
        });
    }
}
