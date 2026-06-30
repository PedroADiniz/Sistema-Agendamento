<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useToastStore } from '@/stores/toast'
import FormInput from '@/components/FormInput.vue'
import BaseButton from '@/components/BaseButton.vue'

const auth = useAuthStore()
const toast = useToastStore()
const router = useRouter()

// dados digitados e erros por campo
const form = reactive({ email: '', password: '' })
const errors = reactive({ email: '', password: '' })
const loading = ref(false)

// chamada quando clica em "Entrar"
async function submit() {
  // limpa os erros antigos
  errors.email = ''
  errors.password = ''
  loading.value = true
  try {
    // tenta logar
    await auth.login({ email: form.email, password: form.password })
    toast.success('Bem-vindo!')
    // deu certo: vai para a tela de usuários
    router.push({ name: 'users' })
  } catch (e) {
    // se a API devolveu erros por campo, mostra em cada input
    if (e.errors) {
      errors.email = e.errors.email?.[0] || ''
      errors.password = e.errors.password?.[0] || ''
    }
    // a mensagem geral de erro já aparece no toast (vem do http.js)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="login-wrap">
    <div class="card login-card">
      <div class="login-card__brand">
        <span class="login-card__logo">A</span>
        <span>Agendamentos</span>
      </div>
      <h1 class="login-card__title">Bem-vindo de volta</h1>
      <p class="muted login-card__subtitle">Entre com suas credenciais para continuar.</p>

      <form @submit.prevent="submit">
        <FormInput
          v-model="form.email"
          label="E-mail"
          type="email"
          required
          placeholder="admin@admin.com"
          :error="errors.email"
        />
        <FormInput
          v-model="form.password"
          label="Senha"
          type="password"
          required
          placeholder="••••••••"
          :error="errors.password"
        />
        <div class="form-actions">
          <BaseButton type="submit" :disabled="loading">
            {{ loading ? 'Entrando...' : 'Entrar' }}
          </BaseButton>
        </div>
      </form>
    </div>
  </div>
</template>
