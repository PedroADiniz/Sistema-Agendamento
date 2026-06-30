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
const filters = reactive({ attendant_id: '', date: '' })

// vira true depois da primeira busca
const searched = ref(false)

// quando true, busca todos os slots (livres + ocupados)
const showAll = ref(false)

// lista de atendentes para o select
const attendants = computed(() =>
  usersStore.items
    .filter((u) => u.role === 'atendente')
    .map((u) => ({ value: u.id, label: u.name || u.email }))
)

// colunas da tabela (muda conforme o modo)
const columnsAvailable = [
  { key: 'start', label: 'Início' },
  { key: 'end', label: 'Fim' },
]
const columnsAll = [
  { key: 'start', label: 'Início' },
  { key: 'end', label: 'Fim' },
  { key: 'client_name', label: 'Cliente' },
]

// ao abrir a tela, carrega os atendentes
onMounted(() => usersStore.fetch())

// executa a busca de acordo com o modo atual
async function search() {
  if (!filters.attendant_id || !filters.date) {
    toast.error('Selecione o atendente e a data.')
    return
  }
  const id = Number(filters.attendant_id)
  if (showAll.value) {
    await agenda.fetchAllSlots(id, filters.date)
  } else {
    await agenda.fetchSlots(id, filters.date)
  }
  searched.value = true
}

// quando o toggle muda, refaz a busca se já tinha pesquisado
async function toggleMode() {
  showAll.value = !showAll.value
  if (searched.value && filters.attendant_id && filters.date) {
    await search()
  }
}

// linhas da tabela: varia conforme o modo
const rows = computed(() => showAll.value ? agenda.allSlots : agenda.slots)

// --- parte de agendar um horário ---
const showBook = ref(false)
const slotToBook = ref(null)
const booking = reactive({ client_name: '', client_email: '' })
const bookErrors = reactive({ client_name: '', start_time: '' })
const bookingLoading = ref(false)

function openBook(slot) {
  slotToBook.value = slot
  booking.client_name = ''
  booking.client_email = ''
  bookErrors.client_name = ''
  bookErrors.start_time = ''
  showBook.value = true
}

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
    // recarrega para o slot agendado sair da lista
    await search()
  } catch (e) {
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

    <!-- filtros: atendente + data + busca -->
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

    <!-- toggle: apenas livres ou todos -->
    <div v-if="searched" class="schedule-toggle">
      <label class="toggle-switch">
        <input type="checkbox" :checked="showAll" @change="toggleMode" />
        <span class="toggle-switch__track"></span>
        <span class="toggle-switch__label">Mostrar horários ocupados</span>
      </label>
    </div>

    <!-- tabela de horários -->
    <BaseTable
      v-if="searched"
      :columns="showAll ? columnsAll : columnsAvailable"
      :rows="rows"
      :loading="agenda.loadingSlots"
      empty-text="Nenhum horário disponível para este atendente nesta data."
      has-actions
    >
      <!-- coluna Cliente: mostra nome (ocupado) ou traço (livre) -->
      <template #cell-client_name="{ row }">
        <span v-if="row.status === 'booked'" class="client-info">
          <span class="badge badge--booked">Ocupado</span>
          {{ row.client_name }}
        </span>
        <span v-else class="muted">—</span>
      </template>

      <!-- ações: "Agendar" só para livres; ocupados mostram traço -->
      <template #actions="{ row }">
        <template v-if="row.status === 'booked' || !showAll">
          <BaseButton
            v-if="!showAll || row.status === 'available'"
            variant="ghost"
            small
            @click="openBook(row)"
          >
            Agendar
          </BaseButton>
          <span v-else class="muted" style="padding: 0 8px;">—</span>
        </template>
        <template v-else>
          <BaseButton variant="ghost" small @click="openBook(row)">Agendar</BaseButton>
        </template>
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
        />
        <p v-if="bookErrors.start_time" class="field__error" style="margin-top: -8px; margin-bottom: 12px;">
          {{ bookErrors.start_time }}
        </p>
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

/* toggle "mostrar ocupados" */
.schedule-toggle {
  margin-bottom: 16px;
}
.toggle-switch {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  user-select: none;
}
.toggle-switch input {
  display: none;
}
.toggle-switch__track {
  width: 38px;
  height: 22px;
  border-radius: 999px;
  background: #d1d5db;
  position: relative;
  flex-shrink: 0;
  transition: background 0.2s;
}
.toggle-switch__track::after {
  content: '';
  position: absolute;
  top: 3px;
  left: 3px;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background: #fff;
  transition: transform 0.2s;
  box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}
.toggle-switch input:checked + .toggle-switch__track {
  background: var(--accent);
}
.toggle-switch input:checked + .toggle-switch__track::after {
  transform: translateX(16px);
}
.toggle-switch__label {
  font-size: 14px;
  font-weight: 500;
  color: #374151;
}

/* info do cliente na célula */
.client-info {
  display: inline-flex;
  align-items: center;
  gap: 8px;
}
</style>
