export const axiosErrorHandle = ()=>{
    axios.interceptors.response.use(
    (response) => response, // 成功時の処理 今回の例ではresponseを返すだけ
    (error) => {            // エラー時の処理
      switch (error.response?.status) {
        case 422:
  　　　　　　　　　　　　　　　　　// ステータスコード別の処理

          // Promise reject オブジェクトを必ずリターンする
          return Promise.reject(error.response?.data);
        case 404:
          // ステータスコード別の処理

          return Promise.reject(error.response?.data);
        case 401:
  　　　　　 console.log('axios error status : 401');// ステータスコード別の処理
            window.location.href = import.meta.env.VITE_URL_LOGIN;

          return Promise.reject(error.response?.data);
        default:
  　　　　　　　　　　　　　　　　　// デフォルトの処理

          return Promise.reject(error.response?.data);
      }
    }
  );
}

