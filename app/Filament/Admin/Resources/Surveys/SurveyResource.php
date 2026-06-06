<?php

namespace App\Filament\Admin\Resources\Surveys;

use App\Models\Survey;
use BackedEnum;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SurveyResource extends Resource
{
    protected static ?string $model = Survey::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Surveys';

    protected static string|\UnitEnum|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->required()->maxLength(180),
            Textarea::make('description')->rows(3),
            Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->searchable()->sortable(),
            TextColumn::make('responses_count')
                ->counts('responses')
                ->label('Responses')
                ->sortable(),
            IconColumn::make('is_active')->boolean(),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ])
            ->actions([
                Tables\Actions\Action::make('viewResults')
                    ->label('Results')
                    ->icon(Heroicon::OutlinedChartBar)
                    ->color('gray')
                    ->url(fn (Survey $record): string => SurveyResource::getUrl('results', ['record' => $record])),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'   => Pages\ListSurveys::route('/'),
            'create'  => Pages\CreateSurvey::route('/create'),
            'edit'    => Pages\EditSurvey::route('/{record}/edit'),
            'results' => Pages\ViewSurveyResults::route('/{record}/results'),
        ];
    }
}
