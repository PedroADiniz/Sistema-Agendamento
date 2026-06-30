import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath, URL } from 'node:url'

// Configuração do Vite. O servidor escuta em 0.0.0.0:5173 (acessível fora do container).
export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      // Permite imports do tipo "@/stores/auth" apontando para ./src.
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  server: {
    host: '0.0.0.0',
    port: 5173,
    // Necessário para hot-reload funcionar dentro do Docker em alguns ambientes.
    watch: {
      usePolling: true,
    },
  },
})
