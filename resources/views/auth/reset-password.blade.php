<x-guest-layout>
    <x-header title="ResetPssword" />
    <main class="u-bg_color--softGray">
        <div class="l-auth">
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
                            <x-text-input id="email" class="c-form--text" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
                            <x-input-error :messages="$errors->get('email')" class="p-auth__form__error" />
                        </div>

                        <!-- Password -->
                        <div class="p-auth__form__input">
                            <x-input-label for="password" class="p-auth__form__name" :value="__('パスワード')" />
                            <x-text-input id="password" class="c-form--text" type="password" name="password" required autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password')" class="p-auth__form__error" />
                        </div>

                        <!-- Confirm Password -->
                        <div class="p-auth__form__input">
                            <x-input-label for="password_confirmation" class="p-auth__form__name" :value="__('パスワード(確認)')" />

                            <x-text-input id="password_confirmation" class="c-form--text"
                                          type="password"
                                          name="password_confirmation" required autocomplete="new-password" />

                            <x-input-error :messages="$errors->get('password_confirmation')" class="p-auth__form__error" />
                        </div>

                        <x-primary-button class="p-auth__form__submit">
                            {{ __('送信') }}
                        </x-primary-button>

                    </form>
                </div>

            </div>
        </div>
    </main>



</x-guest-layout>
