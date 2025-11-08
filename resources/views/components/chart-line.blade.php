@props(['title' => null, 'labels' => [], 'data' => [], 'color' => 'blue'])

@php
    $colors = [
        'blue' => ['bg' => 'rgb(59, 130, 246)', 'light' => 'rgba(59, 130, 246, 0.1)'],
        'green' => ['bg' => 'rgb(34, 197, 94)', 'light' => 'rgba(34, 197, 94, 0.1)'],
        'red' => ['bg' => 'rgb(239, 68, 68)', 'light' => 'rgba(239, 68, 68, 0.1)'],
        'purple' => ['bg' => 'rgb(147, 51, 234)', 'light' => 'rgba(147, 51, 234, 0.1)'],
        'amber' => ['bg' => 'rgb(245, 158, 11)', 'light' => 'rgba(245, 158, 11, 0.1)'],
    ];

    $colorScheme = $colors[$color] ?? $colors['blue'];
    $chartId = 'chart-' . uniqid();

    // Normalize data
    $maxValue = max($data ?: [1]);
    $dataPoints = [];
    foreach ($data as $key => $value) {
        $dataPoints[] = round(($value / $maxValue) * 100, 2);
    }
@endphp

<div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 p-6">
    @if($title)
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">{{ $title }}</h3>
    @endif

    <div x-data="lineChart({{ json_encode($dataPoints) }}, {{ json_encode($labels) }}, '{{ $colorScheme['bg'] }}', '{{ $colorScheme['light'] }}')" class="relative">
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
                <p class="font-semibold">{{ ucfirst(str_replace('_', ' ', $labels[0] ?? '')) }}</p>
                <p x-text="hoveredIndex !== null ? labels[hoveredIndex] : ''"></p>
                <p x-text="hoveredIndex !== null ? (data[hoveredIndex] * maxValue / 100).toFixed(0) : ''"></p>
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="mt-6 flex items-center justify-center gap-4">
        <div class="flex items-center gap-2">
            <div class="w-3 h-3 rounded-full" style="background: {{ $colorScheme['bg'] }};"></div>
            <span class="text-sm text-gray-600 dark:text-gray-400">{{ $title ?? 'Data' }}</span>
        </div>
    </div>
</div>

<script>
function lineChart(data, labels, color, lightColor) {
    return {
        data: data,
        labels: labels,
        color: color,
        lightColor: lightColor,
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

            // Calculate points
            const points = this.data.map((val, idx) => ({
                x: padding + (idx / (this.data.length - 1)) * graphWidth,
                y: height - padding - (val / 100) * graphHeight
            }));

            // Draw area under curve
            if (points.length > 1) {
                let pathData = `M ${points[0].x} ${height - padding}`;
                points.forEach(p => pathData += ` L ${p.x} ${p.y}`);
                pathData += ` L ${points[points.length - 1].x} ${height - padding} Z`;

                const area = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                area.setAttribute('d', pathData);
                area.setAttribute('fill', this.lightColor);
                svg.appendChild(area);
            }

            // Draw line
            if (points.length > 1) {
                let pathData = `M ${points[0].x} ${points[0].y}`;
                points.slice(1).forEach(p => pathData += ` L ${p.x} ${p.y}`);

                const line = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                line.setAttribute('d', pathData);
                line.setAttribute('fill', 'none');
                line.setAttribute('stroke', this.color);
                line.setAttribute('stroke-width', '2');
                line.setAttribute('stroke-linecap', 'round');
                line.setAttribute('stroke-linejoin', 'round');
                svg.appendChild(line);
            }

            // Draw points
            points.forEach((p, idx) => {
                const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                circle.setAttribute('cx', p.x);
                circle.setAttribute('cy', p.y);
                circle.setAttribute('r', this.hoveredIndex === idx ? '5' : '3');
                circle.setAttribute('fill', this.color);
                circle.setAttribute('class', 'transition-all duration-200');
                svg.appendChild(circle);
            });

            // Draw hover area
            points.forEach((p, idx) => {
                const hoverArea = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                hoverArea.setAttribute('cx', p.x);
                hoverArea.setAttribute('cy', p.y);
                hoverArea.setAttribute('r', '20');
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
