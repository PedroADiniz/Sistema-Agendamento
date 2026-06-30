import http from './http'

// Chamadas de autenticação. Cada função devolve o "data" da resposta.
export default {
  // faz login e devolve token + usuário
  login(credentials) {
    return http.post('/login', credentials).then((r) => r.data.data)
  },
  // pega o usuário logado
  me() {
    return http.get('/me').then((r) => r.data.data)
  },
  // faz logout
  logout() {
    return http.post('/logout').then((r) => r.data)
  },
  // renova o token
  refresh() {
    return http.post('/refresh').then((r) => r.data.data)
  },
}
