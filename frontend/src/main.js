import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import './assets/styles.css'

// cria o app a partir do componente principal
const app = createApp(App)

// liga o Pinia (estado) e o Router (rotas)
app.use(createPinia())
app.use(router)

// coloca o app na página (no <div id="app">)
app.mount('#app')
