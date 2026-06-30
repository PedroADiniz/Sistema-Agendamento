import { defineStore } from 'pinia'
import availabilityService from '@/services/availabilityService'
import scheduleService from '@/services/scheduleService'

// Store da agenda: cuida das disponibilidades e dos horários livres.
export const useAgendaStore = defineStore('agenda', {
  // estado: listas e controles de "carregando"
  state: () => ({
    availabilities: [],
    slots: [],       // apenas horários livres
    allSlots: [],    // livres + ocupados (com dados do cliente)
    loadingAvailabilities: false,
    loadingSlots: false,
  }),

  actions: {
    // busca as disponibilidades (de todos ou de um atendente)
    async fetchAvailabilities(userId = null) {
      this.loadingAvailabilities = true
      try {
        this.availabilities = await availabilityService.list(userId)
      } finally {
        this.loadingAvailabilities = false
      }
    },

    // cria uma disponibilidade e recarrega a lista
    async createAvailability(payload) {
      await availabilityService.create(payload)
      await this.fetchAvailabilities()
    },

    // atualiza uma disponibilidade e recarrega a lista
    async updateAvailability(id, payload) {
      await availabilityService.update(id, payload)
      await this.fetchAvailabilities()
    },

    // remove uma disponibilidade e tira ela da lista
    async removeAvailability(id) {
      await availabilityService.remove(id)
      this.availabilities = this.availabilities.filter((a) => a.id !== id)
    },

    // busca os horários livres de um atendente numa data
    async fetchSlots(attendantId, date) {
      this.loadingSlots = true
      try {
        const data = await scheduleService.available(attendantId, date)
        this.slots = data.slots
        return data
      } finally {
        this.loadingSlots = false
      }
    },

    // busca todos os slots do dia (livres + ocupados com nome do cliente)
    async fetchAllSlots(attendantId, date) {
      this.loadingSlots = true
      try {
        const data = await scheduleService.day(attendantId, date)
        this.allSlots = data.slots
      } finally {
        this.loadingSlots = false
      }
    },

    // cria um agendamento
    async book(payload) {
      return scheduleService.book(payload)
    },
  },
})
