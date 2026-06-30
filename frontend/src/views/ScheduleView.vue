<script setup>
import { reactive, ref, computed, onMounted } from 'vue'
import { useAgendaStore } from '@/stores/agenda'
import { useUsersStore } from '@/stores/users'
import { useToastStore } from '@/stores/toast'
import BaseTable from '@/components/BaseTable.vue'
import BaseButton from '@/components/BaseButton.vue'
import BaseModal from '@/components/BaseModal.vue'
import FormSelect from '@/components/FormSelect.vue'
import FormInput from '@/components/FormInput.vue'

const agenda = useAgendaStore()
const usersStore = useUsersStore()
const toast = useToastStore()

// filtros da busca: atendente + data
const filters = reactive({
  attendant_id: '',
  date: '',
})

// vira true depois da primeira busca (para mostrar a tabela)
const searched = ref(false)

// lista de atendentes para o select
const attendants = computed(() =>
  usersStore.items
    .filter((u) => u.role === 'atendente')
    .map((u) => ({ value: u.id, label: u.name || u.email }))
)

// colunas da tabela de horários
const columns = [
  { key: 'start', label: 'Início' },
  { key: 'end', label: 'Fim' },
]

// ao abrir a tela, carrega os atendentes
onMounted(() => usersStore.fetch())

// busca os horários livres
async function search() {
  // precisa escolher atendente e data
  if (!filters.attendant_id || !filters.date) {
    toast.error('Selecione o atendente e a data.')
    return
  }
  await agenda.fetchSlots(Number(filters.attendant_id), filters.date)
  searched.value = true
}

// --- parte de agendar um horário ---
const showBook = ref(false)
const slotToBook = ref(null)
const booking = reactive({ client_name: '', client_email: '' })
const bookErrors = reactive({ client_name: '', start_time: '' })
const bookingLoading = ref(false)

// abre o modal de agendamento para um horário
function openBook(slot) {
  slotToBook.value = slot
  booking.client_name = ''
  booking.client_email = ''
  bookErrors.client_name = ''
  bookErrors.start_time = ''
  showBook.value = true
}

// confirma o agendamento
async function confirmBook() {
  bookingLoading.value = true
  bookErrors.client_name = ''
  bookErrors.start_time = ''
  try {
    await agenda.book({
      user_id: Number(filters.attendant_id),
      scheduled_date: filters.date,
      start_time: slotToBook.value.start,
      client_name: booking.client_name,
      client_email: booking.client_email || null,
    })
    toast.success('Agendamento criado com sucesso.')
    showBook.value = false
    // recarrega os horários: o que foi agendado some da lista
    await agenda.fetchSlots(Number(filters.attendant_id), filters.date)
  } catch (e) {
    // mostra os erros por campo
    if (e.errors) {
      bookErrors.client_name = e.errors.client_name?.[0] || ''
      bookErrors.start_time = e.errors.start_time?.[0] || ''
    }
  } finally {
    bookingLoading.value = false
  }
}
</script>

<template>
  <div class="card">
    <div class="page-header">
      <h1>Consulta de horários disponíveis</h1>
    </div>

    <!-- filtros: atendente + data -->
    <div class="filters">
      <FormSelect
        v-model="filters.attendant_id"
        label="Atendente"
        :options="attendants"
        placeholder="Selecione o atendente"
        required
      />
      <FormInput v-model="filters.date" label="Data" type="date" required />
      <div class="filters__action">
        <BaseButton @click="search" :disabled="agenda.loadingSlots">
          {{ agenda.loadingSlots ? 'Buscando...' : 'Buscar horários' }}
        </BaseButton>
      </div>
    </div>

    <!-- resultado: horários livres de 60 min -->
    <BaseTable
      v-if="searched"
      :columns="columns"
      :rows="agenda.slots"
      :loading="agenda.loadingSlots"
      empty-text="Nenhum horário disponível para este atendente nesta data."
      has-actions
    >
      <template #actions="{ row }">
        <BaseButton variant="ghost" small @click="openBook(row)">Agendar</BaseButton>
      </template>
    </BaseTable>

    <!-- modal para agendar um horário -->
    <BaseModal v-if="showBook" title="Agendar horário" @close="showBook = false">
      <p>
        Horário selecionado:
        <strong>{{ slotToBook?.start }} – {{ slotToBook?.end }}</strong>
      </p>
      <form @submit.prevent="confirmBook">
        <FormInput
          v-model="booking.client_name"
          label="Nome do cliente"
          required
          :error="bookErrors.client_name"
        />
        <FormInput
          v-model="booking.client_email"
          label="E-mail do cliente"
          type="email"
          :error="bookErrors.start_time"
        />
        <div class="form-actions">
          <BaseButton variant="ghost" type="button" @click="showBook = false">Cancelar</BaseButton>
          <BaseButton type="submit" :disabled="bookingLoading">
            {{ bookingLoading ? 'Agendando...' : 'Confirmar agendamento' }}
          </BaseButton>
        </div>
      </form>
    </BaseModal>
  </div>
</template>

<style scoped>
/* deixa os filtros lado a lado */
.filters {
  display: flex;
  gap: 1rem;
  align-items: flex-end;
  flex-wrap: wrap;
  margin-bottom: 1.5rem;
}
.filters .field {
  margin-bottom: 0;
  min-width: 220px;
}
.filters__action {
  padding-bottom: 2px;
}
</style>
