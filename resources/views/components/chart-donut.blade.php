@props(['title' => null, 'labels' => [], 'data' => []])

@php
    $colors = [
        'rgb(59, 130, 246)',      // blue
        'rgb(34, 197, 94)',        // green
        'rgb(239, 68, 68)',        // red
        'rgb(147, 51, 234)',       // purple
        'rgb(245, 158, 11)',       // amber
        'rgb(6, 182, 212)',        // cyan
        'rgb(236, 72, 153)',       // pink
        'rgb(168, 85, 247)',       // violet
    ];

    $total = array_sum($data);
    $chartId = 'chart-' . uniqid();
@endphp

<div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 p-6">
    @if($title)
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">{{ $title }}</h3>
    @endif

    <div x-data="donutChart({{ json_encode($data) }}, {{ json_encode($labels) }}, {{ json_encode(array_slice($colors, 0, count($labels))) }})" class="flex flex-col items-center">
        <svg x-ref="svg" class="w-48 h-48" style="background: none;"></svg>

        <!-- Legend -->
        <div class="mt-6 w-full grid grid-cols-2 gap-3">
            <template x-for="(label, idx) in labels" :key="idx">
                <div class="flex items-center gap-2 text-sm">
                    <div class="w-3 h-3 rounded-full" :style="{ background: colors[idx] }"></div>
                    <div class="flex-1">
                        <p class="text-gray-700 dark:text-gray-300 font-medium" x-text="label"></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400" x-text="data[idx].toLocaleString() + ' (' + ((data[idx] / total) * 100).toFixed(1) + '%)'"></p>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
function donutChart(data, labels, colors) {
    return {
        data: data,
        labels: labels,
        colors: colors,
        total: data.reduce((a, b) => a + b, 0),
        hoveredIndex: null,

        init() {
            this.$nextTick(() => this.drawChart());
            this.$watch('hoveredIndex', () => this.drawChart());
        },

        drawChart() {
            const svg = this.$refs.svg;
            const width = svg.clientWidth;
            const height = svg.clientHeight;
            const centerX = width / 2;
            const centerY = height / 2;
            const radius = Math.min(width, height) / 2 - 20;
            const innerRadius = radius * 0.65;

            // Clear previous content
            svg.innerHTML = '';

            let currentAngle = -Math.PI / 2;

            this.data.forEach((val, idx) => {
                const sliceAngle = (val / this.total) * 2 * Math.PI;
                const startAngle = currentAngle;
                const endAngle = currentAngle + sliceAngle;

                // Create slice path
                const x1 = centerX + radius * Math.cos(startAngle);
                const y1 = centerY + radius * Math.sin(startAngle);
                const x2 = centerX + radius * Math.cos(endAngle);
                const y2 = centerY + radius * Math.sin(endAngle);

                const x3 = centerX + innerRadius * Math.cos(endAngle);
                const y3 = centerY + innerRadius * Math.sin(endAngle);
                const x4 = centerX + innerRadius * Math.cos(startAngle);
                const y4 = centerY + innerRadius * Math.sin(startAngle);

                const largeArc = sliceAngle > Math.PI ? 1 : 0;

                const pathData = `
                    M ${x1} ${y1}
                    A ${radius} ${radius} 0 ${largeArc} 1 ${x2} ${y2}
                    L ${x3} ${y3}
                    A ${innerRadius} ${innerRadius} 0 ${largeArc} 0 ${x4} ${y4}
                    Z
                `;

                const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                path.setAttribute('d', pathData);
                path.setAttribute('fill', this.colors[idx]);
                path.setAttribute('opacity', this.hoveredIndex === idx ? '1' : '0.8');
                path.setAttribute('class', 'transition-opacity duration-200 cursor-pointer');
                path.addEventListener('mouseenter', () => this.hoveredIndex = idx);
                path.addEventListener('mouseleave', () => this.hoveredIndex = null);

                svg.appendChild(path);

                currentAngle = endAngle;
            });

            // Draw center circle for donut effect
            const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
            circle.setAttribute('cx', centerX);
            circle.setAttribute('cy', centerY);
            circle.setAttribute('r', innerRadius);
            circle.setAttribute('fill', window.matchMedia('(prefers-color-scheme: dark)').matches ? '#1e293b' : '#ffffff');
            svg.appendChild(circle);

            // Draw center text
            if (this.hoveredIndex !== null) {
                const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                text.setAttribute('x', centerX);
                text.setAttribute('y', centerY);
                text.setAttribute('text-anchor', 'middle');
                text.setAttribute('dominant-baseline', 'middle');
                text.setAttribute('class', 'text-sm font-bold');
                text.setAttribute('fill', window.matchMedia('(prefers-color-scheme: dark)').matches ? '#fff' : '#000');
                text.textContent = ((this.data[this.hoveredIndex] / this.total) * 100).toFixed(1) + '%';
                svg.appendChild(text);
            }
        }
    }
}
</script>
