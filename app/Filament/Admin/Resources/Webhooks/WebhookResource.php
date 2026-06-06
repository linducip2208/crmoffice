<?php

namespace App\Filament\Admin\Resources\Webhooks;

use App\Models\Webhook;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WebhookResource extends Resource
{
    protected static ?string $model = Webhook::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBolt;

    protected static ?string $navigationLabel = 'Webhooks';

    protected static string|\UnitEnum|null $navigationGroup = 'Integrasi';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('event')
                ->options([
                    'invoice.paid' => 'invoice.paid',
                    'invoice.overdue' => 'invoice.overdue',
                    'lead.created' => 'lead.created',
                    'lead.converted' => 'lead.converted',
                    'ticket.opened' => 'ticket.opened',
                    'ticket.resolved' => 'ticket.resolved',
                    'estimate.accepted' => 'estimate.accepted',
                    'payment.received' => 'payment.received',
                ])
                ->required(),
            TextInput::make('url')->url()->required()->maxLength(500),
            TextInput::make('secret')
                ->password()
                ->revealable()
                ->required()
                ->maxLength(120)
                ->helperText('HMAC-SHA256 signing secret sent via X-Signature header.'),
            Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('event')->badge()->searchable(),
            TextColumn::make('url')->limit(40)->copyable(),
            IconColumn::make('is_active')->boolean(),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWebhooks::route('/'),
            'create' => Pages\CreateWebhook::route('/create'),
            'edit' => Pages\EditWebhook::route('/{record}/edit'),
        ];
    }
}
