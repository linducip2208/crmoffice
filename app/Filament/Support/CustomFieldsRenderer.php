<?php

namespace App\Filament\Support;

use App\Models\CustomField;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

/**
 * Renders dynamic Filament form components based on custom_fields table per entity.
 * Use inside a Resource form Schema with:
 *   ...CustomFieldsRenderer::section('clients')
 *
 * Values stored as JSON in `custom_fields` column on the entity (denormalized for fast read).
 */
class CustomFieldsRenderer
{
    public static function section(string $entity): array
    {
        $fields = CustomField::query()
            ->where('entity', $entity)
            ->orderBy('order')
            ->get();

        if ($fields->isEmpty()) {
            return [];
        }

        $components = $fields->map(fn ($f) => self::buildComponent($f))->filter()->all();

        return [
            Section::make('Custom Fields')
                ->description('Configured via Settings → Custom Fields')
                ->schema($components)
                ->columns(2)
                ->collapsed(),
        ];
    }

    private static function buildComponent(CustomField $field)
    {
        $name = "custom_fields.{$field->field_key}";
        $label = $field->label;
        $required = $field->is_required;

        return match ($field->type) {
            'text' => TextInput::make($name)->label($label)->required($required)->maxLength(255),
            'textarea' => Textarea::make($name)->label($label)->required($required)->rows(3)->columnSpanFull(),
            'number' => TextInput::make($name)->label($label)->required($required)->numeric(),
            'decimal' => TextInput::make($name)->label($label)->required($required)->numeric()->step(0.01),
            'date' => DatePicker::make($name)->label($label)->required($required)->displayFormat('d M Y'),
            'datetime' => DateTimePicker::make($name)->label($label)->required($required),
            'select' => Select::make($name)->label($label)->required($required)
                ->options(self::pairOptions($field->options ?? [])),
            'multi_select' => Select::make($name)->label($label)->required($required)
                ->multiple()->options(self::pairOptions($field->options ?? [])),
            'checkbox' => Checkbox::make($name)->label($label)->columnSpanFull(),
            'url' => TextInput::make($name)->label($label)->required($required)->url(),
            'email' => TextInput::make($name)->label($label)->required($required)->email(),
            'file' => FileUpload::make($name)->label($label)->required($required)->disk('local')->directory("custom-fields/{$field->entity}")->columnSpanFull(),
            default => TextInput::make($name)->label($label)->required($required),
        };
    }

    private static function pairOptions(array $opts): array
    {
        // options can be ['Yes', 'No'] or [{value:'a', label:'A'}, ...]
        $isAssoc = ! empty($opts) && is_array(reset($opts));
        if ($isAssoc) {
            return collect($opts)->mapWithKeys(fn ($o) => [$o['value'] => $o['label'] ?? $o['value']])->toArray();
        }

        return array_combine($opts, $opts);
    }
}
