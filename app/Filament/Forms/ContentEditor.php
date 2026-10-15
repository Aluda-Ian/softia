<?php

namespace App\Filament\Forms;

use Filament\Forms\Components\RichEditor;

/**
 * Rich text editor for pages and news, with images stored publicly.
 */
class ContentEditor
{
    public static function make(string $name, string $directory): RichEditor
    {
        return RichEditor::make($name)
            ->toolbarButtons([
                ['h2', 'h3', 'bold', 'italic', 'link'],
                ['bulletList', 'orderedList', 'blockquote'],
                ['attachFiles'],
                ['undo', 'redo'],
            ])
            ->fileAttachmentsDisk('public')
            ->fileAttachmentsDirectory($directory)
            ->fileAttachmentsVisibility('public')
            ->columnSpanFull();
    }
}
