<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * @phpstan-type LogEntry array{
 *     id: string,
 *     timestamp: Carbon|null,
 *     datetime: string,
 *     level: string,
 *     message: string,
 *     channel: string,
 *     context: array<string, mixed>,
 *     raw: array<string, mixed>
 * }
 */
class LogViewer extends Page implements HasTable
{
    use InteractsWithTable;

    protected static null|string $navigationLabel = 'Log Viewer';

    protected static null|string $title = 'Log Viewer';

    protected static \BackedEnum|string|null $navigationIcon =
        Heroicon::OutlinedDocumentMagnifyingGlass;

    protected string $view = 'filament.pages.log-viewer';

    public null|string $logFilePath = null;

    public bool $isLive = true;

    public int $maxLines = 1000;

    public function table(Table $table): Table
    {
        return $table
            ->records(fn() => $this->getLogEntries())
            ->columns([
                TextColumn::make('datetime')
                    ->label('Time')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('level')
                    ->label('Level')
                    ->badge()
                    ->color(fn($state): string => match ($state) {
                        'ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY' => 'danger',
                        'WARNING', 'NOTICE' => 'warning',
                        'INFO' => 'success',
                        'DEBUG' => 'secondary',
                        default => 'gray',
                    })
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('channel')
                    ->label('Channel')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('message')
                    ->label('Message')
                    ->searchable()
                    ->limit(100)
                    ->tooltip(function (TextColumn $column): null|string {
                        $state = $column->getState();
                        if (is_string($state) && strlen($state) > 100) {
                            return $state;
                        }
                        return null;
                    })
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('context_summary')
                    ->label('Context')
                    ->getStateUsing(function ($record) {
                        if (!is_array($record)) {
                            return '';
                        }
                        $context = $record['context'] ?? [];
                        if (empty($context) || !is_array($context)) {
                            return '';
                        }

                        $summary = [];
                        $innerContext = $context['context'] ?? null;
                        if (is_array($innerContext)) {
                            if (
                                isset($innerContext['method'])
                                && is_string($innerContext['method'])
                            ) {
                                $summary[] = $innerContext['method'];
                            }
                            if (
                                isset($innerContext['path'])
                                && is_string($innerContext['path'])
                            ) {
                                $summary[] = $innerContext['path'];
                            }
                            if (isset($innerContext['status'])) {
                                $summary[] =
                                    'Status: '
                                    . (string) $innerContext['status'];
                            }
                        }

                        return implode(' | ', $summary);
                    })
                    ->limit(80)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('level')
                    ->label('Log Level')
                    ->options([
                        'DEBUG' => 'Debug',
                        'INFO' => 'Info',
                        'NOTICE' => 'Notice',
                        'WARNING' => 'Warning',
                        'ERROR' => 'Error',
                        'CRITICAL' => 'Critical',
                        'ALERT' => 'Alert',
                        'EMERGENCY' => 'Emergency',
                    ])
                    ->query(function ($query, array $data) {
                        // This won't work with custom data, handled in applyFilters
                        return $query;
                    }),

                Filter::make('date_from')
                    ->label('Date From')
                    ->schema([
                        TextInput::make('date_from')
                            ->label('From Date')
                            ->type('datetime-local'),
                    ])
                    ->query(function ($query, array $data) {
                        // Handled in applyFilters
                        return $query;
                    }),

                Filter::make('date_to')
                    ->label('Date To')
                    ->schema([
                        TextInput::make('date_to')
                            ->label('To Date')
                            ->type('datetime-local'),
                    ])
                    ->query(function ($query, array $data) {
                        // Handled in applyFilters
                        return $query;
                    }),
            ])
            ->defaultSort('datetime', 'desc')
            ->poll($this->isLive ? '5s' : null)
            ->searchable()
            ->paginated([10, 25, 50, 100])
            ->recordActions([
                ViewAction::make()
                    ->label('View Details')
                    ->modalHeading(
                        fn($record) => (
                            'Log Entry Details - '
                            . ($record['datetime'] ?? 'Unknown')
                        ),
                    )
                    ->modalContent(fn($record) => view('filament.pages.log-viewer-details', [
                        'record' => $record,
                    ]))
                    ->modalWidth('4xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
            ])
            ->headerActions([
                Action::make('toggleLive')
                    ->label(fn() => $this->isLive ? 'Pause' : 'Resume')
                    ->icon(fn() => $this->isLive
                        ? 'heroicon-o-pause'
                        : 'heroicon-o-play')
                    ->color(fn() => $this->isLive ? 'warning' : 'success')
                    ->action(function () {
                        $this->isLive = !$this->isLive;
                        $this->dispatch('$refresh');
                    }),

                Action::make('refresh')
                    ->label('Refresh')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function () {
                        $this->dispatch('$refresh');
                    }),

                Action::make('settings')
                    ->label('Settings')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->form([
                        TextInput::make('maxLines')
                            ->label('Max Lines to Load')
                            ->numeric()
                            ->minValue(100)
                            ->maxValue(100000)
                            ->default(1000)
                            ->helperText(
                                'Number of log entries to load from the file. Lower values improve performance.',
                            ),
                        TextInput::make('logFilePath')
                            ->label('Log File Path')
                            ->helperText(
                                'Relative to storage/logs/ or absolute path',
                            )
                            ->placeholder('laravel.log'),
                    ])
                    ->fillForm([
                        'maxLines' => $this->maxLines,
                        'logFilePath' => $this->logFilePath,
                    ])
                    ->action(function (array $data) {
                        $this->maxLines = (int) ($data['maxLines'] ?? 1000);
                        $this->logFilePath = $data['logFilePath'] ?? null;
                        $this->dispatch('$refresh');
                    }),
            ]);
    }

    /**
     * @return Collection<int, LogEntry>
     */
    protected function getLogEntries(): Collection
    {
        $filePath = $this->getLogFilePath();

        if (!File::exists($filePath)) {
            return collect();
        }

        $lines = File::lines($filePath);
        $entries = collect();
        $lineCount = 0;
        $allLines = [];

        // Collect all lines first
        foreach ($lines as $line) {
            $line = is_string($line) ? trim($line) : '';
            if (empty($line)) {
                continue;
            }
            $allLines[] = $line;
            $lineCount++;
        }

        // Take only the last maxLines entries
        $linesToProcess = array_slice($allLines, -$this->maxLines);

        foreach ($linesToProcess as $line) {
            try {
                $data = json_decode($line, true, 512, JSON_THROW_ON_ERROR);
                if (is_array($data)) {
                    /** @var array<string, mixed> $data */
                    $entries->push($this->normalizeLogEntry($data));
                }
            } catch (\JsonException $e) {
                // Skip invalid JSON lines
                continue;
            }
        }

        // Apply filters
        $entries = $this->applyFilters($entries);

        // Apply search
        $queryParams = request()->query->all();
        $search = $queryParams['tableSearch'] ?? null;
        if (is_string($search) && $search !== '') {
            $entries = $entries->filter(function ($record) use ($search) {
                return (
                    Str::contains(
                        strtolower($record['message']),
                        strtolower($search),
                    )
                    || Str::contains(
                        strtolower($record['level']),
                        strtolower($search),
                    )
                    || Str::contains(
                        strtolower($record['channel']),
                        strtolower($search),
                    )
                );
            });
        }

        // Sort by datetime descending (newest first)
        return $entries->sortByDesc('timestamp')->values();
    }

    /**
     * @param Collection<int, LogEntry> $entries
     * @return Collection<int, LogEntry>
     * @phpstan-return Collection<int, LogEntry>
     */
    protected function applyFilters(Collection $entries): Collection
    {
        // Get filter state from request - Filament stores filters in the query string
        $queryParams = request()->query->all();
        $tableFilters = $queryParams['tableFilters'] ?? [];

        if (is_array($tableFilters)) {
            // Level filter - SelectFilter stores value directly
            if (
                isset($tableFilters['level'])
                && is_string($tableFilters['level'])
                && $tableFilters['level'] !== ''
            ) {
                $entries = $entries->filter(
                    fn($record) => $record['level'] === $tableFilters['level'],
                );
            }

            // Date from filter - Filter with schema stores in nested array
            if (
                isset($tableFilters['date_from'])
                && is_array($tableFilters['date_from'])
            ) {
                $dateFrom = $tableFilters['date_from']['date_from'] ?? null;
                if (is_string($dateFrom) && $dateFrom !== '') {
                    try {
                        $filterDate = Carbon::parse($dateFrom);
                        $entries = $entries->filter(function ($record) use (
                            $filterDate,
                        ) {
                            $timestamp = $record['timestamp'];
                            return $timestamp instanceof Carbon
                            && $timestamp->gte($filterDate);
                        });
                    } catch (\Exception $e) {
                        // Invalid date, ignore filter
                    }
                }
            }

            // Date to filter
            if (
                isset($tableFilters['date_to'])
                && is_array($tableFilters['date_to'])
            ) {
                $dateTo = $tableFilters['date_to']['date_to'] ?? null;
                if (is_string($dateTo) && $dateTo !== '') {
                    try {
                        $filterDate = Carbon::parse($dateTo);
                        $entries = $entries->filter(function ($record) use (
                            $filterDate,
                        ) {
                            $timestamp = $record['timestamp'];
                            return $timestamp instanceof Carbon
                            && $timestamp->lte($filterDate);
                        });
                    } catch (\Exception $e) {
                        // Invalid date, ignore filter
                    }
                }
            }
        }

        /** @var Collection<int, LogEntry> $entries */
        return $entries;
    }

    /**
     * Get timeline data for the chart (uses unfiltered data)
     * @return array<string, array<string, int>>
     */
    public function getTimelineData(): array
    {
        $filePath = $this->getLogFilePath();

        if (!File::exists($filePath)) {
            return [];
        }

        $lines = File::lines($filePath);
        $entries = collect();
        $allLines = [];

        // Collect all lines first
        foreach ($lines as $line) {
            $line = is_string($line) ? trim($line) : '';
            if (empty($line)) {
                continue;
            }
            $allLines[] = $line;
        }

        // Take only the last maxLines entries
        $linesToProcess = array_slice($allLines, -$this->maxLines);

        foreach ($linesToProcess as $line) {
            try {
                $data = json_decode($line, true, 512, JSON_THROW_ON_ERROR);
                if (is_array($data)) {
                    /** @var array<string, mixed> $data */
                    $entries->push($this->normalizeLogEntry($data));
                }
            } catch (\JsonException $e) {
                // Skip invalid JSON lines
                continue;
            }
        }

        // Group by hour and level
        $timeline = [];
        $levels = [
            'DEBUG',
            'INFO',
            'WARNING',
            'ERROR',
            'CRITICAL',
            'ALERT',
            'EMERGENCY',
        ];

        foreach ($levels as $level) {
            $timeline[$level] = [];
        }

        foreach ($entries as $entry) {
            $timestamp = $entry['timestamp'];
            if (!$timestamp instanceof Carbon) {
                continue;
            }

            $hour = $timestamp->format('Y-m-d H:00:00');
            $level = $entry['level'];

            if (!isset($timeline[$level][$hour])) {
                $timeline[$level][$hour] = 0;
            }
            $timeline[$level][$hour]++;
        }

        return $timeline;
    }

    /**
     * @param array<string, mixed> $data
     * @return LogEntry
     */
    protected function normalizeLogEntry(array $data): array
    {
        $timestamp = $data['datetime'] ?? $data['timestamp'] ?? null;
        $levelRaw = $data['level_name'] ?? $data['level'] ?? 'UNKNOWN';
        $messageRaw = $data['message'] ?? '';
        $channelRaw = $data['channel'] ?? '';

        // Try to parse timestamp
        $parsedDate = null;
        if ($timestamp !== null) {
            try {
                if (is_numeric($timestamp)) {
                    $parsedDate = Carbon::createFromTimestamp(
                        (float) $timestamp,
                    );
                } elseif (is_string($timestamp)) {
                    $parsedDate = Carbon::parse($timestamp);
                }
            } catch (\Exception $e) {
                // Keep as null
            }
        }

        $context = $data['context'] ?? null;
        $contextArray = is_array($context) ? $context : [];

        $level = is_string($levelRaw) ? $levelRaw : 'UNKNOWN';
        $message = is_string($messageRaw) ? $messageRaw : '';
        $channel = is_string($channelRaw) ? $channelRaw : '';

        return [
            'id' => md5(json_encode($data, JSON_THROW_ON_ERROR)),
            'timestamp' => $parsedDate,
            'datetime' => $parsedDate?->toDateTimeString() ?? '',
            'level' => strtoupper($level),
            'message' => $message,
            'channel' => $channel,
            'context' => $contextArray,
            'raw' => $data,
        ];
    }

    protected function getLogFilePath(): string
    {
        if ($this->logFilePath) {
            // If it's a relative path, make it relative to storage/logs
            if (!str_starts_with($this->logFilePath, '/')) {
                return storage_path('logs/' . $this->logFilePath);
            }
            return $this->logFilePath;
        }

        return storage_path('logs/laravel.log');
    }
}
