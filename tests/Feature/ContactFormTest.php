<?php

namespace Tests\Feature;

use App\Livewire\ContactForm;
use App\Models\HelpReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_page_renders(): void
    {
        $this->withoutVite()->get('/contact')->assertOk();
    }

    public function test_valid_submission_creates_help_report(): void
    {
        Livewire::test(ContactForm::class)
            ->set('name', 'Jane Doe')
            ->set('email', 'jane@example.com')
            ->set('subject_type', 'support')
            ->set('subject', 'Need some help')
            ->set('message', 'This is a message that is long enough.')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('submitted', true);

        $this->assertSame(1, HelpReport::count());
    }

    public function test_array_payloads_are_coerced_instead_of_throwing(): void
    {
        Livewire::test(ContactForm::class)
            ->set('name', ['Jane', 'x'])
            ->set('email', [['jane@example.com']])
            ->set('subject_type', [])
            ->set('subject', ['nested' => ['Need some help']])
            ->set('message', ['short'])
            ->assertSet('name', 'Jane')
            ->assertSet('email', 'jane@example.com')
            ->assertSet('subject_type', '')
            ->assertSet('subject', 'Need some help')
            ->assertSet('message', 'short')
            ->call('submit')
            ->assertHasErrors(['subject_type', 'message']);

        $this->assertSame(0, HelpReport::count());
    }

    public function test_submitted_flag_cannot_be_set_by_client(): void
    {
        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::test(ContactForm::class)->set('submitted', ['x']);
    }
}
