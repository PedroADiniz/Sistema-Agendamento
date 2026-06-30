<script setup>
import { ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useToastStore } from '@/stores/toast'
import ToastContainer from '@/components/ToastContainer.vue'

const auth = useAuthStore()
const toast = useToastStore()
const router = useRouter()
const route = useRoute()

// controla se a sidebar está retraída
const collapsed = ref(false)

// esconde o layout (sidebar) na tela de login
const showShell = computed(() => auth.isAuthenticated && route.name !== 'login')

// iniciais do usuário para o avatar
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
    <aside v-if="showShell" class="sidebar" :class="{ 'sidebar--collapsed': collapsed }">

      <!-- topo: logo + botão de retrair -->
      <div class="sidebar__top">
        <div class="sidebar__brand">
          <span class="sidebar__logo">A</span>
          <span class="sidebar__brand-name">AgendaFlow</span>
        </div>
        <button class="sidebar__toggle" @click="collapsed = !collapsed" :title="collapsed ? 'Expandir menu' : 'Retrair menu'">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="15 18 9 12 15 6"/>
          </svg>
        </button>
      </div>

      <!-- navegação -->
      <nav class="sidebar__nav">
        <RouterLink to="/users">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          <span class="sidebar__nav-label">Usuários</span>
        </RouterLink>
        <RouterLink v-if="auth.isAdmin" to="/availabilities">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          <span class="sidebar__nav-label">Disponibilidade</span>
        </RouterLink>
        <RouterLink to="/schedule">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          <span class="sidebar__nav-label">Horários</span>
        </RouterLink>
      </nav>

      <!-- rodapé: usuário + sair -->
      <div class="sidebar__footer">
        <div class="sidebar__user">
          <span class="avatar">{{ initials }}</span>
          <div class="sidebar__user-info">
            <strong>{{ auth.user?.name || auth.user?.email }}</strong>
            <span>{{ auth.isAdmin ? 'Administrador' : 'Atendente' }}</span>
          </div>
        </div>
        <button class="sidebar__logout" @click="handleLogout">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          <span class="sidebar__nav-label">Sair</span>
        </button>
      </div>
    </aside>

    <!-- área principal (sem padding no login) -->
    <main v-if="showShell" class="main">
      <RouterView />
    </main>
    <RouterView v-else />

    <ToastContainer />
  </div>
</template>
