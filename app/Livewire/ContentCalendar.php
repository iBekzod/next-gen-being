<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Services\ContentCalendarService;
use App\Models\ScheduledPost;

class ContentCalendar extends Component
{
    use WithPagination;

    public ContentCalendarService $calendarService;
    public array $scheduledPosts = [];
    public array $drafts = [];
    public string $view = 'month'; // month, week, list
    public ?int $month = null;
    public ?int $year = null;
    public bool $isLoading = true;

    public function mount()
    {
        $this->calendarService = app(ContentCalendarService::class);
        $this->month = now()->month;
        $this->year = now()->year;
        $this->loadCalendar();
    }

    public function loadCalendar()
    {
        $this->isLoading = true;

        try {
            $user = auth()->user();

            // Get scheduled posts for the month
            $this->scheduledPosts = $this->calendarService->getMonthlySchedule($user, $this->month, $this->year);

            // Get drafts
            $this->drafts = $this->calendarService->getUserDrafts($user);
        } finally {
            $this->isLoading = false;
        }
    }

    public function schedulePost($data)
    {
        $result = $this->calendarService->schedulePost(auth()->user(), $data);

        if ($result['success']) {
            $this->dispatch('postScheduled');
            $this->loadCalendar();
        } else {
            $this->dispatch('error', $result['message'] ?? 'Failed to schedule');
        }
    }

    public function nextMonth()
    {
        if ($this->month == 12) {
            $this->month = 1;
            $this->year++;
        } else {
            $this->month++;
        }
        $this->loadCalendar();
    }

    public function previousMonth()
    {
        if ($this->month == 1) {
            $this->month = 12;
            $this->year--;
        } else {
            $this->month--;
        }
        $this->loadCalendar();
    }

    public function render()
    {
        return view('livewire.content-calendar');
    }
}
