<script setup>
import { ref, onMounted } from 'vue'
import { gql, GraphQLClient } from 'graphql-request'


// GraphQLエンドポイントの設定
const endpoint = 'http://localhost:8888/graphql' 

// クッキーからauth_tokenを取得
const authToken = useCookie('auth_token')

// GraphQLClientの初期化
const graphqlClient = new GraphQLClient(endpoint, {
  headers: {
    Authorization: `Bearer ${authToken.value}`,
  },
})

// 認証トークンの変更を監視してヘッダーを更新
watch(authToken, (newToken) => {
  graphqlClient.setHeader('Authorization', newToken ? `Bearer ${newToken}` : '')
})
// データの定義
const folders = ref([])
const title = ref("")
const selectedFolderId = ref(null)
const editingFolderId = ref(null) // 編集中のフォルダーIDを管理
const editTitle = ref("") // 編集用のタイトル

// ページネーションの状態
const foldersPage = ref(1)
const foldersLastPage = ref(1)
const foldersHasMorePages = ref(false)

// ローディング状態とエラーメッセージ
const loading = ref(false)
const errorMessage = ref('')

// エラーメッセージの定義
const newFolderErrors = ref({
  title: '',
});
const newEditFolderErrors = ref({
  title: ""
})

const GET_FOLDERS = gql`
  query GetFolders($page: Int!, $count: Int!) {
    folders(page: $page, count: $count) {
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
        hasMorePages
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

const validateNewFolder = () => {
  let isValid = true;

  if (!title.value.trim()) {  
    newFolderErrors.value.title = 'フォルダータイトルは必須です。';
    isValid = false;
  } else if(title.value.length>=20){
    newFolderErrors.value.title= 'タイトルは20文字以内に設定してください'
    isValid=false;
  }else{
    newFolderErrors.value.title = '';
  }
  return isValid;
};

const validateEditNewFolder = () => {
  let isValid = true;

  if (!editTitle.value.trim()) {  
    newEditFolderErrors.value.title = 'フォルダータイトルは必須です。';
    isValid = false;
  } else if (editTitle.value.length > 20) { // 20文字以内を超えているかをチェック
    newEditFolderErrors.value.title = 'タイトルは20文字以内に設定してください';
    isValid = false;
  } else {
    newEditFolderErrors.value.title = '';
  }

  return isValid;
};



// フォルダーの取得関数
const fetchFolders = async () => {
  try {
    const variables = { page: foldersPage.value, count: 5 }
    const response = await graphqlClient.request(GET_FOLDERS, variables)
    folders.value = response.folders.data
    foldersLastPage.value = response.folders.paginatorInfo.lastPage
    foldersHasMorePages.value = response.folders.paginatorInfo.hasMorePages
  } catch (error) {
    console.error('Error fetching folders:', error)
    alert('フォルダの取得に失敗しました。再度お試しください。')
  }
}

// フォルダーの作成関数
const createFolder = async () => {
  if (!validateNewFolder()) {
    return;
  }
  try {
    const variables = { title: title.value }
    const response = await graphqlClient.request(CREATE_FOLDER, variables)
    console.log('GraphQL response:', response.createFolder) 
    const newFolder = response.createFolder
    if(newFolder){
      console.log('フォルダ作成成功:', newFolder)
      folders.value.push(newFolder)
      title.value = ''  
    } else {
      console.error('フォルダ作成に失敗しました。')
      alert('フォルダの作成に失敗しました。')
    }
  } catch (error) {
    console.error('フォルダ作成エラー:', error)
    alert('フォルダの作成に失敗しました。再度お試しください。')
  }
}

const updateFolder = async (id, newTitle) => {
  if (!validateEditNewFolder()) {  // 修正: 関数を呼び出す
    return;
  }
  try {
    const variables = { id, title: newTitle }
    const response = await graphqlClient.request(UPDATE_FOLDER, variables)
    const folder = folders.value.find(folder => folder.id === id)
    if (folder) folder.title = newTitle
    editingFolderId.value = null 
    console.log('フォルダ更新成功:', response)
  } catch (error) {
    console.error('フォルダ更新エラー:', error)
    alert('フォルダの更新に失敗しました。再度お試しください。')
  }
}

const deleteFolder = async (id) => {
  try {
    const variables = { id }
    const response = await graphqlClient.request(DELETE_FOLDER, variables)
    folders.value = folders.value.filter(folder => folder.id !== id)
    console.log('フォルダ削除成功:', response)
  } catch (error) {
    console.error('フォルダ削除エラー:', error)
    alert('フォルダの削除に失敗しました。再度お試しください。')
  }
}

const selectFolder = (folderId) => {
  selectedFolderId.value = folderId
}

const editFolder = (folder) => {
  editingFolderId.value = folder.id 
  editTitle.value = folder.title  // 修正: editTitle を設定
}

const saveEdit = async (folderId) => {
  await updateFolder(folderId, editTitle.value)  // 修正: editTitle.value を渡す
}

const cancelEdit = () => {
  editingFolderId.value = null 
}

// ページネーション操作関数
const nextFoldersPage = () => {
  if (foldersPage.value < foldersLastPage.value) {
    foldersPage.value += 1
    fetchFolders()
  }
}

const prevFoldersPage = () => {
  if (foldersPage.value > 1) {
    foldersPage.value -= 1
    fetchFolders()
  }
}


onMounted(() => {
  fetchFolders()
})
</script>

<template>
    <div class="folder-container">
      <h2>フォルダ</h2>
      
      <!-- 新規フォルダー追加フォーム -->
      <div class="add-folder-form">
        <input type="text" v-model="title" placeholder="フォルダを追加する" />
        <span v-if="newFolderErrors.title" class="error">{{ newFolderErrors.title }}</span>
        <button @click="createFolder">フォルダを追加する</button>
      </div>
      
      <!-- フォルダー一覧 -->
      <ul>
        <li
          v-for="folder in folders"
          :key="folder.id"
          :class="{ selected: folder.id === selectedFolderId }"
          @click="selectFolder(folder.id)"
        >
          <!-- 編集モード -->
          <template v-if="editingFolderId === folder.id">
            <div class="edit-form">
              <input type="text" v-model="editTitle" />
              <span v-if="newEditFolderErrors.title" class="error">{{newEditFolderErrors.title}}</span>
            </div>
            <span class="actions">
              <button @click="saveEdit(folder.id)">保存</button>
              <button @click="cancelEdit">戻る</button>
            </span>
          </template>
  
          <!-- 通常表示モード -->
          <template v-else>
            <span>{{ folder.title }}</span>
            <span class="actions">
              <button @click.stop="editFolder(folder)">編集</button>
              <button @click="deleteFolder(folder.id)">削除</button>
              <NuxtLink :to="`/folders/${folder.id}`"><p>詳細ページ</p></NuxtLink>
            </span>
          </template>
        </li>
      </ul>
      <div class="pagination">
        <button @click="prevFoldersPage" :disabled="foldersPage === 1">前へ</button>
        <span>ページ {{ foldersPage }} / {{ foldersLastPage }}</span>
        <button @click="nextFoldersPage" :disabled="!foldersHasMorePages">次へ</button>
      </div>
    </div>
  </template>
  



  <style scoped>
.error {
  color: red;
  font-size: 12px;
  margin-top: 5px;
  display: block;
}

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

.add-folder-form, .edit-form {
  display: flex;
  flex-direction: column;
}

.add-folder-form input, .edit-form input {
  padding: 5px;
  margin-bottom: 5px;
  border: 1px solid #ddd;
  border-radius: 4px;
  box-sizing: border-box;
}

button {
  background-color: #007bff;
  color: white;
  border: none;
  padding: 5px 10px;
  margin-top: 5px;
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
  align-items: flex-start; /* 修正: center から flex-start に変更 */
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
  align-items: center;
  gap: 10px;
}

.actions p {
  background-color: transparent;
  color: #49c3d8;
  border: none;
  cursor: pointer;
}

.actions button {
  background-color: #007bff; 
  color: white; 
  border: none;
  cursor: pointer;
  border-radius: 4px; 
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); 
  transition: background-color 0.3s ease, transform 0.3s ease;
}

.actions button:hover {
  background-color: #0056b3; 
  transform: translateY(-2px); 
}

.actions button:active {
  background-color: #00408d; 
  transform: translateY(0);
}

.actions button:focus {
  outline: none; 
  box-shadow: 0 0 0 3px rgba(186, 212, 240, 0.4); 
}

/* 編集モードのフォームを縦方向に配置 */
.edit-form {
  width: 100%;
  display: flex;
  flex-direction: column;
}
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 10px;
  margin-top: 20px;
}

.pagination button {
  background-color: #007bff;
  color: white;
  border: none;
  padding: 6px 12px;
  cursor: pointer;
  border-radius: 4px;
}

.pagination button:disabled {
  background-color: #cccccc;
  cursor: not-allowed;
}

.pagination span {
  font-weight: bold;
}
</style>