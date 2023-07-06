<x-guest-layout>
    <x-header title="Contact" />
    <div class="l-top">
        <div class="p-top">
            <div class="p-top__section">
                <div class="p-top__section__inner">
                    <div class="p-top__helo_copy">
                        <div class="p-top__helo_copy__inner">
                            <span class="p-top__copy--big">24時間フル稼働</span>
                            <span class="p-top__copy--big">自動運用ツールは</span>
                            <div>
                                <span class="p-top__copy--biggest">Kamitter</span>
                                <i class="u-font_color--mainblue p-top__copy--biggest fa-regular fa-circle-check"></i>
                            </div>
                            <!--登録ボタン-->
                            <a class="p-top__regist c-button" href="{{env('VITE_URL_TWITTER_OAUTH')}}">
                                <span class="u-font_size--l">Twitterアカウントで</span>
                                <span class="u-font_size--xl">無料登録</span>
                            </a>
                        </div>

                    </div>
                    <div class="p-top__displays">
                        <div class="p-top__displays__pc p-top__displays__pc--front">
                            <img src="/img/display1.png" alt="logo">
                        </div>
                        <div class="p-top__displays__pc p-top__displays__pc--back">
                            <img src="/img/display2.png" alt="logo">
                        </div>
                        <div class="p-top__displays__smapho">
                            <img src="/img/smapho1.png" alt="logo">
                        </div>
                        <i class="p-top__displays__icon p-top__displays__icon--like fa-solid fa-heart"></i>
                        <i class="p-top__displays__icon p-top__displays__icon--follow fa-solid fa-user-plus"></i>
                        <i class="p-top__displays__icon p-top__displays__icon--unfollow fa-solid fa-user-large-slash"></i>
                        <i class="p-top__displays__icon p-top__displays__icon--tweet fa-solid fa-comment-medical"></i>
                    </div>
                </div>
            </div>
            <div class="p-top__section">
                <div class="p-top__section__inner">
                    <span class="p-top__copy--big">Twitter運用のよくある悩み</span>
                </div>
                <div class="p-top__section__inner">
                    <div class="p-top__problem">
                        <img src="img/busy.png" class="p-top__problem__img">
                        <span class="p-top__copy--middle">運用の時間が確保できない</span>
                    </div>
                    <div class="p-top__problem">
                        <img src="img/data.png" class="p-top__problem__img">
                        <span class="p-top__copy--middle">フォロワーが思うように増えない</span>
                    </div>
                    <div class="p-top__problem">
                        <img src="img/money.png" class="p-top__problem__img">
                        <span class="p-top__copy--middle">顧客獲得に繋がらない</span>
                    </div>
                </div>
            </div>
            <div class="p-top__section">
                <div class="p-top__section__inner">
                    <span class="p-top__copy p-top__copy--big">Kamitterなら自動運用で</span>
                    <span class="u-highlight--blue p-top__copy p-top__copy--big">圧倒的パフォーマンス</span>
                </div>
                <div class="p-top__section__inner">
                    <img src="https://dreamon-s3-1.s3.ap-northeast-1.amazonaws.com/graph.png" class="p-top__graph">
                </div>
                <div class="p-top__section__inner">
                    <div class="p-top__text">
                        <p>各Twitterアカウントのフォロー数・フォロワー数をもとに、Kamitter独自のアルゴリズムでフォロー・フォロー解除の実行可否の判断をするので安心して運用を任せることができます。
                        </p>
                    </div>
                </div>
            </div>
            <!--Kamitter機能-->
            <div class="p-top__section">
                <div class="p-top__section__inner">
                    <span class="p-top__copy--big">Kamitterの機能</span>
                </div>
                <!--フォロー-->
                <div class="p-top__function p-top__function--follow">
                    <div class="p-top__function__title">
                        <i class="u-font_color--mainblue c-icon--shadow fa-solid fa-user-plus"></i>
                        <span> 自動フォロー</span>
                    </div>
                    <p class="p-top__function__text">
                        1日最大500件、指定したキーワードにヒットするアカウントを自動でフォローします。
                        除外キーワードも設定できるのでターゲットを確実に絞り込むことができます。
                    </p>
                </div>
                <!--アンフォロー-->
                <div class="p-top__function p-top__function--unfollow">
                    <div class="p-top__function__title">
                        <i class="u-font_color--lightpurple c-icon--shadow fa-solid fa-user-large-slash"></i>
                        <span> 自動アンフォロー</span>
                    </div>
                    <p class="p-top__function__text">
                        1日最大500件、顧客となる可能性の低いアカウントを自動でフォロー解除します。
                        保護アカウントリストを作成できるので大切なアカウントはアンフォローしません。
                    </p>
                </div>
                <!--いいね-->
                <div class="p-top__function p-top__function--like">
                    <div class="p-top__function__title">
                        <i class="u-font_color--pink c-icon--shadow fa-solid fa-heart"></i>
                        <span> 自動いいね</span>
                    </div>
                    <p class="p-top__function__text">
                        1日最大1000件、指定したキーワードにヒットするツイートに自動でいいねします。
                        除外キーワードも設定できるので関係のないツイートを避けることができます。
                    </p>
                </div>
                <!--ツイート予約-->
                <div class="p-top__function p-top__function--reserve">
                    <div class="p-top__function__title">
                        <i class="u-font_color--middleblue c-icon--shadow fa-solid fa-comment-medical"></i>
                        <span> ツイート予約</span>
                    </div>
                    <p class="p-top__function__text">
                        好きな時間にツイートを予約できます。
                        予定がある日でも、毎日ツイートを継続することが可能です。
                    </p>
                </div>
                <!--複数アカウント管理-->
                <div class="p-top__function p-top__function--accounts">
                    <div class="p-top__function__title">
                        <i class="u-font_color--lightgreen c-icon--shadow fa-solid fa-arrow-right-arrow-left"></i>
                        <span> 複数アカウント管理</span>
                    </div>
                    <p class="p-top__function__text">
                        10アカウントまで登録することができます。
                        全てのアカウントで同時にフォロー/アンフォロー/いいねの自動機能、ツイート予約を使用することができます。
                    </p>
                </div>
                <!--登録ボタン-->
                <a class="p-top__regist c-button" href="{{env('VITE_URL_TWITTER_OAUTH')}}">
                    <span class="u-font_size--l">Twitterアカウントで</span>
                    <span class="u-font_size--xl">無料登録</span>
                </a>
            </div>
            <!--QA-->
            <div class="p-top__section">
                <div class="p-top__section__inner">
                    <span class="p-top__copy p-top__copy--big">よくある質問</span>
                </div>
                <div class="p-top__qa">
                    <span class="p-top__qa__q">Q. 使用開始後しばらくはフォローが少ないのはなぜですか？</span>
                    <p class="p-top__qa__a">
                        KamitterではTwitterアカウントの凍結を防止するため、各アカウントのフォロワー数をもとに1日に可能なフォロー上限数を設定しています。
                        フォロワー数が増加するに従いフォローする数が増えていきます。
                    </p>
                </div>
                <div class="p-top__qa">
                    <span class="p-top__qa__q">Q. 凍結の心配はありませんか？</span>
                    <p class="p-top__qa__a">
                        1日のフォロー・アンフォロー上限の設定や、人が操作しているかのような独自のアルゴリズムにより、自動化によるアカウント凍結をされないよう対策しています。
                        ただし、100％を保証するものではありませんのでご了承ください。
                        アカウント凍結された場合はKamitterが凍結を検出し、速やかに通知メールが送信されます。
                    </p>
                </div>
                <div class="p-top__qa">
                    <span class="p-top__qa__q">Q. スマホでも利用できますか？</span>
                    <p class="p-top__qa__a">
                        スマートフォン、PCの両方に対応しています。
                        ブラウザ上で起動や設定をしていただき、自動機能等の処理はKamitterのサーバー上で実行されますので、24時間自動で運用することが可能です。
                </div>
                <!--登録ボタン-->
                <a class="p-top__regist c-button" href="{{env('VITE_URL_TWITTER_OAUTH')}}">
                    <span class="u-font_size--l">Twitterアカウントで</span>
                    <span class="u-font_size--xl">無料登録</span>
                </a>
            </div>
        </div>
    </div>

    </main>
</x-guest-layout>
