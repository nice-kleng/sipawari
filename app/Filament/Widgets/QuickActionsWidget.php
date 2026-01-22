<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class QuickActionsWidget extends Widget
{
    protected static string $view = 'filament.widgets.quick-actions-widget';
    protected static ?int $sort = 12;
    protected int | string | array $columnSpan = 'full';
}
