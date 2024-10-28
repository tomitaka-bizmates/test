<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { gql, GraphQLClient } from 'graphql-request'

const router = useRouter()
const authToken = useCookie('auth_token') // クッキーを取得・設定
const endpoint = 'http://localhost:8888/graphql'
const graphqlClient = new GraphQLClient(endpoint, {
  headers: {
    Authorization: authToken.value ? `Bearer ${authToken.value}` : '',
  },
})

const name = ref('');
const email = ref('');
const password = ref('');
const confirmPassword = ref('');
const errors = ref({
  name: '',
  email: '',
  password: '',
  confirmPassword: ''
});



const REGISTER_MUTATION = gql`
  mutation Register($name: String!, $email: String!, $password: String!) {
    register(name: $name, email: $email, password: $password) {
      user {
        id
        name
        email
      }
      token
    }
  }
`
const validateForm = () => {
  let isValid = true;
  errors.value = {
    name: '',
    email: '',
    password: '',
    confirmPassword: ''
  };

  if (!name.value.trim()) {
    errors.value.name = '名前は必須です。';
    isValid = false;
  }

  if (!email.value.trim()) {
    errors.value.email = 'メールアドレスは必須です。';
    isValid = false;
  } else if (!/\S+@\S+\.\S+/.test(email.value)) {
    errors.value.email = '有効なメールアドレスを入力してください。';
    isValid = false;
  }

  if (!password.value.trim()) {
    errors.value.password = 'パスワードは必須です。';
    isValid = false;
  } else if (password.value.length < 6) {
    errors.value.password = 'パスワードは6文字以上である必要があります。';
    isValid = false;
  }
  if (password.value !== confirmPassword.value) {
    errors.value.confirmPassword = 'パスワードが一致しません。';
    isValid = false;
  }

  return isValid;
};


const register =async()=>{
    if(!validateForm()){
        return
    }
    
  try {
    const variables = {
      name: name.value,
      email: email.value,
      password: password.value
    };
    const response = await graphqlClient.request(REGISTER_MUTATION, variables);    
    console.log('登録成功:', response);
    alert('登録が完了しました！ログインページに移動します。');    

    // クッキーにトークンを保存
    authToken.value = response.register.token
    // 認証ヘッダーを更新
    graphqlClient.setHeader('Authorization', `Bearer ${response.register.token}`)


    router.push('/login');
  } catch (error) {
    console.error('登録エラー:', error);
    if (error.response && error.response.errors) {
      error.response.errors.forEach(err => {
        alert(err.message);
      });}else{
    alert('登録に失敗しました。再度お試しください。');
      }
  }
}




</script>

<template>
    <div class="auth-container">
      <h2>新規登録</h2>
      <form @submit.prevent="register">
      <div class="auth-form">
        <input type="text" v-model="name" placeholder="名前" />
        <span v-if="errors.name" class="error">{{ errors.name }}</span>
        <input type="email" v-model="email" placeholder="メールアドレス" />
        <span v-if="errors.email" class="error">{{ errors.email }}</span>
        <input type="password" v-model="password" placeholder="パスワード" />
        <span v-if="errors.password" class="error">{{ errors.password }}</span>
        <input type="password" v-model="confirmPassword"  placeholder="パスワード確認" />
        <span v-if="errors.confirmPassword" class="error">{{ errors.confirmPassword }}</span>
        <button type="submit">登録する</button>
      </div>
    </form>
      <NuxtLink to="/login" class="login-link">既にアカウントをお持ちですか？ログイン</NuxtLink>
    </div>
  </template>  
  <style scoped>
  .auth-container {
    width: 400px;
    margin: 50px auto;
    padding: 20px;
    border: 1px solid #ccc;
    border-radius: 8px;
    background-color: #f9f9f9;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  }
  
  h2 {
    font-size: 18px;
    margin-bottom: 10px;
    text-align: center;
  }
  
  .auth-form {
    display: flex;
    flex-direction: column;
  }
  
  .auth-form input {
    padding: 10px;
    margin-bottom: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
  }
  
  button {
    background-color: #007bff;
    color: white;
    border: none;
    padding: 10px;
    cursor: pointer;
    border-radius: 4px;
  }
  
  button:hover {
    background-color: #0056b3;
  }
  
  .login-link {
    display: block;
    text-align: center;
    margin-top: 10px;
    color: #007bff;
  }
  .error {
  color: red;
  font-size: 12px;
  margin-bottom: 10px;
}
  </style>
  