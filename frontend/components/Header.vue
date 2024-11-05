<script setup>
import {gql, GraphQLClient } from 'graphql-request';
import { useAuth } from '~/composables/useAuth';

const router = useRouter()

const{authToken,graphqlClient}=useAuth()

const LOGOUT_MUTATION = gql`
  mutation Logout {
    logout
  }
`

const logout = async () => {
  try {

    const response = await graphqlClient.request(LOGOUT_MUTATION)
    console.log('ログアウト成功:', response)
    authToken.value = null
    graphqlClient.setHeader('Authorization', '')
    alert('ログアウトが完了しました。')
    router.push('/login')
  } catch (error) {
    console.error('ログアウトエラー:', error)
    alert('ログアウトに失敗しました。再度お試しください。')
  }
}

</script>

<template>
    <header class="header">
      <div class="header-container">
        <div class="logo">
          <NuxtLink to="/">
          <img src="/todo.png" alt="Logo" />
        </NuxtLink>
        </div>
        <nav class="nav">
         <h1 class="title">TODOアプリ</h1>
        </nav>
        <div class="search-bar">
          <input type="text" placeholder="Search..." />
          <button>検索</button>
        </div>

       <button v-if="authToken" @click="logout">ログアウト</button>
  
      </div>
    </header>
  </template>
  

  
  <style scoped>
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
  .header {
    width: 100%;
    background-color: #c9d9ea;
    padding: 10px 0;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  }
  
  .header-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
  }

  .nav .title {
  color: #007bff; 
  font-size: 24px; 
}
  
  .logo img {
    height: 40px;
  }
   
  .search-bar {
    display: flex;
    gap: 10px;
  }
ふうふう
  .search-bar input {
    padding: 5px;
    border-radius: 4px;
    border: none;
  }
  
  .search-bar button {
    padding: 5px 10px;
    border-radius: 4px;
    background-color: white;
    color: #007bff;
    border: none;
    cursor: pointer;
  }
  
  .search-bar button:hover {
    background-color: #f1f1f1;
  }
  
  @media (max-width: 768px) {
    .header-container {
      flex-direction: column;
      align-items: flex-start;
    }
  
    .nav ul {
      flex-direction: column;
      gap: 10px;
    }
  
    .search-bar {
      width: 100%;
    }
  
    .search-bar input {
      flex: 1;
    }
  
    .search-bar button {
      width: auto;
    }
  }
  </style>
  