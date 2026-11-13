@php
    $form = $form ?? 'form';
    $wrapperClass = $wrapperClass ?? 'mt-3';
@endphp
@if(\App\Services\Recaptcha::enabled($form))
    <div class="{{ $wrapperClass }}">
        @if(\App\Services\Recaptcha::isV3())
            <input type="hidden" name="g-recaptcha-response" data-recaptcha-v3 value="">
        @else
            <div class="g-recaptcha" data-sitekey="{{ \App\Services\Recaptcha::siteKey() }}"></div>
        @endif
    </div>
@endif
