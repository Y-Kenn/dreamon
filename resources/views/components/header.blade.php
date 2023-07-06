<header class="p-header">
    <a href="{{ route('top') }}" class="p-header__logo">
        <img src="https://dreamon-s3-1.s3.ap-northeast-1.amazonaws.com/header_logo.png" alt="logo">
    </a>

    <nav>
        @if($title === 'Login' || $title === 'Terms')
            <a href="{{ env('VITE_URL_TWITTER_OAUTH') }}" class="p-header__button p-header__button--regist">Twitterアカウントで登録</a>
        @else
            <a href="{{ route('login') }}" class="p-header__button p-header__button--login">ログイン</a>
        @endif
    </nav>
</header>
