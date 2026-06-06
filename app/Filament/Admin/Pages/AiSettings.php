<?php

namespace App\Filament\Admin\Pages;

use App\Models\Provider;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\View as ViewField;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\File;

class AiSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?string $navigationLabel = 'AI Settings';

    protected static ?string $title = 'AI Settings';

    protected static string|\UnitEnum|null $navigationGroup = 'Integrasi';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.admin.pages.ai-settings';

    public array $data = [];

    public array $providerPresets = [];

    public array $existingProviderNames = [];

    public function mount(): void
    {
        $this->form->fill($this->loadSettings());
        $this->loadProviderPresets();
    }

    public function loadProviderPresets(): void
    {
        $path = storage_path('app/ai-presets/llm-providers.json');

        if (!File::exists($path)) {
            $this->providerPresets = [];

            return;
        }

        $presets = json_decode(File::get($path), true) ?? [];

        $existing = Provider::where('type', 'llm')
            ->pluck('name')
            ->map(fn ($n) => mb_strtolower($n))
            ->toArray();

        $this->existingProviderNames = $existing;
        $this->providerPresets = $presets;
    }

    public function importPreset(string $key): void
    {
        $path = storage_path('app/ai-presets/llm-providers.json');

        if (!File::exists($path)) {
            Notification::make()
                ->title('Preset file not found.')
                ->danger()
                ->send();

            return;
        }

        $presets = json_decode(File::get($path), true) ?? [];
        $preset = collect($presets)->firstWhere('key', $key);

        if (!$preset) {
            Notification::make()
                ->title("Preset '{$key}' not found.")
                ->danger()
                ->send();

            return;
        }

        $exists = Provider::where('type', 'llm')
            ->where('name', $preset['name'])
            ->exists();

        if ($exists) {
            Notification::make()
                ->title("{$preset['name']} sudah ada.")
                ->body('Provider sudah dikonfigurasi sebelumnya.')
                ->warning()
                ->send();

            return;
        }

        Provider::create([
            'name' => $preset['name'],
            'type' => 'llm',
            'api_format' => $preset['api_format'],
            'base_url' => $preset['base_url'],
            'extra_config' => [
                'default_model' => $preset['recommended_model'] ?? null,
                'models' => $preset['models'] ?? [],
                'pricing_note' => $preset['pricing'] ?? null,
                'tier' => $preset['tier'] ?? null,
            ],
            'extra_headers' => [],
            'is_active' => false,
            'priority' => 0,
        ]);

        $this->loadProviderPresets();

        Notification::make()
            ->title("{$preset['name']} berhasil ditambahkan!")
            ->body('Lengkapi API key di halaman Providers, lalu aktifkan.')
            ->success()
            ->send();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('LLM Provider Status')
                    ->schema([
                        ViewField::make('provider_status')
                            ->view('filament.admin.pages.ai-settings-provider-status', [
                                'hasProvider' => $this->hasActiveProvider(),
                                'providerName' => $this->activeProviderName(),
                                'providerModel' => $this->activeProviderModel(),
                            ]),
                    ]),

                Section::make('Proposal Drafting')
                    ->schema([
                        Toggle::make('proposal_drafting.enabled')
                            ->label('Enable proposal drafting')
                            ->helperText('Generate proposals from client/lead context'),
                        Select::make('proposal_drafting.provider_id')
                            ->label('Provider')
                            ->options($this->llmProviderOptions())
                            ->nullable(),
                        TextInput::make('proposal_drafting.model')
                            ->label('Model')
                            ->placeholder('e.g. gpt-4o')
                            ->nullable(),
                    ])->columns(3),

                Section::make('Ticket Classification')
                    ->schema([
                        Toggle::make('ticket_classify.enabled')
                            ->label('Enable ticket classification')
                            ->helperText('Auto-tag tickets and detect sentiment'),
                        Select::make('ticket_classify.provider_id')
                            ->label('Provider')
                            ->options($this->llmProviderOptions())
                            ->nullable(),
                        TextInput::make('ticket_classify.model')
                            ->label('Model')
                            ->placeholder('e.g. gpt-4o-mini')
                            ->nullable(),
                    ])->columns(3),

                Section::make('Thread Summarization')
                    ->schema([
                        Toggle::make('thread_summarize.enabled')
                            ->label('Enable thread summarization')
                            ->helperText('Summarize long ticket threads'),
                        Select::make('thread_summarize.provider_id')
                            ->label('Provider')
                            ->options($this->llmProviderOptions())
                            ->nullable(),
                        TextInput::make('thread_summarize.model')
                            ->label('Model')
                            ->placeholder('e.g. gpt-4o-mini')
                            ->nullable(),
                    ])->columns(3),

                Section::make('KB Suggestions')
                    ->schema([
                        Toggle::make('kb_suggest.enabled')
                            ->label('Enable KB article suggestions')
                            ->helperText('Suggest related KB articles for tickets'),
                        Select::make('kb_suggest.provider_id')
                            ->label('Provider')
                            ->options($this->llmProviderOptions())
                            ->nullable(),
                        TextInput::make('kb_suggest.model')
                            ->label('Model')
                            ->placeholder('e.g. gpt-4o-mini')
                            ->nullable(),
                    ])->columns(3),

                Section::make('Reply Drafting')
                    ->schema([
                        Toggle::make('reply_draft.enabled')
                            ->label('Enable reply drafting')
                            ->helperText('AI drafts professional replies'),
                        Select::make('reply_draft.provider_id')
                            ->label('Provider')
                            ->options($this->llmProviderOptions())
                            ->nullable(),
                        TextInput::make('reply_draft.model')
                            ->label('Model')
                            ->placeholder('e.g. gpt-4o')
                            ->nullable(),
                    ])->columns(3),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::set('ai_features', $data);

        Notification::make()
            ->title('AI settings saved.')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')->label('Save Settings')->submit('save'),
        ];
    }

    public function hasActiveProvider(): bool
    {
        return Provider::where('type', 'llm')->where('is_active', true)->exists();
    }

    public function activeProviderName(): ?string
    {
        $provider = Provider::where('type', 'llm')
            ->where('is_active', true)
            ->orderBy('priority')
            ->first();

        return $provider?->name;
    }

    public function activeProviderModel(): ?string
    {
        $provider = Provider::where('type', 'llm')
            ->where('is_active', true)
            ->orderBy('priority')
            ->first();

        return $provider?->extra_config['default_model'] ?? null;
    }

    private function llmProviderOptions(): array
    {
        return Provider::where('type', 'llm')
            ->where('is_active', true)
            ->orderBy('priority')
            ->pluck('name', 'id')
            ->toArray();
    }

    private function loadSettings(): array
    {
        $defaults = [
            'proposal_drafting' => ['enabled' => false, 'provider_id' => null, 'model' => null],
            'ticket_classify' => ['enabled' => false, 'provider_id' => null, 'model' => null],
            'thread_summarize' => ['enabled' => false, 'provider_id' => null, 'model' => null],
            'kb_suggest' => ['enabled' => false, 'provider_id' => null, 'model' => null],
            'reply_draft' => ['enabled' => false, 'provider_id' => null, 'model' => null],
        ];

        $saved = Setting::get('ai_features', []);

        return array_merge($defaults, is_array($saved) ? $saved : []);
    }
}
