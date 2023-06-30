<x-guest-layout>
    <x-header title="ResetPssword">
        {{ $authorize_url }}
    </x-header>
    <div class="p-auth">
        <h1 class="p-auth__title">パスワード再設定</h1>
        <div class="p-auth__form">
            <form method="POST" action="{{ route('password.store') }}">
                @csrf

                <!-- Password Reset Token -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <!-- Email Address -->
                <div class="p-auth__form__input">
                    <x-input-label for="email" class="p-auth__form__name" :value="__('メールアドレス')" />
                    <x-text-input id="email" class="c-input" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="p-auth__form__error" />
                </div>

                <!-- Password -->
                <div class="p-auth__form__input">
                    <x-input-label for="password" class="p-auth__form__name" :value="__('パスワード')" />
                    <x-text-input id="password" class="c-input" type="password" name="password" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="p-auth__form__error" />
                </div>

                <!-- Confirm Password -->
                <div class="p-auth__form__input">
                    <x-input-label for="password_confirmation" class="p-auth__form__name" :value="__('パスワード(確認)')" />

                    <x-text-input id="password_confirmation" class="c-input"
                                  type="password"
                                  name="password_confirmation" required autocomplete="new-password" />

                    <x-input-error :messages="$errors->get('password_confirmation')" class="p-auth__form__error" />
                </div>

                <div class="flex items-center justify-end mt-4">
                    <x-primary-button class="p-auth__form__submit c-button">
                        {{ __('送信') }}
                    </x-primary-button>
                </div>
            </form>
        </div>

    </div>


</x-guest-layout>
