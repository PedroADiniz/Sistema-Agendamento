import http from './http'

// Chamadas da API de horários
export default {
  // busca os horários livres de um atendente numa data
  available(attendantId, date) {
    return http
      .get('/schedule/available', { params: { attendant_id: attendantId, date } })
      .then((r) => r.data.data)
  },
  // busca todos os slots do dia: livres + ocupados (com nome do cliente)
  day(attendantId, date) {
    return http
      .get('/schedule/day', { params: { attendant_id: attendantId, date } })
      .then((r) => r.data.data)
  },
  // cria um agendamento (reserva um horário)
  book(payload) {
    return http.post('/appointments', payload).then((r) => r.data.data)
  },
}
