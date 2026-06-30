import http from './http'

// Chamadas da API de disponibilidades
export default {
  // lista as disponibilidades (se passar userId, filtra por atendente)
  list(userId = null) {
    const params = userId ? { user_id: userId } : {}
    return http.get('/availabilities', { params }).then((r) => r.data.data)
  },
  // cria uma disponibilidade
  create(payload) {
    return http.post('/availabilities', payload).then((r) => r.data.data)
  },
  // atualiza uma disponibilidade
  update(id, payload) {
    return http.put(`/availabilities/${id}`, payload).then((r) => r.data.data)
  },
  // remove uma disponibilidade
  remove(id) {
    return http.delete(`/availabilities/${id}`).then((r) => r.data)
  },
}
