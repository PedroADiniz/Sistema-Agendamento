import http from './http'

// Chamadas da API de usuários
export default {
  // lista os usuários
  list() {
    return http.get('/users').then((r) => r.data.data)
  },
  // busca um usuário pelo id
  get(id) {
    return http.get(`/users/${id}`).then((r) => r.data.data)
  },
  // cria um usuário
  create(payload) {
    return http.post('/users', payload).then((r) => r.data.data)
  },
  // atualiza um usuário
  update(id, payload) {
    return http.put(`/users/${id}`, payload).then((r) => r.data.data)
  },
  // remove um usuário
  remove(id) {
    return http.delete(`/users/${id}`).then((r) => r.data)
  },
}
