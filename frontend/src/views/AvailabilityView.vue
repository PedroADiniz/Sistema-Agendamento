<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAgendaStore } from '@/stores/agenda'
import { useUsersStore } from '@/stores/users'
import { useToastStore } from '@/stores/toast'
import BaseTable from '@/components/BaseTable.vue'
import BaseButton from '@/components/BaseButton.vue'
import BaseModal from '@/components/BaseModal.vue'
import AvailabilityFormModal from '@/components/AvailabilityFormModal.vue'

const agenda = useAgendaStore()
const usersStore = useUsersStore()
const toast = useToastStore()

// colunas da tabela
const columns = [
  { key: 'attendant_name', label: 'Atendente' },
  { key: 'weekday_label', label: 'Dia' },
  { key: 'start_time', label: 'Início' },
  { key: 'end_time', label: 'Fim' },
  { key: 'active', label: 'Ativo' },
]

// controle do modal de criar/editar
const showForm = ref(false)
const formMode = ref('create')
const selected = ref(null)

// controle do modal de excluir
const showDelete = ref(false)
const toDelete = ref(null)

// lista de atendentes para o select (só quem é atendente)
const attendants = computed(() =>
  usersStore.items
    .filter((u) => u.role === 'atendente')
    .map((u) => ({ value: u.id, label: u.name || u.email }))
)

// adiciona o nome do atendente em cada linha da tabela
const rows = computed(() =>
  agenda.availabilities.map((a) => ({
    ...a,
    attendant_name: a.attendant?.name || `#${a.user_id}`,
  }))
)

// ao abrir a tela, carrega disponibilidades e usuários juntos
onMounted(async () => {
  await Promise.all([agenda.fetchAvailabilities(), usersStore.fetch()])
})

// abre o modal em modo criar
function openCreate() {
  formMode.value = 'create'
  selected.value = null
  showForm.value = true
}
// abre o modal em modo editar
function openEdit(item) {
  formMode.value = 'edit'
  selected.value = item
  showForm.value = true
}
// fecha o modal depois de salvar
function onSaved() {
  showForm.value = false
}

// abre a confirmação de exclusão
function askDelete(item) {
  toDelete.value = item
  showDelete.value = true
}
// confirma e exclui a disponibilidade
async function confirmDelete() {
  try {
    await agenda.removeAvailability(toDelete.value.id)
    toast.success('Disponibilidade excluída.')
  } finally {
    showDelete.value = false
    toDelete.value = null
  }
}
</script>

<template>
  <div class="card">
    <div class="page-header">
      <h1>Disponibilidade dos atendentes</h1>
      <BaseButton @click="openCreate">+ Nova disponibilidade</BaseButton>
    </div>

    <BaseTable :columns="columns" :rows="rows" :loading="agenda.loadingAvailabilities" has-actions>
      <!-- coluna "Ativo" como etiqueta verde/vermelha -->
      <template #cell-active="{ row }">
        <span class="badge" :class="row.active ? 'badge--on' : 'badge--off'">
          {{ row.active ? 'Sim' : 'Não' }}
        </span>
      </template>
      <!-- botões de ação por linha -->
      <template #actions="{ row }">
        <BaseButton variant="ghost" small @click="openEdit(row)">Editar</BaseButton>
        <BaseButton variant="danger" small @click="askDelete(row)">Excluir</BaseButton>
      </template>
    </BaseTable>

    <!-- modal de criar/editar disponibilidade -->
    <AvailabilityFormModal
      v-if="showForm"
      :mode="formMode"
      :availability="selected"
      :attendants="attendants"
      @close="showForm = false"
      @saved="onSaved"
    />

    <!-- modal de confirmação de exclusão -->
    <BaseModal v-if="showDelete" title="Confirmar exclusão" @close="showDelete = false">
      <p>Tem certeza que deseja excluir esta disponibilidade?</p>
      <div class="form-actions">
        <BaseButton variant="ghost" @click="showDelete = false">Cancelar</BaseButton>
        <BaseButton variant="danger" @click="confirmDelete">Excluir</BaseButton>
      </div>
    </BaseModal>
  </div>
</template>
