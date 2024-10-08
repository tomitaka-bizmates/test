<script setup>
import { ref, onMounted } from 'vue'
import { request, gql, GraphQLClient } from 'graphql-request'

const endpoint = 'http://localhost:8888/graphql' // Lighthouseのエンドポイント

// GraphQLクライアントのインスタンスを作成
const graphqlClient = new GraphQLClient(endpoint)

// GraphQLクエリ
const GET_FOLDERS = gql`
  query GetFolders($first: Int!) {
    folders(first: $first) {
      data {
        id
        title
        created_at
        updated_at
      }
      paginatorInfo {
        total
        currentPage
        lastPage
      }
    }
  }
`

// データとエンドポイントを定義
const folders = ref([])


// コンポーネントがマウントされたときにデータを取得
const fetchFolders = async () => {
  try {
    const variables = { first: 10 }
    const response = await graphqlClient.request(GET_FOLDERS, variables)
    folders.value = response.folders.data
  } catch (error) {
    console.error('Error fetching folders:', error)
  }
}

// ページがマウントされた時にデータを取得
onMounted(() => {
  fetchFolders()
})
</script>

<template>
  <div>
    <h1>フォルダ一覧</h1>
    <ul>
      <li v-for="folder in folders" :key="folder.id">
        {{ folder.title }} - 作成日時: {{ folder.created_at }}
      </li>
    </ul>
  </div>
</template>

<style scoped>
h1 {
  font-size: 24px;
  margin-bottom: 10px;
}

ul {
  list-style-type: none;
  padding: 0;
}

li {
  padding: 5px 0;
}
</style>
