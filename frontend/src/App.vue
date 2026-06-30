<script setup>
import { computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useToastStore } from '@/stores/toast'
import ToastContainer from '@/components/ToastContainer.vue'

const auth = useAuthStore()
const toast = useToastStore()
const router = useRouter()
const route = useRoute()

// Esconde o layout (sidebar) na tela de login.
const showShell = computed(() => auth.isAuthenticated && route.name !== 'login')

// Iniciais do usuário para o avatar.
const initials = computed(() => {
  const base = auth.user?.name || auth.user?.email || '?'
  return base.trim().slice(0, 2).toUpperCase()
})

async function handleLogout() {
  await auth.logout()
  toast.success('Sessão encerrada.')
  router.push({ name: 'login' })
}
</script>

<template>
  <div class="app" :class="{ 'app--shell': showShell }">
    <!-- Navegação lateral (apenas autenticado) -->
    <aside v-if="showShell" class="sidebar">
      <div class="sidebar__brand">
        <span class="sidebar__logo">A</span>
        <span>Agendamentos</span>
      </div>

      <nav class="sidebar__nav">
        <RouterLink to="/users">Usuários</RouterLink>
        <!-- Disponibilidade só aparece para admin -->
        <RouterLink v-if="auth.isAdmin" to="/availabilities">Disponibilidade</RouterLink>
        <RouterLink to="/schedule">Horários</RouterLink>
      </nav>

      <div class="sidebar__footer">
        <div class="sidebar__user">
          <span class="avatar">{{ initials }}</span>
          <div class="sidebar__user-info">
            <strong>{{ auth.user?.name || auth.user?.email }}</strong>
            <span>{{ auth.isAdmin ? 'Administrador' : 'Atendente' }}</span>
          </div>
        </div>
        <button class="btn btn--ghost btn--block" @click="handleLogout">Sair</button>
      </div>
    </aside>

    <!-- Área principal -->
    <main class="main">
      <RouterView />
    </main>

    <ToastContainer />
  </div>
</template>
