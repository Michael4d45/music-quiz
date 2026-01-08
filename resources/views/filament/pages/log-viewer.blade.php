<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Timeline Chart --}}
        <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10" wire:ignore>
            <h3 class="mb-4 text-lg font-semibold">Log Timeline</h3>
            <div class="h-64">
                <canvas id="logTimelineChart"></canvas>
            </div>
        </div>

        {{-- Table --}}
        {{ $this->table }}
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script>
            let timelineChart = null;
            let lastTimelineDataHash = null;

            function getDataHash(data) {
                return JSON.stringify(data);
            }

            function initTimelineChart() {
                const timelineData = @json($this->getTimelineData());
                const dataHash = getDataHash(timelineData);
                
                // Only update if data actually changed
                if (dataHash === lastTimelineDataHash && timelineChart) {
                    return;
                }
                
                lastTimelineDataHash = dataHash;
                
                // Destroy existing chart if it exists
                const ctx = document.getElementById('logTimelineChart');
                if (timelineChart) {
                    timelineChart.destroy();
                    timelineChart = null;
                }

                if (!ctx) {
                    return;
                }
                
                // Prepare data for Chart.js
                const datasets = [];
                const colors = {
                    'DEBUG': 'rgb(156, 163, 175)',
                    'INFO': 'rgb(34, 197, 94)',
                    'WARNING': 'rgb(251, 191, 36)',
                    'ERROR': 'rgb(239, 68, 68)',
                    'CRITICAL': 'rgb(220, 38, 38)',
                    'ALERT': 'rgb(185, 28, 28)',
                    'EMERGENCY': 'rgb(127, 29, 29)'
                };

                // Collect all unique hours
                const allHours = new Set();
                Object.values(timelineData).forEach(levelData => {
                    Object.keys(levelData).forEach(hour => allHours.add(hour));
                });
                const sortedHours = Array.from(allHours).sort();

                // Create datasets for each level
                Object.entries(timelineData).forEach(([level, levelData]) => {
                    if (Object.keys(levelData).length > 0) {
                        datasets.push({
                            label: level,
                            data: sortedHours.map(hour => levelData[hour] || 0),
                            borderColor: colors[level] || 'rgb(156, 163, 175)',
                            backgroundColor: (colors[level] || 'rgb(156, 163, 175)').replace('rgb', 'rgba').replace(')', ', 0.1)'),
                            tension: 0.4,
                            fill: true
                        });
                    }
                });

                // Format labels to show only time
                const formattedLabels = sortedHours.map(hour => {
                    const date = new Date(hour);
                    return date.toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
                });

                timelineChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: formattedLabels,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        },
                        interaction: {
                            mode: 'nearest',
                            axis: 'x',
                            intersect: false
                        }
                    }
                });
            }

            // Initialize on page load
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initTimelineChart);
            } else {
                initTimelineChart();
            }
        </script>
    @endpush
</x-filament-panels::page>
