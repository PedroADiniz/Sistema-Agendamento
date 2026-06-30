<script setup>
import { reactive, ref, computed, onMounted } from 'vue'
import { useAgendaStore } from '@/stores/agenda'
import { useToastStore } from '@/stores/toast'
import BaseModal from '@/components/BaseModal.vue'
import BaseButton from '@/components/BaseButton.vue'
import FormInput from '@/components/FormInput.vue'
import FormSelect from '@/components/FormSelect.vue'

// props que vêm da tela de disponibilidade
const props = defineProps({
  mode: { type: String, default: 'create' }, // 'create' ou 'edit'
  availability: { type: Object, default: null }, // disponibilidade sendo editada
  attendants: { type: Array, default: () => [] }, // lista de atendentes para o select
})
// avisos para a tela: fechar e salvou
const emit = defineEmits(['close', 'saved'])

const agenda = useAgendaStore()
const toast = useToastStore()
// true quando está editando
const isEdit = computed(() => props.mode === 'edit')
const loading = ref(false)

// opções do select de dia da semana
const weekdayOptions = [
  { value: 0, label: 'Domingo' },
  { value: 1, label: 'Segunda-feira' },
  { value: 2, label: 'Terça-feira' },
  { value: 3, label: 'Quarta-feira' },
  { value: 4, label: 'Quinta-feira' },
  { value: 5, label: 'Sexta-feira' },
  { value: 6, label: 'Sábado' },
]
// opções do select de "ativo?"
const activeOptions = [
  { value: '1', label: 'Sim' },
  { value: '0', label: 'Não' },
]

// dados do formulário
const form = reactive({
  user_id: '',
  weekday: '',
  start_time: '',
  end_time: '',
  active: '1',
})
// erros por campo (vindos do 422 da API)
const errors = reactive({ user_id: '', weekday: '', start_time: '', end_time: '', active: '' })

// ao abrir em modo edição, preenche o formulário
onMounted(() => {
  if (isEdit.value && props.availability) {
    form.user_id = props.availability.user_id
    form.weekday = props.availability.weekday
    form.start_time = props.availability.start_time
    form.end_time = props.availability.end_time
    form.active = props.availability.active ? '1' : '0'
  }
})

// limpa os erros antes de enviar
function clearErrors() {
  Object.keys(errors).forEach((k) => (errors[k] = ''))
}

// envia o formulário (criar ou editar)
async function submit() {
  clearErrors()
  loading.value = true
  // ajusta os tipos que a API espera (números e booleano)
  const payload = {
    user_id: Number(form.user_id),
    weekday: Number(form.weekday),
    start_time: form.start_time,
    end_time: form.end_time,
    active: form.active === '1',
  }
  try {
    if (isEdit.value) {
      await agenda.updateAvailability(props.availability.id, payload)
      toast.success('Disponibilidade atualizada.')
    } else {
      await agenda.createAvailability(payload)
      toast.success('Disponibilidade criada.')
    }
    emit('saved')
  } catch (e) {
    // mostra os erros por campo
    if (e.errors) {
      Object.keys(errors).forEach((k) => (errors[k] = e.errors[k]?.[0] || ''))
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <BaseModal :title="isEdit ? 'Editar disponibilidade' : 'Nova disponibilidade'" @close="emit('close')">
    <form @submit.prevent="submit">
      <!-- atendente não pode ser trocado na edição -->
      <FormSelect
        v-model="form.user_id"
        label="Atendente"
        :options="attendants"
        placeholder="Selecione o atendente"
        :error="errors.user_id"
        :disabled="isEdit"
      />
      <FormSelect
        v-model="form.weekday"
        label="Dia da Semana"
        :options="weekdayOptions"
        placeholder="Selecione o dia"
        :error="errors.weekday"
      />
      <FormInput v-model="form.start_time" label="Hora Inicial" type="time" required :error="errors.start_time" />
      <FormInput v-model="form.end_time" label="Hora Final" type="time" required :error="errors.end_time" />
      <FormSelect v-model="form.active" label="Ativo?" :options="activeOptions" required :error="errors.active" />

      <div class="form-actions">
        <BaseButton variant="ghost" type="button" @click="emit('close')">Cancelar</BaseButton>
        <BaseButton type="submit" :disabled="loading">
          {{ loading ? 'Salvando...' : 'Salvar' }}
        </BaseButton>
      </div>
    </form>
  </BaseModal>
</template>
