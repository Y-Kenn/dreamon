<x-guest-layout>
    <x-header title="Contact" />
    <main class="u-bg_color--softGray">
        <div class="l-contact">
            <div class="p-auth">
                <h1 class="p-auth__title">お問合せ</h1>
                <div class="p-auth__form">
                    <form method="POST" action="{{ route('contact.create') }}">
                        @csrf

                        @if (session('flash_message'))
                            <div class="p-auth__form__success">
                                {{ session('flash_message') }}
                            </div>
                        @endif
                        <div class="p-auth__form__input">
                            <x-input-label for="email" class="p-auth__form__name" :value="__('メールアドレス')" />
                            <x-text-input id="email" class="c-input" type="email" name="email" :value="old('email')" required autofocus />
                            <x-input-error :messages="$errors->get('email')" class="p-auth__form__error" />
                        </div>

                        <div class="p-auth__form__input">
                            <x-input-label for="text" class="p-auth__form__name" :value="__('お問合せ内容')" />
                            {{--                    <x-text-input id="text" class="c-input" type="textarea" name="email" :value="old('email')" required autofocus autocomplete="username" />--}}
                            <textarea id="text" class="c-input" name="text" required autofocus>{{ old('text') }}</textarea>
                            <x-input-error :messages="$errors->get('text')" class="p-auth__form__error" />
                        </div>
                        <x-primary-button class="p-auth__form__submit c-button">
                            {{ __('送信') }}
                        </x-primary-button>
                    </form>

                </div>
            </div>

        </div>
    </main>

</x-guest-layout>
