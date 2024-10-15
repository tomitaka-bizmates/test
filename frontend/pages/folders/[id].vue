<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { gql, GraphQLClient } from 'graphql-request'
const route =useRoute();
console.log(route.name)
const folderId = route.params.id

const folder = ref(null)
const tasks = ref([])


const graphqlClient = new GraphQLClient('http://localhost:8888/graphql')

// GraphQLクエリを定義
// GraphQLクエリ
const GET_FOLDER_AND_TASKS = gql`
  query GetFolderAndTasks($id: ID!) {
    folder(id: $id) {
      id
      title
      tasks {
        id
        title
        status
        due_date
      }
    }
  }
`

// フォルダとそのタスクを取得する関数
const fetchFolderAndTasks = async () => {
  try {
    const variables = { id: folderId }
    const response = await graphqlClient.request(GET_FOLDER_AND_TASKS, variables)
    folder.value = response.folder
    tasks.value = response.folder.tasks
  } catch (error) {
    console.error('Error fetching folder and tasks:', error)
  }
}

onMounted(() => {
  fetchFolderAndTasks() // コンポーネントがマウントされたときにデータを取得
})
</script>

<template>
    <div class="folder-container">
      <h2 class="folder-title">フォルダ: {{ folder?.title }}</h2>
      
      <h3 class="task-title">タスク一覧</h3>
      <ul class="task-list">
        <li v-for="task in tasks" :key="task.id" class="task-item">
          <div class="task-header">
            <span class="task-name">{{ task.title }}</span>
            <span class="task-status">ステータス: {{ task.status }}</span>
          </div>
          <div class="task-due">期日: {{ task.due_date }}</div>
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
  color: #007bff;
}

.task-due {
  color: #555;
}
</style>