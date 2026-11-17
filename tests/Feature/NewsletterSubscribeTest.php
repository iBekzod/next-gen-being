<?php

namespace Tests\Feature;

use App\Livewire\NewsletterSubscribe;
use App\Models\NewsletterSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\TestCase;

class NewsletterSubscribeTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_subscription_is_created(): void
    {
        Livewire::test(NewsletterSubscribe::class)
            ->set('email', 'reader@example.com')
            ->set('frequency', 'daily')
            ->call('subscribe')
            ->assertHasNoErrors()
            ->assertSet('subscribed', true);

        $this->assertSame(1, NewsletterSubscription::where('email', 'reader@example.com')->count());
    }

    public function test_array_payloads_are_coerced_instead_of_throwing(): void
    {
        Livewire::test(NewsletterSubscribe::class)
            ->set('email', [['not-an-email']])
            ->set('frequency', ['nested' => ['daily']])
            ->assertSet('email', 'not-an-email')
            ->assertSet('frequency', 'daily')
            ->call('subscribe')
            ->assertHasErrors(['email'])
            ->assertSet('subscribed', false);

        $this->assertSame(0, NewsletterSubscription::count());
    }

    public function test_unknown_frequency_is_rejected_by_validation(): void
    {
        Livewire::test(NewsletterSubscribe::class)
            ->set('email', 'reader@example.com')
            ->set('frequency', [])
            ->call('subscribe')
            ->assertHasErrors(['frequency']);
    }

    public function test_compact_mount_param_accepts_arrays(): void
    {
        Livewire::test(NewsletterSubscribe::class, ['compact' => [['1']]])
            ->assertSet('compact', true);
    }

    /** @dataProvider serverOnlyProps */
    public function test_server_only_props_cannot_be_set_by_client(string $prop): void
    {
        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::test(NewsletterSubscribe::class)->set($prop, ['x']);
    }

    public static function serverOnlyProps(): array
    {
        return [['subscribed'], ['error'], ['compact']];
    }
}
