<div class="space-y-6">
    {{-- Basic Information --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Timestamp</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $record['datetime'] ?? 'N/A' }}</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Level</dt>
            <dd class="mt-1">
                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset
                    @if(in_array($record['level'] ?? '', ['ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'])) bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-400/10 dark:text-red-400 dark:ring-red-400/20
                    @elseif(in_array($record['level'] ?? '', ['WARNING', 'NOTICE'])) bg-yellow-50 text-yellow-700 ring-yellow-600/20 dark:bg-yellow-400/10 dark:text-yellow-400 dark:ring-yellow-400/20
                    @elseif(($record['level'] ?? '') === 'INFO') bg-green-50 text-green-700 ring-green-600/20 dark:bg-green-400/10 dark:text-green-400 dark:ring-green-400/20
                    @else bg-gray-50 text-gray-700 ring-gray-600/20 dark:bg-gray-400/10 dark:text-gray-400 dark:ring-gray-400/20
                    @endif">
                    {{ $record['level'] ?? 'UNKNOWN' }}
                </span>
            </dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Channel</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $record['channel'] ?? 'N/A' }}</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">ID</dt>
            <dd class="mt-1 font-mono text-xs text-gray-900 dark:text-white">{{ $record['id'] ?? 'N/A' }}</dd>
        </div>
    </div>

    {{-- Message --}}
    <div>
        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Message</dt>
        <dd class="mt-1 rounded-md bg-gray-50 p-3 text-sm text-gray-900 dark:bg-gray-800 dark:text-white">
            {{ $record['message'] ?? 'N/A' }}
        </dd>
    </div>

    {{-- Context --}}
    @if(!empty($record['context']))
        <div>
            <dt class="mb-2 text-sm font-medium text-gray-500 dark:text-gray-400">Context</dt>
            <dd class="rounded-md bg-gray-50 p-3 dark:bg-gray-800">
                <pre class="overflow-x-auto text-xs text-gray-900 dark:text-white">{{ json_encode($record['context'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
            </dd>
        </div>
    @endif

    {{-- Raw Data --}}
    <div>
        <dt class="mb-2 text-sm font-medium text-gray-500 dark:text-gray-400">Raw JSON</dt>
        <dd class="rounded-md bg-gray-50 p-3 dark:bg-gray-800">
            <pre class="overflow-x-auto text-xs text-gray-900 dark:text-white">{{ json_encode($record['raw'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
        </dd>
    </div>
</div>
