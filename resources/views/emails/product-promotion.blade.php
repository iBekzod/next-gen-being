@component('mail::message')
# 🎁 New AI Resources Available

Hi {{ $user->name }},

We've just released some amazing AI prompt templates and tutorials that might help you!

@foreach($products as $product)

## {{ $product->title }}

{{ $product->short_description ?? Str::limit($product->description, 100) }}

**Price:** {{ $product->formatted_price }}

@component('mail::button', ['url' => route('digital-products.show', $product->slug), 'color' => 'success'])
View Details
@endcomponent

---

@endforeach

These resources are designed to help you automate your workflows and master AI tools faster.

@if($segment === 'free')
**Plus:** Upgrade to Pro for exclusive access to premium prompts and tutorials!

@component('mail::button', ['url' => route('subscription.plans')])
View Plans
@endcomponent
@endif

---

Thanks for being part of NextGenBeing!

Best regards,<br>
The NextGenBeing Team

@component('mail::subcopy')
Want to stop receiving these emails? Update your [notification preferences]({{ route('dashboard.settings') }})
@endcomponent
@endcomponent
