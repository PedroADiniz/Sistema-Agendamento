<script setup>
import { ref, onMounted } from 'vue'
import { useUsersStore } from '@/stores/users'
import { useAuthStore } from '@/stores/auth'
import { useToastStore } from '@/stores/toast'
import BaseTable from '@/components/BaseTable.vue'
import BaseButton from '@/components/BaseButton.vue'
import BaseModal from '@/components/BaseModal.vue'
import UserFormModal from '@/components/UserFormModal.vue'

const usersStore = useUsersStore()
const auth = useAuthStore()
const toast = useToastStore()

// colunas que a tabela vai mostrar
const columns = [
  { key: 'name', label: 'Nome' },
  { key: 'email', label: 'E-mail' },
  { key: 'role', label: 'Tipo' },
]

// controle do modal de criar/editar
const showForm = ref(false)
const formMode = ref('create')
const selectedUser = ref(null)

// controle do modal de confirmar exclusão
const showDelete = ref(false)
const userToDelete = ref(null)

// ao abrir a tela, carrega a lista
onMounted(() => usersStore.fetch())

// pode editar? admin edita qualquer um; atendente edita só ele mesmo
function canEdit(user) {
  return auth.isAdmin || auth.currentUserId === user.id
}
// pode excluir? só admin, e não a si mesmo
function canDelete(user) {
  return auth.isAdmin && auth.currentUserId !== user.id
}

// abre o modal em modo criar
function openCreate() {
  formMode.value = 'create'
  selectedUser.value = null
  showForm.value = true
}
// abre o modal em modo editar
function openEdit(user) {
  formMode.value = 'edit'
  selectedUser.value = user
  showForm.value = true
}
// fecha o modal depois de salvar
function onSaved() {
  showForm.value = false
}

// abre a confirmação de exclusão
function askDelete(user) {
  userToDelete.value = user
  showDelete.value = true
}
// confirma e exclui o usuário
async function confirmDelete() {
  try {
    await usersStore.remove(userToDelete.value.id)
    toast.success('Usuário excluído com sucesso.')
  } finally {
    showDelete.value = false
    userToDelete.value = null
  }
}
</script>

<template>
  <div class="card">
    <div class="page-header">
      <h1>Usuários</h1>
      <!-- botão de novo usuário: só admin vê -->
      <BaseButton v-if="auth.isAdmin" @click="openCreate">+ Novo usuário</BaseButton>
    </div>

    <BaseTable
      :columns="columns"
      :rows="usersStore.items"
      :loading="usersStore.loading"
      has-actions
    >
      <!-- coluna "Tipo" mostrada como etiqueta colorida -->
      <template #cell-role="{ row }">
        <span class="badge" :class="row.role === 'admin' ? 'badge--admin' : 'badge--atendente'">
          {{ row.role_label }}
        </span>
      </template>

      <!-- botões de ação em cada linha -->
      <template #actions="{ row }">
        <BaseButton v-if="canEdit(row)" variant="ghost" small @click="openEdit(row)">
          Editar
        </BaseButton>
        <BaseButton v-if="canDelete(row)" variant="danger" small @click="askDelete(row)">
          Excluir
        </BaseButton>
      </template>
    </BaseTable>

    <!-- modal de criar/editar usuário -->
    <UserFormModal
      v-if="showForm"
      :mode="formMode"
      :user="selectedUser"
      :can-edit-role="auth.isAdmin"
      @close="showForm = false"
      @saved="onSaved"
    />

    <!-- modal de confirmação de exclusão -->
    <BaseModal v-if="showDelete" title="Confirmar exclusão" @close="showDelete = false">
      <p>
        Tem certeza que deseja excluir o usuário
        <strong>{{ userToDelete?.name || userToDelete?.email }}</strong>?
      </p>
      <div class="form-actions">
        <BaseButton variant="ghost" @click="showDelete = false">Cancelar</BaseButton>
        <BaseButton variant="danger" @click="confirmDelete">Excluir</BaseButton>
      </div>
    </BaseModal>
  </div>
</template>
