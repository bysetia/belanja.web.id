<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('reset-password') }}">
            @csrf

            <input type="hidden" name="email" value="{{ $email }}">
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="block">
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            </div>

            <div class="mt-4">
                <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
                <x-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <x-button onclick="printToken()">
                    {{ __('Reset Password') }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>

    <script>
        function printToken() {
            const token = "{{ $token }}";
            console.log(token);
            // Lakukan hal lain yang ingin Anda lakukan dengan token di sini
        }
    </script>
</x-guest-layout>