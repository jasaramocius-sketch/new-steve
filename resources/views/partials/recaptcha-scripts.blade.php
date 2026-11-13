@php
    $form = $form ?? 'form';
@endphp
@if(\App\Services\Recaptcha::enabled($form))
    @if(\App\Services\Recaptcha::isV3())
        <script src="https://www.google.com/recaptcha/api.js?render={{ urlencode(\App\Services\Recaptcha::siteKey()) }}" async defer></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const input = document.querySelector('input[name="g-recaptcha-response"][data-recaptcha-v3]');
                const form = input ? input.closest('form') : null;
                if (!input || !form) return;

                const siteKey = @json(\App\Services\Recaptcha::siteKey());
                const action = @json($form);

                form.addEventListener('submit', function (event) {
                    if (form.dataset.recaptchaSubmitting === 'true') {
                        return;
                    }

                    event.preventDefault();
                    let attempts = 0;

                    const execute = function () {
                        if (typeof grecaptcha === 'undefined') {
                            if (++attempts > 100) {
                                window.alert('reCAPTCHA could not be loaded. Please check your connection and try again.');
                                return;
                            }

                            window.setTimeout(execute, 100);
                            return;
                        }

                        grecaptcha.ready(function () {
                            grecaptcha.execute(siteKey, { action: action }).then(function (token) {
                                input.value = token;
                                form.dataset.recaptchaSubmitting = 'true';

                                if (typeof form.requestSubmit === 'function') {
                                    form.requestSubmit();
                                } else {
                                    form.submit();
                                }
                            }).catch(function () {
                                window.alert('reCAPTCHA verification failed. Please try again.');
                            });
                        });
                    };

                    execute();
                });
            });
        </script>
    @else
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endif
@endif
