<script setup>
import { ref, onMounted } from 'vue'
import { gql, GraphQLClient } from 'graphql-request'

const endpoint = 'http://localhost:8888/graphql' 
const graphqlClient = new GraphQLClient(endpoint)

const folders = ref([])
const title =ref("")
const selectedFolderId = ref(null)
const editingFolderId = ref(null) // 編集中のフォルダーIDを管理
const editTitle = ref("") // 編集用のタイトル


// GraphQLクエリ
const GET_FOLDER = gql`
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

const CREATE_FOLDER = gql`
  mutation CreateFolder($title: String!) {
    createFolder(title: $title) {
      id
      title
      created_at
      updated_at
    }
  }
`

const UPDATE_FOLDER = gql`
  mutation UpdateFolder($id: ID!, $title: String!) {
    updateFolder(id: $id, title: $title) {
      id
      title
      updated_at
    }
  }
`

const DELETE_FOLDER = gql`
  mutation DeleteFolder($id: ID!) {
    deleteFolder(id: $id) {
      id
      title
    }
  }
`

//フォルダーのREAD
const fetchFolders = async () => {
  try {
    const variables = { first: 10 }
    const response = await graphqlClient.request(GET_FOLDER, variables)
    folders.value = response.folders.data
  } catch (error) {
    console.error('Error fetching folders:', error)
  }
}
//フォルダーのCREATE
const createFolder = async () => {
  try {
    const variables = { title: title.value }
    const response = await graphqlClient.request(CREATE_FOLDER, variables)
    const newFolder = response.createFolder
    console.log(response)
    // 新しく作成されたフォルダをリストに追加
    folders.value.push(newFolder)
    title.value = ''  // 入力をクリア
    console.log('フォルダ作成成功:', response)
    // フォルダのリストを更新するなどの処理
  } catch (error) {
    console.error('フォルダ作成エラー:', error)
  }
}
//フォルダーのUPDATE
const updateFolder = async (id, newTitle) => {
  try {
    const variables = { id, title: newTitle }
    const response = await graphqlClient.request(UPDATE_FOLDER, variables)
    const folder = folders.value.find(folder => folder.id === id)
    if (folder) folder.title = newTitle
    editingFolderId.value = null // 編集モード終了
  } catch (error) {
    console.error('フォルダ更新エラー:', error)
  }
}

const deleteFolder = async (id) => {
  try {
    const variables = { id }
    const response = await graphqlClient.request(DELETE_FOLDER, variables)
    folders.value = folders.value.filter(folder => folder.id !== id)
    // reloadNuxtApp()
    console.log('フォルダ削除成功:', response)

    // フォルダのリストを更新するなどの処理
  } catch (error) {
    console.error('フォルダ削除エラー:', error)
  }
}

const selectFolder = (folderId) => {
  selectedFolderId.value = folderId
}



const editFolder = (folder) => {
  editingFolderId.value = folder.id // 編集中のフォルダーIDをセット
  editTitle.value = folder.title // 編集中のタイトルをセット
}

const saveEdit = async (folderId) => {
  await updateFolder(folderId, editTitle.value)
}

const cancelEdit = () => {
  editingFolderId.value = null // 編集モードを解除
}


onMounted(() => {
  fetchFolders()
})
</script>

<template>
    <div class="folder-container">
      <h2>フォルダ</h2>
      <input type="text" v-model="title" placeholder="フォルダを追加する" />
      <button @click="createFolder">フォルダを追加する</button>
  
      <ul>
        <li
          v-for="folder in folders"
          :key="folder.id"
          :class="{ selected: folder.id === selectedFolderId }"
          @click="selectFolder(folder.id)"
        >
          <!-- {{ folder.title }}
          <span class="actions">
            <button @click.stop="editFolder(folder.id)">編集</button>
            <button @click="deleteFolder(folder.id)">削除</button> -->
          <!-- </span> -->
          <template v-if="editingFolderId === folder.id">
          <input type="text" v-model="editTitle" />
          <span class="actions">
            <button @click="saveEdit(folder.id)">保存</button>
            <button @click="cancelEdit">戻る</button>
          </span>
        </template>
        <template v-else>
          {{ folder.title }}
          <span class="actions">
            <button @click.stop="editFolder(folder)">編集</button>
            <button @click="deleteFolder(folder.id)">削除</button>
            <NuxtLink :to="`/folders/${folder.id}`"><p>詳細ページ</p></NuxtLink>
         
          </span>
        </template>
        </li>
      </ul>
    </div>
  </template>
  
  <style scoped>
  .folder-container {
    width: 500px;
    margin: 20px auto;
    padding: 20px;
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
    box-sizing: border-box;
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
  .actions p{
    background-color: transparent;
    color: #49c3d8;
    border: none;
    cursor: pointer;
    width: 80px;
  }
  .actions button {
    background-color: transparent;
    color: #007bff;
    border: none;
    cursor: pointer;
    width: 80px;
  }
  
  .actions button:hover {
    text-decoration: underline;
  }
  </style>