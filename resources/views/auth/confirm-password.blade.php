<x-guest-layout>
    <div class="mb-4 px-4 sm:px-0 text-sm text-gray-600 dark:text-gray-400">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="px-4 sm:px-0">
        @csrf

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" 
                          class="block mt-1 w-full text-base sm:text-sm"
                          type="password"
                          name="password"
                          required 
                          autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex justify-end mt-4 sm:mt-6">
            <x-primary-button class="w-full sm:w-auto justify-center">
                {{ __('Confirm') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>