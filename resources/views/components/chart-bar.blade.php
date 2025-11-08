@props(['title' => null, 'labels' => [], 'data' => [], 'color' => 'blue'])

@php
    $colors = [
        'blue' => 'rgb(59, 130, 246)',
        'green' => 'rgb(34, 197, 94)',
        'red' => 'rgb(239, 68, 68)',
        'purple' => 'rgb(147, 51, 234)',
        'amber' => 'rgb(245, 158, 11)',
    ];

    $barColor = $colors[$color] ?? $colors['blue'];
    $chartId = 'chart-' . uniqid();

    // Normalize data
    $maxValue = max($data ?: [1]);
@endphp

<div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 p-6">
    @if($title)
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">{{ $title }}</h3>
    @endif

    <div x-data="barChart({{ json_encode($data) }}, {{ json_encode($labels) }}, '{{ $barColor }}')" class="relative">
        <svg x-ref="svg" class="w-full h-64" style="background: none;"></svg>

        <!-- Tooltip -->
        <div x-show="hoveredIndex !== null"
             x-cloak
             class="absolute top-0 left-0 pointer-events-none z-10"
             :style="{
                 'left': (hoveredIndex !== null ? (hoveredIndex / (labels.length - 1)) * 100 : 0) + '%',
                 'transform': 'translateX(-50%)'
             }">
            <div class="bg-gray-900 dark:bg-black text-white text-xs rounded-lg px-3 py-2 whitespace-nowrap -translate-y-12 shadow-lg border border-gray-700">
                <p class="font-semibold" x-text="hoveredIndex !== null ? labels[hoveredIndex] : ''"></p>
                <p x-text="hoveredIndex !== null ? data[hoveredIndex].toLocaleString() : ''"></p>
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="mt-6 flex items-center justify-center gap-4">
        <div class="flex items-center gap-2">
            <div class="w-3 h-3 rounded" style="background: {{ $barColor }};"></div>
            <span class="text-sm text-gray-600 dark:text-gray-400">{{ $title ?? 'Data' }}</span>
        </div>
    </div>
</div>

<script>
function barChart(data, labels, color) {
    return {
        data: data,
        labels: labels,
        color: color,
        hoveredIndex: null,
        maxValue: Math.max(...data),

        init() {
            this.$nextTick(() => this.drawChart());
            this.$watch('hoveredIndex', () => this.drawChart());
        },

        drawChart() {
            const svg = this.$refs.svg;
            const width = svg.clientWidth;
            const height = svg.clientHeight;
            const padding = 40;
            const graphWidth = width - padding * 2;
            const graphHeight = height - padding * 2;
            const barWidth = graphWidth / (this.data.length * 1.5);

            // Clear previous content
            svg.innerHTML = '';

            // Draw background
            const bg = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
            bg.setAttribute('width', width);
            bg.setAttribute('height', height);
            bg.setAttribute('fill', 'transparent');
            svg.appendChild(bg);

            // Draw grid lines
            for (let i = 0; i <= 5; i++) {
                const y = padding + (i * graphHeight / 5);
                const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
                line.setAttribute('x1', padding);
                line.setAttribute('y1', y);
                line.setAttribute('x2', width - padding);
                line.setAttribute('y2', y);
                line.setAttribute('stroke', window.matchMedia('(prefers-color-scheme: dark)').matches ? '#334155' : '#e5e7eb');
                line.setAttribute('stroke-width', '1');
                svg.appendChild(line);
            }

            // Draw bars
            this.data.forEach((val, idx) => {
                const barHeight = (val / this.maxValue) * graphHeight;
                const x = padding + (idx * (graphWidth / this.data.length)) + (graphWidth / this.data.length - barWidth) / 2;
                const y = height - padding - barHeight;

                const rect = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
                rect.setAttribute('x', x);
                rect.setAttribute('y', y);
                rect.setAttribute('width', barWidth);
                rect.setAttribute('height', barHeight);
                rect.setAttribute('fill', this.color);
                rect.setAttribute('rx', '4');
                rect.setAttribute('opacity', this.hoveredIndex === idx ? '1' : '0.8');
                rect.setAttribute('class', 'transition-all duration-200 cursor-pointer');
                rect.addEventListener('mouseenter', () => this.hoveredIndex = idx);
                rect.addEventListener('mouseleave', () => this.hoveredIndex = null);
                svg.appendChild(rect);
            });

            // Draw hover areas
            this.data.forEach((val, idx) => {
                const barHeight = (val / this.maxValue) * graphHeight;
                const x = padding + (idx * (graphWidth / this.data.length)) + (graphWidth / this.data.length - barWidth) / 2;
                const y = height - padding - barHeight;

                const hoverArea = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
                hoverArea.setAttribute('x', x - 10);
                hoverArea.setAttribute('y', 0);
                hoverArea.setAttribute('width', barWidth + 20);
                hoverArea.setAttribute('height', height);
                hoverArea.setAttribute('fill', 'transparent');
                hoverArea.setAttribute('class', 'cursor-pointer');
                hoverArea.addEventListener('mouseenter', () => this.hoveredIndex = idx);
                hoverArea.addEventListener('mouseleave', () => this.hoveredIndex = null);
                svg.appendChild(hoverArea);
            });
        }
    }
}
</script>
