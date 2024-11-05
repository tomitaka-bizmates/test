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
      router.push('/dashboard') // ログイン後のリダイレクト先
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

  return {
    authToken,
    graphqlClient,
    login,
    logout,
  }
}
