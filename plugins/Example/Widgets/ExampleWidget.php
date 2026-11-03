<?php

namespace Plugins\Example\Widgets;

use App\Dashboard\Widget;
use Illuminate\Http\Request;

/**
 * Example dashboard widget contributed by the Example plugin.
 */
class ExampleWidget extends Widget
{
    public function id(): string
    {
        return 'example.status';
    }

    public function title(): string
    {
        return trans('example::general.widget_title');
    }

    public function view(): string
    {
        return 'example::widgets.status';
    }

    public function order(): int
    {
        return 50;
    }

    public function data(Request $request): array
    {
        return [
            'message' => trans('example::general.widget_message'),
        ];
    }
}
