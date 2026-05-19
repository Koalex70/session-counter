<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Статистика посещений') }}
            </h2>
            <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-2">
                <label for="days" class="text-sm text-gray-600">{{ __('Период') }}</label>
                <select
                    id="days"
                    name="days"
                    class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                    onchange="this.form.submit()"
                >
                    <option value="7" @selected($days === 7)>7 {{ __('дней') }}</option>
                    <option value="30" @selected($days === 30)>30 {{ __('дней') }}</option>
                </select>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500">{{ __('Всего визитов') }}</p>
                    <p class="text-3xl font-semibold text-gray-900 mt-1">{{ number_format($summary['total_visits']) }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500">{{ __('Уникальных посетителей') }}</p>
                    <p class="text-3xl font-semibold text-gray-900 mt-1">{{ number_format($summary['unique_visitors']) }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500">{{ __('Топ город') }}</p>
                    <p class="text-3xl font-semibold text-gray-900 mt-1">{{ $summary['top_city'] ?? '—' }}</p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Уникальные посещения по часам') }}</h3>
                <div class="h-96">
                    <canvas id="chartHourly"></canvas>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Посещения по городам') }}</h3>
                <div class="max-w-lg mx-auto h-80">
                    <canvas id="chartCities"></canvas>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
        <script>
            const days = {{ $days }};
            const chartOptions = {
                responsive: true,
                maintainAspectRatio: false,
            };

            fetch(`{{ route('dashboard.data.hourly') }}?days=${days}`)
                .then((response) => response.json())
                .then((data) => {
                    new Chart(document.getElementById('chartHourly'), {
                        type: 'bar',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                label: '{{ __('Уникальные посещения') }}',
                                data: data.values,
                                backgroundColor: 'rgba(79, 70, 229, 0.7)',
                            }],
                        },
                        options: {
                            ...chartOptions,
                            indexAxis: 'y',
                            scales: {
                                x: {
                                    title: { display: true, text: '{{ __('Количество уникальных') }}' },
                                    beginAtZero: true,
                                    ticks: { precision: 0 },
                                },
                                y: {
                                    title: { display: true, text: '{{ __('Время') }}' },
                                },
                            },
                        },
                    });
                });

            fetch(`{{ route('dashboard.data.cities') }}?days=${days}`)
                .then((response) => response.json())
                .then((data) => {
                    new Chart(document.getElementById('chartCities'), {
                        type: 'doughnut',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                data: data.values,
                                backgroundColor: [
                                    '#4f46e5', '#06b6d4', '#10b981', '#f59e0b',
                                    '#ef4444', '#8b5cf6', '#ec4899', '#64748b',
                                ],
                            }],
                        },
                        options: chartOptions,
                    });
                });
        </script>
    @endpush
</x-app-layout>
