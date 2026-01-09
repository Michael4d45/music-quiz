<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use Michael4d45\FilamentSystemInfo\Widgets\SystemInfoWidget as BaseSystemInfoWidget;

class SystemInfoWidget extends BaseSystemInfoWidget
{
    protected array $packages = [
        [
            'name' => 'laravel/framework',
            'displayName' => 'Laravel Version',
            'icon' => 'heroicon-o-cpu-chip',
            'type' => 'packagist',
        ],
        [
            'name' => 'php',
            'displayName' => 'PHP Version',
            'icon' => 'heroicon-o-code-bracket',
            'type' => 'php',
        ],
        [
            'name' => 'filament/filament',
            'displayName' => 'Filament Version',
            'icon' => 'heroicon-o-squares-2x2',
            'type' => 'packagist',
        ],
    ];

    protected string|null $heading = 'System Information';

    protected null|string $pollingInterval = '60s';

    protected bool $showDeploymentInfo = true;

    protected string $releaseInfoPath = '.release-info';
}
