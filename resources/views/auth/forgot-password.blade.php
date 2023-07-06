@php
$test = 'test';
@endphp
<x-guest-layout message="Forget-Password">
<x-header title="Forget-Password"/>
    <main class="u-bg_color--softGray">
        <div class="l-auth">
            <div class="p-auth">
                <h1 class="p-auth__title">パスワード再発行</h1>
                <p>パスワード再設定の案内メールを送信します。</p>
                <p>Kamitterに登録済のメールアドレスを入力してください。</p>

                <div class="p-auth__form">
                    <!-- Session Status -->
                    <x-auth-session-status class="p-auth__form__success" :status="session('status')" />

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <!-- Email Address -->
                        <div class="p-auth__form__input">
                            <x-input-label for="email" :value="__('メールアドレス')" class="p-auth__form__name" />
                            <x-text-input id="email" class="" class="c-input" type="email" name="email" :value="old('email')" required autofocus />
                            <x-input-error :messages="$errors->get('email')" class="p-auth__form__error" />
                        </div>

                        <div class="">
                            <x-primary-button class="p-auth__form__submit">
                                {{ __('送信') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

</x-guest-layout>
