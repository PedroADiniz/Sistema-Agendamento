import { defineStore } from 'pinia'

// contador para dar um id único a cada toast
let nextId = 1

// Store dos avisos (toasts) que aparecem no canto da tela.
export const useToastStore = defineStore('toast', {
  // lista de avisos na tela: cada um { id, type, message }
  state: () => ({
    items: [],
  }),
  actions: {
    // adiciona um aviso e agenda a remoção automática
    push(message, type = 'info', timeout = 4000) {
      const id = nextId++
      this.items.push({ id, type, message })
      // some sozinho depois do tempo
      setTimeout(() => this.dismiss(id), timeout)
    },
    // atalho para aviso verde de sucesso
    success(message) {
      this.push(message, 'success')
    },
    // atalho para aviso vermelho de erro
    error(message) {
      this.push(message, 'error')
    },
    // atalho para aviso neutro
    info(message) {
      this.push(message, 'info')
    },
    // remove um aviso da lista
    dismiss(id) {
      this.items = this.items.filter((t) => t.id !== id)
    },
  },
})
