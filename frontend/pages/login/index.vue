<script setup>
import { ref } from 'vue';
import { gql, GraphQLClient } from 'graphql-request';
import { useRouter } from 'vue-router';
import { useAuth } from '~/composables/useAuth';

const router = useRouter()
const {authToken,graphqlClient}=useAuth()

const email = ref('');
const password = ref('');
const errors = ref({
  email: '',
  password: ''
});



const LOGIN_MUTATION = gql`
  mutation Login($email: String!, $password: String!) {
    login(email: $email, password: $password) {
      user{
      id
      name
      email
      }
      token
    }
  }
`;

const validateForm = () => {
  let isValid = true;
  errors.value = {
    email: '',
    password: ''
  };

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
  }

  return isValid;
};

const login = async () => {
  if (!validateForm()) {
    return;
  }

  try {
    const variables = {
      email: email.value,
      password: password.value
    };
    const response = await graphqlClient.request(LOGIN_MUTATION, variables);
    console.log('ログイン成功:', response);
    //クッキーにトークンを保存
    authToken.value = response.login.token
    //認証ヘッダーの更新
    graphqlClient.setHeader('Authorization', `Bearer ${response.login.token}`)
    alert('ログインが成功しました！');    
    router.push('/');
  } catch (error) {
    console.error('ログインエラー:', error)
    if (error.response && error.response.errors) {
      error.response.errors.forEach(err => {
        alert(err.message)
      })
    } else {
      alert('ログインに失敗しました。再度お試しください。')
    }
  }
}
</script>

<template>
  <div class="auth-container">
    <h2>ログイン</h2>
    <form @submit.prevent="login">
      <div class="auth-form">
        <input type="email" v-model="email" placeholder="メールアドレス" />
        <span v-if="errors.email" class="error">{{ errors.email }}</span>
        
        <input type="password" v-model="password" placeholder="パスワード" />
        <span v-if="errors.password" class="error">{{ errors.password }}</span>
        
        <button type="submit">ログイン</button>
      </div>
    </form>
    <NuxtLink to="/register" class="register-link">新規登録はこちら</NuxtLink>
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

.register-link {
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
