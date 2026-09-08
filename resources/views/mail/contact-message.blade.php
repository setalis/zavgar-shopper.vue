<x-mail::message>
# {{ __('backend.contact.heading') }}

**{{ __('backend.contact.fields.name') }}:** {{ $firstName }} {{ $lastName }}

**{{ __('backend.contact.fields.email') }}:** {{ $email }}

@if ($phone)
**{{ __('backend.contact.fields.phone') }}:** {{ $phone }}
@endif

**{{ __('backend.contact.fields.topic') }}:** {{ $topic->getLabel() }}

@if ($orderNumber)
**{{ __('backend.contact.fields.order_number') }}:** {{ $orderNumber }}
@endif

**{{ __('backend.contact.fields.body') }}:**

{{ $body }}
</x-mail::message>
