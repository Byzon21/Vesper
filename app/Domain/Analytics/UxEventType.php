<?php

declare(strict_types=1);

namespace App\Domain\Analytics;

/**
 * Types d'événements d'analytics UX collectés.
 */
enum UxEventType: string
{
    case PageView = 'page_view';
    case Click = 'click';
    case Scroll = 'scroll';
    case Focus = 'focus';
    case Error = 'error';
    case Idle = 'idle';
    case RageClick = 'rage_click';
    case DeadClick = 'dead_click';
}
