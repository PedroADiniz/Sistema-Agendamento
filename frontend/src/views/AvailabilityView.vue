<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAgendaStore } from '@/stores/agenda'
import { useUsersStore } from '@/stores/users'
import { useToastStore } from '@/stores/toast'
import BaseButton from '@/components/BaseButton.vue'
import BaseModal from '@/components/BaseModal.vue'
import AvailabilityFormModal from '@/components/AvailabilityFormModal.vue'
import FormSelect from '@/components/FormSelect.vue'

const agenda = useAgendaStore()
const usersStore = useUsersStore()
const toast = useToastStore()

// filtro por atendente
const filterAttendant = ref('')

// controle do modal de criar/editar
const showForm = ref(false)
const formMode = ref('create')
const selected = ref(null)

// controle do modal de excluir
const showDelete = ref(false)
const toDelete = ref(null)

// lista de atendentes para o select do filtro e do formulário
const attendants = computed(() =>
  usersStore.items
    .filter((u) => u.role === 'atendente')
    .map((u) => ({ value: u.id, label: u.name || u.email }))
)

// agrupa as disponibilidades por atendente, aplicando o filtro selecionado
const grouped = computed(() => {
  let items = agenda.availabilities
  if (filterAttendant.value) {
    items = items.filter((a) => a.user_id === Number(filterAttendant.value))
  }
  const map = {}
  items.forEach((a) => {
    if (!map[a.user_id]) {
      map[a.user_id] = {
        userId: a.user_id,
        name: a.attendant?.name || `#${a.user_id}`,
        items: [],
      }
    }
    map[a.user_id].items.push(a)
  })
  return Object.values(map)
})

// ao abrir a tela, carrega disponibilidades e usuários ao mesmo tempo
onMounted(async () => {
  await Promise.all([agenda.fetchAvailabilities(), usersStore.fetch()])
})

function openCreate() {
  formMode.value = 'create'
  selected.value = null
  showForm.value = true
}
function openEdit(item) {
  formMode.value = 'edit'
  selected.value = item
  showForm.value = true
}
function onSaved() {
  showForm.value = false
}

function askDelete(item) {
  toDelete.value = item
  showDelete.value = true
}
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

    <!-- filtro por atendente -->
    <div class="filter-bar">
      <FormSelect
        v-model="filterAttendant"
        :options="attendants"
        placeholder="Todos os atendentes"
      />
    </div>

    <!-- tabela agrupada: cada grupo é um atendente -->
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>Dia</th>
            <th>Início</th>
            <th>Fim</th>
            <th>Ativo</th>
            <th class="table__actions-cell">Ações</th>
          </tr>
        </thead>

        <tbody v-if="agenda.loadingAvailabilities">
          <tr><td colspan="5" class="table__empty">Carregando...</td></tr>
        </tbody>

        <tbody v-else-if="!grouped.length">
          <tr><td colspan="5" class="table__empty">Nenhuma disponibilidade cadastrada.</td></tr>
        </tbody>

        <!-- um bloco <tbody> por atendente -->
        <template v-else v-for="group in grouped" :key="group.userId">
          <tbody>
            <!-- linha de cabeçalho do grupo -->
            <tr class="table__group-header">
              <td colspan="5">{{ group.name }}</td>
            </tr>
            <!-- linhas de horário do atendente -->
            <tr v-for="row in group.items" :key="row.id">
              <td>{{ row.weekday_label }}</td>
              <td>{{ row.start_time }}</td>
              <td>{{ row.end_time }}</td>
              <td>
                <span class="badge" :class="row.active ? 'badge--on' : 'badge--off'">
                  {{ row.active ? 'Sim' : 'Não' }}
                </span>
              </td>
              <td class="table__actions-cell">
                <div class="table__actions">
                  <BaseButton variant="ghost" small @click="openEdit(row)">Editar</BaseButton>
                  <BaseButton variant="danger" small @click="askDelete(row)">Excluir</BaseButton>
                </div>
              </td>
            </tr>
          </tbody>
        </template>
      </table>
    </div>

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
