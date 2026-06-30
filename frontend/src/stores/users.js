import { defineStore } from 'pinia'
import userService from '@/services/userService'

// Store dos usuários: guarda a lista e faz as operações de CRUD.
export const useUsersStore = defineStore('users', {
  // estado: a lista e um controle de "carregando"
  state: () => ({
    items: [],
    loading: false,
  }),

  actions: {
    // busca a lista de usuários na API
    async fetch() {
      this.loading = true
      try {
        this.items = await userService.list()
      } finally {
        this.loading = false
      }
    },

    // cria um usuário e recarrega a lista
    async create(payload) {
      const user = await userService.create(payload)
      await this.fetch()
      return user
    },

    // atualiza um usuário e recarrega a lista
    async update(id, payload) {
      const user = await userService.update(id, payload)
      await this.fetch()
      return user
    },

    // remove um usuário e tira ele da lista
    async remove(id) {
      await userService.remove(id)
      this.items = this.items.filter((u) => u.id !== id)
    },
  },
})
