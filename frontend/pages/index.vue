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
const selectedFolderId = ref(null)

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

// フォルダを選択
const selectFolder = (folderId) => {
  selectedFolderId.value = folderId
}
// 削除処理（未実装）
const deleteFolder = (folderId) => {
  console.log(`フォルダID: ${folderId} を削除します`)
}

// 編集処理（未実装）
const editFolder = (folderId) => {
  console.log(`フォルダID: ${folderId} を編集します`)
}


// ページがマウントされた時にデータを取得
onMounted(() => {
  fetchFolders()
})
</script>

<template>
    <div class="folder-container">
      <h2>フォルダ</h2>
      <input type="text" placeholder="フォルダを追加する" />
      <button>フォルダを追加する</button>
  
      <ul>
        <li
          v-for="folder in folders"
          :key="folder.id"
          :class="{ selected: folder.id === selectedFolderId }"
          @click="selectFolder(folder.id)"
        >
          {{ folder.title }}
          <span class="actions">
            <button @click.stop="editFolder(folder.id)">編集</button>
            <button @click.stop="deleteFolder(folder.id)">削除</button>
          </span>
        </li>
      </ul>
    </div>
  </template>
  
  <style scoped>
  .folder-container {
    /* width: 300px; */
    margin: 20px;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 8px;
    background-color: #f9f9f9;
  }
  
  h2 {
    font-size: 18px;
    margin-bottom: 10px;
  }
  
  input {
    width: calc(100% - 20px);
    padding: 5px;
    margin-bottom: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
  }
  
  button {
    background-color: #007bff;
    color: white;
    border: none;
    padding: 5px 10px;
    margin-left: 5px;
    cursor: pointer;
    border-radius: 4px;
  }
  
  button:hover {
    background-color: #0056b3;
  }
  
  ul {
    list-style-type: none;
    padding: 0;
  }
  
  li {
    padding: 10px;
    margin-bottom: 5px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #fff;
    border-radius: 4px;
    border: 1px solid #ddd;
    cursor: pointer;
  }
  
  li.selected {
    background-color: #9de3ec;
    color: white;
  }
  
  .actions {
    display: flex;
    gap: 10px;
  }
  
  .actions button {
    background-color: transparent;
    color: #007bff;
    border: none;
    cursor: pointer;
  }
  
  .actions button:hover {
    text-decoration: underline;
  }
  </style>