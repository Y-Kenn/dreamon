import { createStore } from 'vuex';

export default createStore({
    state: {
        user_id: null,
        performances: [],
        locked_flag: false,
        process_statuses: [],
        mentions: [],
        follow_keywords: [],
        like_keywords: [],
        reserved_tweets: [],
        active_account: {
            description: "",
            id:  "",
            name: "",
            profile_image_url: "",
            public_metrics: {followers_count: 0, following_count: 0, tweet_count: 0, listed_count: 0},
            username: ""
        },
        process_status: {},
        follow_bases:[],
        protected_accounts: [],
        my_accounts: [],
        active_page: 0,
        max_accounts: import.meta.env.VITE_MAX_ACCOUNTS,
        password_exist_flag: true,
        email_address: "",
    },
    getters: {
        getActiveAccount(state){
            return state.active_account;
        },
        getMaxAccounts(state){
            return state.max_account;
        }
    },
    mutations: {
        findActiveAccount(state){
            if(state.my_accounts.length !== 0){
                for(let account of state.my_accounts){
                    if(account.active_flag){
                        state.active_account = account;
                    }
                }
            }else{
                state.active_account = {
                    description: "",
                    id:  "",
                    name: "",
                    profile_image_url: "",
                    public_metrics: {followers_count: 0, following_count: 0, tweet_count: 0, listed_count: 0},
                    username: ""
                }
            }

        },
        setMaxAccounts(state, payload){
            state.max_account = payload;
        },
        setActivePage(state, payload){
            state.active_page = payload;
        }

    },
    actions: {
        //パフォーマンスを取得
        async getPerformances(){
            const url = import.meta.env.VITE_URL_TWITTER_DATA;
            const result = await axios.get(url);
            this.state.performances = result.data;
        },
        //Twitterアカウント凍結フラグを取得
        async getLockedFlag(){
            // const url = 'http://localhost/locked';
            const url = import.meta.env.VITE_URL_LOCKED;
            const result = await axios.get(url);
            this.state.locked_flag = result.data.locked_flag;
        },
        //自動機能の稼働状況を取得
        async getProcessStatuses(){
            // const url = 'http://localhost/process-status';
            const url = import.meta.env.VITE_URL_PROCESS_STATUS;
            const result = await axios.get(url);
            this.state.process_statuses = result.data;
        },
        //メンション取得
        async getMentions(){
            // const url = 'http://localhost/mention';
            const url = import.meta.env.VITE_URL_MENTION;
            const result = await axios.get(url);
            this.state.mentions = result.data;
        },
        //フォローキーワードを取得
        async getFollowKeywords(){
            const url = import.meta.env.VITE_URL_FOLLOW_KEYWORDS;
            let result = await axios.get(url);
            this.state.follow_keywords = result.data;
        },
        //いいねキーワードを取得
        async getLikeKeywords(){
            const url = import.meta.env.VITE_URL_LIKE_KEYWORDS;
            let result = await axios.get(url);
            this.state.like_keywords = result.data;
        },
        //予約済かつ未投稿のツイートを取得
        async getReservedTweets(){
            // const url = 'http://localhost/reserved-tweet';
            const url = import.meta.env.VITE_URL_RESERVED_TWEET;
            const result = await axios.get(url);
            this.state.reserved_tweets = result.data
        },
        //現在使用中のアカウントのプロフィイール情報を取得
        async getActiveAccount(context, payload){
            const url = import.meta.env.VITE_URL_CHANGE_ACCOUNT + '/' + payload;
            const result = await axios.get(url, payload);

            this.state.active_account = result.data;
        },
        //登録済の全てのアカウントのプロフィール情報を取得
        async getMyAccounts(context){
            const url = import.meta.env.VITE_URL_CHANGE_ACCOUNT;
            const result = await axios.get(url);
            this.state.my_accounts = result.data.data;
            this.state.user_id = result.data.user_id;
            this.commit('findActiveAccount');
        },
        //アクティブアカウントに対する全自動機能の稼働状態を取得
        async getProcessStatus(){
            const url = import.meta.env.VITE_URL_PROCESS_STATUS;
            let result = await axios.get(url);
            this.state.process_status = result.data;
        },
        //フォロワー参照アカウントを取得
        async getFollowBases(){
            const url = import.meta.env.VITE_URL_TARGET_BASE;
            const result = await axios.get(url);
            this.state.follow_bases = result.data;
        },
        //アンフォロー対象外アカウントの取得
        async getProtectedAccounts(){
            const url = import.meta.env.VITE_URL_PROTECTED_ACCOUNT;
            const result = await axios.get(url);
            this.state.protected_accounts = result.data;
        },
        //パスワードが登録されているか確認
        async checkExistPassword(){
            const url = import.meta.env.VITE_URL_REGIST_PASSWORD;
            const result = await axios.get(url);
            this.state.password_exist_flag = result.data;
        },
        //メールアドレスを取得
        async getEmailAddress(){
            const url = import.meta.env.VITE_URL_EMAIL;
            const result = await axios.get(url);
            this.state.email_address = result.data.email;
        }

    }
})
