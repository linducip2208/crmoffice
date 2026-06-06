<?php

namespace App\Filament\Admin\Resources\Providers\Schemas;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\File;

class ProviderForm
{
    protected static function loadPresets(string $type): array
    {
        $dir = storage_path("app/{$type}-presets");

        if (!File::isDirectory($dir)) {
            return [];
        }

        $presets = [];
        foreach (File::files($dir) as $file) {
            if ($file->getExtension() !== 'json') {
                continue;
            }
            $data = json_decode($file->getContents(), true);
            if (is_array($data)) {
                $presets[$file->getBasename('.json')] = $data['name'] ?? $file->getBasename('.json');
            }
        }

        return $presets;
    }

    protected static function loadPresetData(string $type, string $key): ?array
    {
        $path = storage_path("app/{$type}-presets/{$key}.json");

        if (!File::exists($path)) {
            return null;
        }

        return json_decode(File::get($path), true);
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Quick Setup from Preset')
                ->description('Pilih preset untuk auto-fill konfigurasi provider. Tersedia preset untuk pembayaran (Midtrans, Xendit, Stripe) dan AI (DeepSeek, Groq, Gemini, Ollama).')
                ->schema([
                    Select::make('preset_type_selector')
                        ->label('Provider Type')
                        ->options([
                            'llm' => 'AI / LLM Provider',
                            'payment' => 'Payment Gateway',
                            'mail' => 'Email Sending',
                        ])
                        ->placeholder('Pilih tipe dulu...')
                        ->live()
                        ->afterStateUpdated(function (Set $set, ?string $state) {
                            $set('preset_selector', null);
                            $set('type', $state);
                            $set('api_format', null);
                            $set('base_url', null);
                            $set('extra_config', []);
                        }),

                    Select::make('preset_selector')
                        ->label('Provider Preset')
                        ->placeholder('Pilih preset...')
                        ->options(fn (Get $get): array => static::loadPresets($get('preset_type_selector') ?? $get('type') ?: ''))
                        ->hidden(fn (Get $get): bool => empty($get('preset_type_selector')) && empty($get('type')))
                        ->live()
                        ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                            if (!$state) {
                                return;
                            }
                            $type = $get('preset_type_selector') ?? $get('type');
                            $preset = static::loadPresetData($type, $state);
                            if (!$preset) {
                                return;
                            }

                            $set('name', $preset['name'] ?? '');
                            $set('api_format', $preset['api_format'] ?? '');
                            $set('base_url', $preset['base_url'] ?? '');

                            $extraConfig = $preset['extra_config'] ?? [];
                            $set('extra_config', $extraConfig);
                            $set('preset_notes', $preset['notes'] ?? '');
                        }),

                    Placeholder::make('preset_notes')
                        ->hidden(fn (Get $get): bool => empty($get('preset_notes')))
                        ->content(fn (Get $get): string => (string) $get('preset_notes')),
                ]),

            Section::make('Provider Identity')->schema([
                Grid::make(2)->schema([
                    TextInput::make('name')
                        ->label('Display name')
                        ->required()
                        ->maxLength(120)
                        ->placeholder('e.g., DeepSeek, Groq, Midtrans Production'),

                    Select::make('type')
                        ->options([
                            'payment' => 'Payment Gateway',
                            'mail' => 'Email Sending',
                            'mail_inbound' => 'Inbound Email (IMAP/webhook)',
                            'sms' => 'SMS Gateway',
                            'storage' => 'File Storage (S3-compatible)',
                            'llm' => 'LLM / AI Provider',
                            'currency_rate' => 'Currency Rate Feed',
                        ])
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Set $set, string $state) {
                            $set('api_format', null);
                            $set('extra_config', []);
                            $set('preset_selector', null);
                            $set('preset_type_selector', null);
                            $set('preset_notes', null);
                        }),

                    Select::make('api_format')
                        ->label('API Format')
                        ->required()
                        ->live()
                        ->options(fn (Get $get) => match ($get('type')) {
                            'payment' => [
                                'redirect_flow' => 'Redirect Flow (Midtrans Snap, Stripe Checkout, Xendit Invoice)',
                                'embed_flow' => 'Embed Flow (Stripe Elements, Braintree)',
                                'qr_flow' => 'QR Flow (QRIS, PromptPay, VietQR)',
                            ],
                            'mail' => [
                                'smtp' => 'SMTP (any server)',
                                'rest_api' => 'REST API (Mailgun, SendGrid, Postmark, SES, Resend)',
                            ],
                            'mail_inbound' => [
                                'imap_poll' => 'IMAP Poll',
                                'webhook_route' => 'Webhook Route (Mailgun Routes / Postmark / SendGrid Inbound)',
                            ],
                            'sms' => [
                                'rest_sms' => 'REST SMS (Twilio, Vonage, MessageBird, Zenziva)',
                            ],
                            'storage' => [
                                's3_compatible' => 'S3-Compatible (AWS / R2 / Wasabi / B2 / MinIO / DO Spaces)',
                            ],
                            'llm' => [
                                'openai_compatible' => 'OpenAI-Compatible (DeepSeek, Groq, OpenAI, Mistral, Ollama, vLLM)',
                                'anthropic' => 'Anthropic Native Format (Claude)',
                                'gemini' => 'Google Gemini Native',
                            ],
                            'currency_rate' => [
                                'json_feed' => 'JSON Feed',
                            ],
                            default => [],
                        }),

                    TextInput::make('priority')
                        ->label('Priority (lower = preferred when multiple active)')
                        ->numeric()
                        ->default(0),
                ]),
                Toggle::make('is_active')->label('Active')->default(true),
            ]),

            Section::make('Endpoint & Authentication')->schema([
                TextInput::make('base_url')
                    ->url()
                    ->placeholder('https://api.example.com')
                    ->helperText(fn (Get $get) => match ($get('type')) {
                        'llm' => match ($get('api_format')) {
                            'openai_compatible' => 'Contoh: https://api.deepseek.com  |  https://api.groq.com/openai/v1  |  https://openrouter.ai/api/v1  |  http://localhost:11434 (Ollama)',
                            'anthropic' => 'Contoh: https://api.anthropic.com',
                            'gemini' => 'Contoh: https://generativelanguage.googleapis.com/v1beta/openai',
                            default => null,
                        },
                        'payment' => 'Contoh: https://api.midtrans.com  |  https://api.stripe.com  |  https://api.xendit.co',
                        default => null,
                    })
                    ->columnSpanFull(),

                TextInput::make('api_key')
                    ->label('API Key / Secret')
                    ->password()
                    ->revealable()
                    ->helperText(fn (Get $get) => match ($get('api_format')) {
                        'openai_compatible' => 'API key dari dashboard provider. Untuk Ollama (localhost) bisa dikosongkan.',
                        'anthropic' => 'API key dari console.anthropic.com',
                        'gemini' => 'API key gratis dari aistudio.google.com',
                        default => 'Encrypted at rest. Only owner can view plaintext.',
                    })
                    ->dehydrated(fn ($state) => filled($state))
                    ->columnSpanFull(),

                KeyValue::make('extra_headers')
                    ->label('Extra HTTP Headers')
                    ->columnSpanFull()
                    ->keyLabel('Header')
                    ->valueLabel('Value')
                    ->helperText(fn (Get $get) => match ($get('api_format')) {
                        'openai_compatible' => 'Untuk OpenRouter: tambah HTTP-Referer + X-Title. Lainnya biasanya kosong.',
                        default => 'e.g., X-Account-ID, X-Region',
                    }),
            ])->collapsible(),

            Section::make('Adapter Configuration')->schema([
                Select::make('extra_config.default_model')
                    ->label('Default Model')
                    ->options(fn (Get $get) => static::modelSuggestions($get('api_format')))
                    ->searchable()
                    ->nullable()
                    ->hidden(fn (Get $get) => $get('type') !== 'llm')
                    ->helperText('Pilih model AI yang akan dipakai. Bisa di-override per fitur di AI Settings.')
                    ->live(),

                KeyValue::make('extra_config')
                    ->label('Additional Config (Key-Value)')
                    ->columnSpanFull()
                    ->keyLabel('Key')
                    ->valueLabel('Value')
                    ->helperText(fn (Get $get) => match ($get('type')) {
                        'llm' => match ($get('api_format')) {
                            'openai_compatible' => "default_model (sudah diset di atas).\nBisa tambah: temperature, max_tokens.\nModel murah rekomendasi:\n• DeepSeek: deepseek-chat (Rp 4/1M input)\n• Groq: deepseek-r1-distill-llama-70b (GRATIS)\n• OpenRouter: google/gemini-2.0-flash-001 (GRATIS)\n• Gemini: gemini-2.0-flash (GRATIS)\n• Ollama: deepseek-r1:8b (GRATIS, self-host)",
                            'anthropic' => "default_model (e.g., claude-3-5-sonnet-latest).\nTambahan: anthropic_version, max_tokens.",
                            'gemini' => "default_model (e.g., gemini-2.0-flash, gemini-1.5-pro).\nGemini Flash GRATIS 1500 req/hari.",
                            default => null,
                        },
                        'payment' => "redirect_flow: create_intent_endpoint, callback_signature_header, callback_signature_algo, status_field_path, reference_field_path, amount_field_path\nembed_flow: create_session_endpoint, publishable_key\nqr_flow: create_qr_endpoint, qr_field_path",
                        'mail' => "smtp: host, port, encryption (tls/ssl), username, from_address, from_name\nrest_api: send_endpoint, from_address, from_name",
                        'storage' => "s3_compatible: bucket, region, access_key, secret_key, use_path_style (true/false)",
                        default => null,
                    })
                    ->live(),
            ])
            ->description(fn (Get $get) => match ($get('type')) {
                'llm' => 'AI provider siap pakai. Semua pakai format OpenAI-compatible — ganti base_url + API key, model apa saja bisa.',
                'payment' => 'Konfigurasi gateway pembayaran. Format adapter menentukan flow (redirect/embed/QR).',
                default => 'Konfigurasi provider. Lihat contoh di bawah.',
            })
            ->collapsible(),
        ]);
    }

    protected static function modelSuggestions(?string $format): array
    {
        return match ($format) {
            'openai_compatible' => [
                '' => '--- Pilih model ---',
                'deepseek-chat' => 'DeepSeek V3 — $0.27/M input (paling murah)',
                'deepseek-reasoner' => 'DeepSeek R1 — reasoning model',
                'deepseek-r1-distill-llama-70b' => 'DeepSeek R1 Distill (via Groq) — GRATIS',
                'gpt-4o-mini' => 'OpenAI GPT-4o mini — $0.15/M input',
                'gpt-4o' => 'OpenAI GPT-4o — $2.50/M input',
                'gemini-2.0-flash' => 'Google Gemini Flash — GRATIS',
                'gemini-2.5-flash' => 'Google Gemini 2.5 Flash — $0.15/M',
                'google/gemini-2.0-flash-001' => 'Gemini via OpenRouter — GRATIS',
                'llama-3.3-70b-versatile' => 'Llama 3.3 70B (via Groq) — GRATIS',
                'qwen-2.5-32b' => 'Qwen 2.5 32B (via Groq) — GRATIS',
                'llama3.1:8b' => 'Llama 3.1 8B (via Ollama) — GRATIS (local)',
                'deepseek-r1:8b' => 'DeepSeek R1 8B (via Ollama) — GRATIS (local)',
                'mistral-small-3.1-24b' => 'Mistral Small via OpenRouter — $0.10/M input',
            ],
            'anthropic' => [
                '' => '--- Pilih model ---',
                'claude-3-5-haiku-latest' => 'Claude 3.5 Haiku — $0.80/M input',
                'claude-3-5-sonnet-latest' => 'Claude 3.5 Sonnet — $3.00/M input',
                'claude-3-opus-latest' => 'Claude 3 Opus — $15.00/M input',
            ],
            'gemini' => [
                '' => '--- Pilih model ---',
                'gemini-2.0-flash' => 'Gemini 2.0 Flash — GRATIS (1500 req/hari)',
                'gemini-2.5-flash' => 'Gemini 2.5 Flash — $0.15/M input',
                'gemini-2.5-pro' => 'Gemini 2.5 Pro — $1.25/M input',
                'gemini-1.5-pro' => 'Gemini 1.5 Pro — $2.50/M input',
            ],
            default => [],
        };
    }
}
