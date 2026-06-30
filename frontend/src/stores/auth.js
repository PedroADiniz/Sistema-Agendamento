import { defineStore } from 'pinia'
import authService from '@/services/authService'
import { TOKEN_KEY } from '@/services/http'

// nome da chave onde guardamos o usuário no navegador
const USER_KEY = 'agendamento_user'

// Store de login: guarda o token e o usuário (com o perfil).
// É daqui que os guards de rota e os botões por perfil pegam a informação.
export const useAuthStore = defineStore('auth', {
  // estado inicial: tenta recuperar token e usuário do navegador
  state: () => ({
    token: localStorage.getItem(TOKEN_KEY) || null,
    user: JSON.parse(localStorage.getItem(USER_KEY) || 'null'),
  }),

  getters: {
    // true se tem token (está logado)
    isAuthenticated: (state) => !!state.token,
    // true se o usuário é admin
    isAdmin: (state) => state.user?.role === 'admin',
    // true se o usuário é atendente
    isAtendente: (state) => state.user?.role === 'atendente',
    // id do usuário logado
    currentUserId: (state) => state.user?.id ?? null,
  },

  actions: {
    // faz login e guarda a sessão
    async login(credentials) {
      const data = await authService.login(credentials)
      this.setSession(data.token, data.user)
      return data
    },

    // atualiza o usuário logado consultando a API
    async fetchMe() {
      const user = await authService.me()
      this.user = user
      localStorage.setItem(USER_KEY, JSON.stringify(user))
      return user
    },

    // faz logout (tenta avisar o backend e limpa a sessão)
    async logout() {
      try {
        if (this.token) await authService.logout()
      } catch (e) {
        // se der erro (token já expirado), ignora
      } finally {
        this.clearSession()
      }
    },

    // salva token + usuário no estado e no navegador
    setSession(token, user) {
      this.token = token
      this.user = user
      localStorage.setItem(TOKEN_KEY, token)
      localStorage.setItem(USER_KEY, JSON.stringify(user))
    },

    // limpa token + usuário do estado e do navegador
    clearSession() {
      this.token = null
      this.user = null
      localStorage.removeItem(TOKEN_KEY)
      localStorage.removeItem(USER_KEY)
    },
  },
})
