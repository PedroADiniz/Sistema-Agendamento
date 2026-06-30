import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useToastStore } from '@/stores/toast'

import LoginView from '@/views/LoginView.vue'
import UsersView from '@/views/UsersView.vue'
import AvailabilityView from '@/views/AvailabilityView.vue'
import ScheduleView from '@/views/ScheduleView.vue'

// lista de rotas da aplicação
const routes = [
  {
    // tela de login (só para quem não está logado)
    path: '/login',
    name: 'login',
    component: LoginView,
    meta: { guestOnly: true },
  },
  {
    // ao abrir a raiz, manda para usuários
    path: '/',
    redirect: '/users',
  },
  {
    // lista de usuários (precisa estar logado)
    path: '/users',
    name: 'users',
    component: UsersView,
    meta: { requiresAuth: true },
  },
  {
    // disponibilidade (precisa estar logado E ser admin)
    path: '/availabilities',
    name: 'availabilities',
    component: AvailabilityView,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    // consulta de horários (precisa estar logado)
    path: '/schedule',
    name: 'schedule',
    component: ScheduleView,
    meta: { requiresAuth: true },
  },
  {
    // qualquer rota desconhecida volta para usuários
    path: '/:pathMatch(.*)*',
    redirect: '/users',
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// roda antes de cada troca de rota (as "travas" de acesso)
router.beforeEach((to) => {
  const auth = useAuthStore()

  // rota exige login e não está logado -> manda para o login
  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { name: 'login' }
  }

  // rota é só de admin e o usuário não é admin -> avisa e volta para usuários
  if (to.meta.requiresAdmin && !auth.isAdmin) {
    const toast = useToastStore()
    toast.error('Acesso restrito a administradores.')
    return { name: 'users' }
  }

  // já está logado e tentou abrir o login -> manda para usuários
  if (to.meta.guestOnly && auth.isAuthenticated) {
    return { name: 'users' }
  }

  // pode seguir
  return true
})

export default router
