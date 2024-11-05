// composables/useAuth.js
import { ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { gql, GraphQLClient } from 'graphql-request'

export function useAuth() {
  const router = useRouter()
  const authToken = useCookie('auth_token') // クッキーを取得・設定
  const endpoint = 'http://localhost:8888/graphql'
  const graphqlClient = new GraphQLClient(endpoint, {
    headers: {
      Authorization: authToken.value ? `Bearer ${authToken.value}` : '',
    },
  })

  // 認証トークンの変更を監視してヘッダーを更新
  watch(authToken, (newToken) => {
    graphqlClient.setHeader('Authorization', newToken ? `Bearer ${newToken}` : '')
  })

  
  const register = async (name, email, password) => {
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
    try {
      const variables = { name, email, password }
      const response = await graphqlClient.request(REGISTER_MUTATION, variables)
      authToken.value = response.register.token
      graphqlClient.setHeader('Authorization', `Bearer ${response.register.token}`)
      return response.register.user
    } catch (error) {
      console.error('登録エラー:', error)
      throw error
    }
  }

  const login = async (email, password) => {
    const LOGIN_MUTATION = gql`
      mutation Login($email: String!, $password: String!) {
        login(email: $email, password: $password) {
          user {
            id
            name
            email
          }
          token
        }
      }
    `
    try {
      const variables = { email, password }
      const response = await graphqlClient.request(LOGIN_MUTATION, variables)
      authToken.value = response.login.token
      graphqlClient.setHeader('Authorization', `Bearer ${response.login.token}`)
      return response.login.user
    } catch (error) {
      console.error('ログインエラー:', error)
      throw error
    }
  }

  const logout = () => {
    authToken.value = ''
    graphqlClient.setHeader('Authorization', '')
    router.push('/login')
  }

  const isAuthenticated = ref(!!authToken.value)

  watch(authToken, (newToken) => {
    isAuthenticated.value = !!newToken
  })

  return {
    authToken,
    graphqlClient,
    register,
    login,
    logout,
    isAuthenticated,
  }
}