<script setup>
// Tabela reutilizável.
// columns: [{ key, label }]; rows: array de objetos.
// Slot "cell-<key>" permite customizar a célula; slot "actions" para ações por linha.
defineProps({
  columns: { type: Array, required: true },
  rows: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
  emptyText: { type: String, default: 'Nenhum registro encontrado.' },
  hasActions: { type: Boolean, default: false },
})
</script>

<template>
  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th v-for="col in columns" :key="col.key">{{ col.label }}</th>
          <th v-if="hasActions" class="table__actions-cell">Ações</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="loading">
          <td :colspan="columns.length + (hasActions ? 1 : 0)" class="table__empty">Carregando...</td>
        </tr>
        <tr v-else-if="!rows.length">
          <td :colspan="columns.length + (hasActions ? 1 : 0)" class="table__empty">{{ emptyText }}</td>
        </tr>
        <tr v-for="row in rows" v-else :key="row.id">
          <td v-for="col in columns" :key="col.key">
            <slot :name="`cell-${col.key}`" :row="row">{{ row[col.key] }}</slot>
          </td>
          <td v-if="hasActions" class="table__actions-cell">
            <div class="table__actions">
              <slot name="actions" :row="row" />
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
