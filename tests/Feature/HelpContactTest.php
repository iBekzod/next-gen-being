<?php

namespace Tests\Feature;

use App\Livewire\HelpContact;
use App\Models\HelpReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\TestCase;

class HelpContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_submission_creates_help_report(): void
    {
        Livewire::test(HelpContact::class)
            ->call('openModal')
            ->set('type', 'bug')
            ->set('priority', 'high')
            ->set('subject', 'Broken button')
            ->set('description', 'The save button does nothing when clicked.')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('showModal', false);

        $this->assertSame(1, HelpReport::count());
    }

    public function test_array_payloads_are_coerced_instead_of_throwing(): void
    {
        Livewire::test(HelpContact::class)
            ->call('openModal')
            ->set('type', [['bug']])
            ->set('priority', [])
            ->set('subject', ['nested' => ['Broken button']])
            ->set('description', [['short']])
            ->assertSet('type', 'bug')
            ->assertSet('priority', '')
            ->assertSet('subject', 'Broken button')
            ->assertSet('description', 'short')
            ->call('submit')
            ->assertHasErrors(['priority', 'description']);

        $this->assertSame(0, HelpReport::count());
    }

    public function test_show_modal_cannot_be_set_by_client(): void
    {
        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::test(HelpContact::class)->set('showModal', ['x']);
    }
}
