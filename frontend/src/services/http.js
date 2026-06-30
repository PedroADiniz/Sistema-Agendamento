import axios from 'axios'
import { useToastStore } from '@/stores/toast'

// nome da chave onde guardamos o token no navegador
export const TOKEN_KEY = 'agendamento_token'

// cria o axios que todas as telas vão usar para falar com a API
const http = axios.create({
  // endereço da API (vem do docker; se não vier, usa localhost)
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
})

// antes de cada requisição: se tiver token, manda ele no cabeçalho
http.interceptors.request.use((config) => {
  // pega o token salvo
  const token = localStorage.getItem(TOKEN_KEY)
  // se existir, adiciona no header Authorization
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// depois de cada resposta: trata os erros num lugar só
http.interceptors.response.use(
  // se deu certo, só repassa a resposta
  (response) => response,
  // se deu erro, cai aqui
  (error) => {
    const toast = useToastStore()

    // sem resposta = servidor caiu ou sem internet
    if (!error.response) {
      toast.error('Não foi possível conectar ao servidor. Tente novamente.')
      return Promise.reject({ status: 0, message: 'Falha de conexão.', errors: {} })
    }

    // pega o status e a mensagem que a API mandou
    const { status, data } = error.response
    const message = data?.message || 'Ocorreu um erro inesperado.'
    const errors = data?.errors || {}

    if (status === 401) {
      // 401 = não autenticado. checa se o erro veio da própria tela de login
      const isLoginRequest = error.config?.url?.includes('/login')
      // apaga o token (sessão acabou)
      localStorage.removeItem(TOKEN_KEY)

      // se não está no login, avisa e manda para o login
      if (!isLoginRequest && window.location.pathname !== '/login') {
        toast.error('Sua sessão expirou. Faça login novamente.')
        window.location.assign('/login')
      } else {
        toast.error(message)
      }
    } else if (status === 422) {
      // 422 = erro de validação; o formulário mostra campo a campo, aqui só um aviso
      toast.error(message)
    } else {
      // outros erros (403, 404, 500...) mostram um aviso
      toast.error(message)
    }

    // repassa o erro para quem chamou (com status, mensagem e erros por campo)
    return Promise.reject({ status, message, errors })
  }
)

export default http
