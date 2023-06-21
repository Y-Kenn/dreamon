import { createStore } from 'vuex';

export default createStore({
    state: {
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
        max_accounts: 3,
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
            console.log('findMyAccount');
            console.log(state.my_accounts.length);
            if(state.my_accounts.length !== 0){
                for(let account of state.my_accounts){
                    if(account.active_flag){
                        state.active_account = account;
                    }
                }
            }else{
                console.log('empty')
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
            const url = 'http://localhost/twitter-data';
            const result = await axios.get(url);
            this.state.performances = result.data;
        },
        //Twitterアカウント凍結フラグを取得
        async getLockedFlag(){
            const url = 'http://localhost/locked';
            const result = await axios.get(url);
            this.state.locked_flag = result.data.locked_flag;
        },
        //自動機能の稼働状況を取得
        async getProcessStatuses(){
            const url = 'http://localhost/process-status';
            const result = await axios.get(url);
            this.state.process_statuses = result.data;
            console.log('process_statuses');
            console.log(this.state.process_statuses);
        },
        //メンション取得
        async getMentions(){
            const url = 'http://localhost/mention';
            const result = await axios.get(url);
            this.state.mentions = result.data;
        },
        //フォローキーワードを取得
        async getFollowKeywords(){
            let result = await axios.get('http://localhost/follow-keywords');
            this.state.follow_keywords = result.data;
        },
        //いいねキーワードを取得
        async getLikeKeywords(){
            let result = await axios.get('http://localhost/like-keywords');
            this.state.like_keywords = result.data;
        },
        //予約済かつ未投稿のツイートを取得
        async getReservedTweets(){
            const url = 'http://localhost/reserved-tweet';
            const result = await axios.get(url);
            this.state.reserved_tweets = result.data
        },
        //現在使用中のアカウントのプロフィイール情報を取得
        async getActiveAccount(context, payload){
            const url = 'http://localhost/change-account' + '/' + payload;
            const result = await axios.get(url, payload);
            console.log('action');
            
            this.state.active_account = result.data;
            console.log(this.state.active_account);
        },
        //登録済の全てのアカウントのプロフィール情報を取得
        async getMyAccounts(context){
            const url = 'http://localhost/change-account';
            const result = await axios.get(url);
            if(result.data){
                this.state.my_accounts = result.data;
            }else{
                this.state.my_accounts = [];
                console.log('empty');
            }
            this.commit('findActiveAccount');
            console.log('action - getMyAccounts');
            console.log(result.data);
        },
        //アクティブアカウントに対する全自動機能の稼働状態を取得
        async getProcessStatus(){
            const url = 'http://localhost/process-status';
            let result = await axios.get(url);
            this.state.process_status = result.data;
            console.log('process-status');
            console.log(this.state.process_status);
        },
        //フォロワー参照アカウントを取得
        async getFollowBases(){
            const url = 'http://localhost/target-base';
            const result = await axios.get(url);
            this.state.follow_bases = result.data;
            console.log('get follow bases');
            console.log(this.state.follow_bases);
        },
        //アンフォロー対象外アカウントの取得
        async getProtectedAccounts(){
            const url = 'http://localhost/protected-account';
            const result = await axios.get(url);
            this.state.protected_accounts = result.data;
            console.log('get protected accounts');
            console.log(this.state.protected_accounts);
        },
        //パスワードが登録されているか確認
        async checkExistPassword(){
            const url = 'http://localhost/regist-password';
            const result = await axios.get(url);
            this.state.password_exist_flag = result.data;
        },
        //メールアドレスを取得
        async getEmailAddress(){
            const url = 'http://localhost/email';
            const result = await axios.get(url);
            this.state.email_address = result.data.email;
            console.log(result.data)
        }
        
    }
})