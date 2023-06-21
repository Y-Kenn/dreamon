<header class="p-header">
    <a href=""class="p-header__logo">
        <img src="/img/header_logo.png" alt="logo">
    </a>

    <nav>
        @if($title === 'Login')
            <a href="{{$slot}}" class="p-header__button p-header__button--regist">Twitterアカウントで登録</a>
        @else
            <a href="{{ route('login') }}" class="p-header__button p-header__button--login">ログイン</a>
        @endif
    </nav>
</header>