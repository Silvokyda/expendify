<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    {{-- Pie: Budget/Balance --}}
    <div class="rounded-2xl bg-white dark:bg-gray-900 p-6 shadow-sm ring-1 ring-black/5">
        <h3 class="font-semibold mb-3">Total balance</h3>
        <canvas id="budgetPie" height="160"></canvas>
    </div>

    {{-- Line: Balances (cumulative) --}}
    <div class="rounded-2xl bg-white dark:bg-gray-900 p-6 shadow-sm ring-1 ring-black/5">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold">Balances</h3>
            <span class="text-xs text-gray-500">Cumulative net per day</span>
        </div>
        <canvas id="balancesLine" height="160"></canvas>
    </div>

    {{-- Line: Period comparison (daily net) --}}
    <div class="rounded-2xl bg-white dark:bg-gray-900 p-6 shadow-sm ring-1 ring-black/5 lg:col-span-2">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold">Period comparison</h3>
            <span class="text-xs text-gray-500 capitalize">{{ $period }} vs previous {{ $period }}</span>
        </div>
        <canvas id="periodCompareLine" height="180"></canvas>
    </div>

    {{-- Bar: This month income vs expense --}}
    <div class="rounded-2xl bg-white dark:bg-gray-900 p-6 shadow-sm ring-1 ring-black/5 lg:col-span-2">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold">This period — income & expenses</h3>
            <span class="text-xs text-gray-500">
                {{ $start->format('M j') }} – {{ $end->format('M j, Y') }}
            </span>
        </div>
        <canvas id="thisMonthBar" height="140"></canvas>
    </div>
</div>

{{-- Chart.js --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const CHART = @json($chartData);

// Pie
new Chart(document.getElementById('budgetPie'), {
  type: 'doughnut',
  data: {
    labels: CHART.pie.labels,
    datasets: [{ data: CHART.pie.data }]
  },
  options: {
    responsive: true,
    plugins: { legend: { position: 'bottom' } }
  }
});

// Cumulative Balances Line
new Chart(document.getElementById('balancesLine'), {
  type: 'line',
  data: {
    labels: CHART.balances.labels,
    datasets: [
      { label: 'Current',  data: CHART.balances.current,  fill: false, tension: 0.25 },
      { label: 'Previous', data: CHART.balances.previous, fill: false, tension: 0.25 }
    ]
  },
  options: {
    responsive: true,
    scales: { x: { ticks: { maxRotation: 0, autoSkip: true } }, y: { beginAtZero: true } },
    plugins: { legend: { position: 'bottom' } }
  }
});

// Period Comparison Line (daily net)
new Chart(document.getElementById('periodCompareLine'), {
  type: 'line',
  data: {
    labels: CHART.periodCompare.labels,
    datasets: [
      { label: 'Current',  data: CHART.periodCompare.current,  fill: false, tension: 0.25 },
      { label: 'Previous', data: CHART.periodCompare.previous, fill: false, tension: 0.25 }
    ]
  },
  options: {
    responsive: true,
    scales: { x: { ticks: { maxRotation: 0, autoSkip: true } } },
    plugins: { legend: { position: 'bottom' } }
  }
});

// This month: income vs expense
new Chart(document.getElementById('thisMonthBar'), {
  type: 'bar',
  data: {
    labels: ['Income', 'Expenses'],
    datasets: [{ data: [CHART.thisMonth.income, CHART.thisMonth.expense] }]
  },
  options: {
    responsive: true,
    scales: { y: { beginAtZero: true } },
    plugins: { legend: { display: false } }
  }
});
</script>
@endpush
