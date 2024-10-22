<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { gql, GraphQLClient } from 'graphql-request'

// ルート情報の取得
const route = useRoute();
const folderId = route.params.id

// データの定義
const folder = ref(null)
const tasks = ref([])

// タスク追加用のフォームデータ
const newTaskTitle = ref("")
const newTaskStatus = ref(1) // デフォルトステータス
const newTaskDueDate = ref("")

// 編集モードの管理
const editingTaskId = ref(null)
const editTaskTitle = ref("")
const editTaskStatus = ref(1)
const editTaskDueDate = ref("")

// エラーメッセージの定義
const newTaskErrors = ref({
  title: '',
  due_date: '',
});
const newEditTaskErrors = ref({
  title: '',
  due_date: '',
});

// ページネーションの状態
const tasksPage = ref(1)
const tasksLastPage = ref(1)
const tasksHasMorePages = ref(false)

// ステータスに応じてクラスを付与する関数
const getTaskStatusClass = (status) => {
  if (status === 1) return 'status-not-started';  // 未完了（赤色）
  if (status === 2) return 'status-in-progress';  // 進行中（緑色）
  if (status === 3) return 'status-completed';    // 完了（灰色）
  return '';
}

// GraphQLクライアントの設定
const graphqlClient = new GraphQLClient('http://localhost:8888/graphql')

const GET_FOLDER = gql`
  query GetFolder($id: ID!) {
    folder(id: $id) {
      id
      title
    }
  }
`

const GET_TASKS = gql`
  query GetTasks($folder_id: ID!, $page: Int!, $count: Int!) {
    tasks(folder_id: $folder_id, page: $page, count: $count) {
      data {
        id
        title
        status
        due_date
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



const CREATE_TASK = gql`
  mutation CreateTask($folder_id: ID!, $title: String!, $status: Int!, $due_date: Date!) {
    createTask(folder_id: $folder_id, title: $title, status: $status, due_date: $due_date) {
      id
      title
      status
      due_date
    }
  }
`
const UPDATE_TASK = gql`
  mutation UpdateTask($id: ID!, $title: String!, $status: Int!, $due_date: Date!) {
    updateTask(id: $id, title: $title, status: $status, due_date: $due_date) {
      id
      title
      status
      due_date
    }
  }
`
const DELETE_TASK = gql`
  mutation DeleteTask($id: ID!) {
    deleteTask(id: $id) {
      id
    }
  }
`

// 新規タスク追加時のバリデーション関数
const validateNewTask = () => {
  let isValid = true;

  // タイトルが空かチェック
  if (!newTaskTitle.value.trim()) {
    newTaskErrors.value.title = 'タスクタイトルは必須です。';
    isValid = false;
  } else {
    newTaskErrors.value.title = '';
  }

  // 期日が正しく入力されているかチェック
  if (!newTaskDueDate.value) {
    newTaskErrors.value.due_date = '期日は必須です。';
    isValid = false;
  } else if (new Date(newTaskDueDate.value) < new Date().setHours(0, 0, 0, 0)) {  // 今日より前の日付をチェック
    newTaskErrors.value.due_date = '期日は今日以降の日付を選択してください。';
    isValid = false;
  } else {
    newTaskErrors.value.due_date = '';
  }

  return isValid;
};

// 編集タスクのバリデーション関数
const validateNewEditTask = () => {
  let isValid = true;

  // タイトルが空かチェック
  if (!editTaskTitle.value.trim()) {  // 修正: newEditTaskTitle -> editTaskTitle
    newEditTaskErrors.value.title = 'タスクタイトルは必須です。';
    isValid = false;
  } else {
    newEditTaskErrors.value.title = '';
  }

  // 期日が正しく入力されているかチェック
  if (!editTaskDueDate.value) {  // 修正: newTaskDueDate -> editTaskDueDate
    newEditTaskErrors.value.due_date = '期日は必須です。';
    isValid = false;
  } else if (new Date(editTaskDueDate.value) < new Date().setHours(0, 0, 0, 0)) {  // 今日より前の日付をチェック
    newEditTaskErrors.value.due_date = '期日は今日以降の日付を選択してください。';
    isValid = false;
  } else {
    newEditTaskErrors.value.due_date = '';
  }

  return isValid;
};

// フォルダの取得関数
const fetchFolder = async () => {
  try {
    const variables = { id: folderId }
    const response = await graphqlClient.request(GET_FOLDER, variables)
    folder.value = response.folder
  } catch (error) {
    console.error('Error fetching folder:', error)
    alert('フォルダの取得に失敗しました。再度お試しください。')
  }
}

// タスクの取得関数
const fetchTasks = async () => {
  try {
    const variables = { folder_id: folderId, page: tasksPage.value, count: 5 }
    const response = await graphqlClient.request(GET_TASKS, variables)
    tasks.value = response.tasks.data
    tasksLastPage.value = response.tasks.paginatorInfo.lastPage
    tasksHasMorePages.value = response.tasks.paginatorInfo.hasMorePages
  } catch (error) {
    console.error('Error fetching tasks:', error)
    alert('タスクの取得に失敗しました。再度お試しください。')
  }
}

// フォルダとタスクの取得関数
const fetchFolderAndTasks = async () => {
  await fetchFolder()
  await fetchTasks()
}

// タスクを追加する関数
const addTask = async () => {
  if (!validateNewTask()) {
    return;
  }
  try {
    const variables = {
      folder_id: folderId,
      title: newTaskTitle.value,
      status: newTaskStatus.value,
      due_date: newTaskDueDate.value
    }
    const response = await graphqlClient.request(CREATE_TASK, variables)
    
    // 新しいタスクをタスクリストに追加
    tasks.value.push(response.createTask)

    // フォームのリセット
    newTaskTitle.value = ""
    newTaskStatus.value = 1
    newTaskDueDate.value = ""
  } catch (error) {
    console.error('Error adding task:', error)
    alert('タスクの追加に失敗しました。再度お試しください。')
  }
}

// タスクを更新する関数
const updateTask = async (taskId) => {
  if (!validateNewEditTask()) {
    return;
  }
  try {
    const variables = {
      id: taskId,
      title: editTaskTitle.value,
      status: parseInt(editTaskStatus.value),
      due_date: editTaskDueDate.value
    }
    const response = await graphqlClient.request(UPDATE_TASK, variables)

    // タスクリストの更新
    const index = tasks.value.findIndex(task => task.id === taskId)
    if (index !== -1) {
      tasks.value[index] = response.updateTask
    }

    // 編集モードを終了
    editingTaskId.value = null
  } catch (error) {
    console.error('Error updating task:', error)
    alert('タスクの編集に失敗しました。再度お試しください。')
  }
}

// タスクを削除する関数
const deleteTask = async (taskId) => {
  try {
    const variables = { id: taskId }
    await graphqlClient.request(DELETE_TASK, variables)
    
    // タスクリストから削除
    tasks.value = tasks.value.filter(task => task.id !== taskId)
  } catch (error) {
    console.error('Error deleting task:', error)
  }
}

// 編集モードに入る関数
const startEditingTask = (task) => {
  editingTaskId.value = task.id
  editTaskTitle.value = task.title
  editTaskStatus.value = task.status
  editTaskDueDate.value = task.due_date
}
// ページネーション操作関数
const nextTasksPage = async () => {
  if (tasksPage.value < tasksLastPage.value) {
    tasksPage.value += 1
    await fetchTasks()
  }
}

const prevTasksPage = async () => {
  if (tasksPage.value > 1) {
    tasksPage.value -= 1
    await fetchTasks()
  }
}

// コンポーネントがマウントされたときにデータを取得
onMounted(() => {
  fetchFolderAndTasks()
})
</script>

<template>
    <div class="folder-container">
      <!-- 新しいタスクを追加するフォーム -->
      <div class="task-form">
        <h3>新しいタスクを追加</h3>
        <input type="text" v-model="newTaskTitle" placeholder="タスクタイトル" />
        <span v-if="newTaskErrors.title" class="error">{{ newTaskErrors.title }}</span>
  
        <input type="date" v-model="newTaskDueDate" placeholder="期日" />
        <span v-if="newTaskErrors.due_date" class="error">{{ newTaskErrors.due_date }}</span>
  
        <select v-model="newTaskStatus">
          <option value="1">未着手</option>
          <option value="2">進行中</option>
          <option value="3">完了</option>
        </select>
        <button @click="addTask">タスクを追加</button>
      </div>
  
      <!-- タスク一覧 -->
      <div class="tasks">
        <h2 class="folder-title">フォルダ: {{ folder?.title }}</h2>
        <h3 class="task-title">タスク一覧</h3>
        <ul class="task-list">
          <li v-for="task in tasks" :key="task.id" class="task-item">
            <!-- 編集モード -->
            <div v-if="editingTaskId === task.id">
              <input type="text" v-model="editTaskTitle" />
              <span v-if="newEditTaskErrors.title" class="error">{{ newEditTaskErrors.title }}</span>
  
              <input type="date" v-model="editTaskDueDate" />
              <span v-if="newEditTaskErrors.due_date" class="error">{{ newEditTaskErrors.due_date }}</span>
  
              <select v-model="editTaskStatus">
                <option value="1">未着手</option>
                <option value="2">進行中</option>
                <option value="3">完了</option>
              </select>
              <span class="actions">
                <button @click="updateTask(task.id)">保存</button>
                <button @click="editingTaskId = null">戻る</button>
              </span>
            </div>
  
            <!-- 通常表示モード -->
            <div v-else>
              <div class="task-header">
                <span class="task-name">{{ task.title }}</span>
                <span class="task-status" :class="getTaskStatusClass(task.status)">
                  <!-- ステータスの値に応じた表示 -->
                  <span v-if="task.status === 1">未完了</span>
                  <span v-else-if="task.status === 2">進行中</span>
                  <span v-else-if="task.status === 3">完了</span>
                </span>
              </div>
              <div class="task-due">期日: {{ task.due_date }}</div>
              <span class="actions">
                <button @click.stop="startEditingTask(task)">編集</button>
                <button @click="deleteTask(task.id)">削除</button>
              </span>
            </div>
          </li>
        </ul>
        <div class="pagination">
        <button @click="prevTasksPage" :disabled="tasksPage === 1">前へ</button>
        <span>ページ {{ tasksPage }} / {{ tasksLastPage }}</span>
        <button @click="nextTasksPage" :disabled="!tasksHasMorePages">次へ</button>
      </div>
      </div>
    </div>
  </template>

<style scoped>
.error {
  color: red;
  font-size: 12px;
  margin-bottom: 10px;
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
.task-form {
  margin-top: 20px;
  margin-bottom: 40px;
  background-color: #e3ecee;
  padding: 10px;
  border-radius: 10px;
}

.task-form input, .task-form select {
  display: block;
  width: 80%;
  margin-bottom: 10px;
  padding: 10px;
  font-size: 16px;
}

.task-form button {
    background-color: transparent;
    background-color: #007bff; 
  color: white; 
  border: none;
  cursor: pointer;
  padding: 6px 10px; 
  border-radius: 4px; 
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); 
  transition: background-color 0.3s ease, transform 0.3s ease;
}

.task-form button:hover {
    background-color: #0056b3; 
    transform: translateY(-2px); 
}

.task-form button:active {
  background-color: #00408d; 
  transform: translateY(0);
}

.task-form button:focus {
  outline: none; 
  box-shadow: 0 0 0 3px rgba(186, 212, 240, 0.4); 
}
/* .tasks{
    padding: 2px;
} */

.folder-title {
  font-size: 24px;
  font-weight: bold;
  margin-bottom: 20px;
}

.task-title {
  font-size: 20px;
  font-weight: bold;
  margin-bottom: 10px;
}

.task-list {
  list-style-type: none;
  padding: 0;
}

.task-item {
  padding: 15px;
  margin-bottom: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  background-color: #fff;
}

.task-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 5px;
}

.task-name {
  font-weight: bold;
}

.task-status {
  font-style: italic;
  color: red;
}

.status-not-started {
  color: red;
}

.status-in-progress {
  color: green;
}

.status-completed {
  color: gray;
}
.task-due {
  color: #555;
}

.actions {
  display: flex;
  gap: 10px;
}

.actions button {
    background-color: transparent;
    background-color: #007bff; 
  color: white; 
  border: none;
  cursor: pointer;
  padding: 6px 10px; 
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

.pagination button:hover {
  background-color: #0056b3;
}

.pagination button:disabled {
  background-color: #cccccc;
  cursor: not-allowed;
}

.pagination span {
  font-weight: bold;
}

</style>