
<x-guest-layout>
    <x-header title="Login">
        {{ $authorize_url }}
    </x-header>
    <main class="u-bg_color--softGray">
        <div class="l-auth">
            <div class="p-auth">
                <h1 class="p-auth__title">ログイン</h1>
                <div class="p-auth__form">
                    <!-- Session Status -->
                    <x-auth-session-status class="" :status="session('status')" />

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <h2 class="p-auth__form__title">パスワードでログイン</h2>
                        <!-- Email Address -->
                        <div class="p-auth__form__input">
                            <x-input-label for="email" class="p-auth__form__name" :value="__('メールアドレス')" />
                            <x-text-input id="email" class="c-input" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                            <x-input-error :messages="$errors->get('email')" class="p-auth__form__error" />
                        </div>

                        <!-- Password -->
                        <div class="p-auth__form__input ">
                            <x-input-label for="password" class="p-auth__form__name" :value="__('パスワード')" />

                            <x-text-input id="password" class="c-input"
                                          type="password"
                                          name="password"
                                          required autocomplete="current-password" />

                            <x-input-error :messages="$errors->get('password')" class="p-auth__form__error" />
                        </div>

                        <!-- Remember Me -->
                        <div class="">
                            <input id="remember_me" type="checkbox" class="p-auth__form__checkbox" name="remember">
                            <span class="p-auth__form__name">{{ __('ログイン状態を保持する') }}</span>

                        </div>
                        <x-primary-button class="p-auth__form__submit">
                            {{ __('ログイン') }}
                        </x-primary-button>
                        <div class="p-auth__form__forget">
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}">
                                    {{ __('パスワードを忘れた方はこちら') }}
                                </a>
                            @endif
                        </div>

                        <div class="p-auth__form__separate">
                            <span class="p-auth__form__separate__border"></span>
                            <span class="p-auth__form__separate__text">または</span>
                        </div>

                        <h2 class="p-auth__form__title">Twitterでログイン</h2>

                        @if (Route::has('password.request'))
                            <a class="p-auth__form__twitter"
                               href="{{ $authorize_url }}">
                                <i class="fa-brands fa-twitter"></i>
                                Twitter
                            </a>
                        @endif



                    </form>
                </div>
            </div>
        </div>
    </main>

</x-guest-layout>
