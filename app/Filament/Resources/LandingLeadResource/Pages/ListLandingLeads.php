<?php

namespace App\Filament\Resources\LandingLeadResource\Pages;

use App\Filament\Resources\LandingLeadResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListLandingLeads extends ListRecords
{
    protected static string $resource = LandingLeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('export_all')
                ->label('Export All')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(function () {
                    $emails = static::getResource()::getModel()::pluck('email')->implode("\n");
                    return response()->streamDownload(function () use ($emails) {
                        echo $emails;
                    }, 'landing-leads-' . now()->format('Y-m-d') . '.txt');
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Leads'),
            'today' => Tab::make('Today')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('created_at', today())),
            'this_week' => Tab::make('This Week')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])),
            'this_month' => Tab::make('This Month')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereMonth('created_at', now()->month)),
        ];
    }
}
