<?php

namespace App\Filament\Widgets;

use App\Models\UserInteraction;
use Filament\Widgets\ChartWidget;

class UserInteractionsChart extends ChartWidget
{
    protected static ?string $heading = 'User Interactions (Last 7 Days)';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        try {
            $likes = $this->getInteractionData('like');
            $bookmarks = $this->getInteractionData('bookmark');
            $views = $this->getInteractionData('view');
            $shares = $this->getInteractionData('share');

            return [
                'datasets' => [
                    [
                        'label' => 'Likes',
                        'data' => $likes,
                        'backgroundColor' => 'rgba(239, 68, 68, 0.2)',
                        'borderColor' => 'rgb(239, 68, 68)',
                    ],
                    [
                        'label' => 'Bookmarks',
                        'data' => $bookmarks,
                        'backgroundColor' => 'rgba(251, 191, 36, 0.2)',
                        'borderColor' => 'rgb(251, 191, 36)',
                    ],
                    [
                        'label' => 'Views',
                        'data' => $views,
                        'backgroundColor' => 'rgba(34, 197, 94, 0.2)',
                        'borderColor' => 'rgb(34, 197, 94)',
                    ],
                    [
                        'label' => 'Shares',
                        'data' => $shares,
                        'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                        'borderColor' => 'rgb(59, 130, 246)',
                    ],
                ],
                'labels' => $this->getLabels(),
            ];
        } catch (\Exception $e) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getInteractionData(string $type): array
    {
        try {
            $data = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $count = UserInteraction::where('type', $type)
                    ->whereDate('created_at', $date)
                    ->count();
                $data[] = $count;
            }
            return $data;
        } catch (\Exception $e) {
            return [0, 0, 0, 0, 0, 0, 0];
        }
    }

    protected function getLabels(): array
    {
        $labels = [];
        for ($i = 6; $i >= 0; $i--) {
            $labels[] = now()->subDays($i)->format('M d');
        }
        return $labels;
    }
}
