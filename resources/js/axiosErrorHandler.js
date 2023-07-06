export const axiosErrorHandle = ()=>{
    axios.interceptors.response.use(
    (response) => response, // 成功時の処理
    (error) => {            // エラー時の処理
      switch (error.response?.status) {
        case 422:
          return Promise.reject(error.response?.data);

        case 404:
          return Promise.reject(error.response?.data);

        // セッションが切れている場合はログインページへ遷移
        case 401:
            window.location.href = import.meta.env.VITE_URL_LOGIN;

          return Promise.reject(error.response?.data);
        default:

          return Promise.reject(error.response?.data);
      }
    }
  );
}

