<script setup>
import { reactive, ref, computed, onMounted } from 'vue'
import { useUsersStore } from '@/stores/users'
import { useToastStore } from '@/stores/toast'
import BaseModal from '@/components/BaseModal.vue'
import BaseButton from '@/components/BaseButton.vue'
import FormInput from '@/components/FormInput.vue'
import FormSelect from '@/components/FormSelect.vue'

// props que vêm da tela de usuários
const props = defineProps({
  mode: { type: String, default: 'create' }, // 'create' = criar, 'edit' = editar
  user: { type: Object, default: null },      // usuário sendo editado
  canEditRole: { type: Boolean, default: false }, // só admin muda o tipo
})
// avisos que o modal manda para a tela: fechar e salvou
const emit = defineEmits(['close', 'saved'])

const usersStore = useUsersStore()
const toast = useToastStore()

// true quando está no modo edição
const isEdit = computed(() => props.mode === 'edit')
const loading = ref(false)

// opções do select de tipo de usuário
const roleOptions = [
  { value: 'atendente', label: 'Atendente' },
  { value: 'admin', label: 'Administrador' },
]

// dados do formulário
const form = reactive({
  name: '',
  role: 'atendente',
  email: '',
  password: '',
  password_confirmation: '',
})

// erros por campo (preenchidos quando a API devolve 422)
const errors = reactive({
  name: '', role: '', email: '', password: '', password_confirmation: '',
})

// ao abrir em modo edição, preenche o formulário com os dados do usuário
onMounted(() => {
  if (isEdit.value && props.user) {
    form.name = props.user.name || ''
    form.role = props.user.role || 'atendente'
    form.email = props.user.email || ''
  }
})

// limpa os erros antes de enviar de novo
function clearErrors() {
  Object.keys(errors).forEach((k) => (errors[k] = ''))
}

// envia o formulário (criar ou editar)
async function submit() {
  clearErrors()
  loading.value = true
  try {
    if (isEdit.value) {
      // na edição não mandamos e-mail nem senha (não podem mudar)
      const payload = { name: form.name }
      if (props.canEditRole) payload.role = form.role
      await usersStore.update(props.user.id, payload)
      toast.success('Usuário atualizado com sucesso.')
    } else {
      // na criação mandamos todos os campos
      await usersStore.create({
        name: form.name,
        role: form.role,
        email: form.email,
        password: form.password,
        password_confirmation: form.password_confirmation,
      })
      toast.success('Usuário criado com sucesso.')
    }
    // avisa a tela que salvou
    emit('saved')
  } catch (e) {
    // se a API devolveu erros por campo, mostra cada um no seu input
    if (e.errors) {
      Object.keys(errors).forEach((k) => {
        errors[k] = e.errors[k]?.[0] || ''
      })
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <BaseModal :title="isEdit ? 'Editar usuário' : 'Novo usuário'" @close="emit('close')">
    <form @submit.prevent="submit">
      <FormInput v-model="form.name" label="Nome" :error="errors.name" />

      <FormSelect
        v-model="form.role"
        label="Tipo de Usuário"
        :options="roleOptions"
        :error="errors.role"
        :disabled="isEdit && !canEditRole"
      />

      <!-- e-mail e senha aparecem só ao criar -->
      <template v-if="!isEdit">
        <FormInput
          v-model="form.email"
          label="E-mail"
          type="email"
          required
          :error="errors.email"
        />
        <FormInput
          v-model="form.password"
          label="Senha"
          type="password"
          required
          :error="errors.password"
        />
        <FormInput
          v-model="form.password_confirmation"
          label="Confirme a Senha"
          type="password"
          required
          :error="errors.password_confirmation"
        />
      </template>

      <!-- na edição mostra o e-mail só para leitura -->
      <FormInput v-if="isEdit" :model-value="form.email" label="E-mail (não editável)" disabled />

      <div class="form-actions">
        <BaseButton variant="ghost" type="button" @click="emit('close')">Cancelar</BaseButton>
        <BaseButton type="submit" :disabled="loading">
          {{ loading ? 'Salvando...' : 'Salvar' }}
        </BaseButton>
      </div>
    </form>
  </BaseModal>
</template>
