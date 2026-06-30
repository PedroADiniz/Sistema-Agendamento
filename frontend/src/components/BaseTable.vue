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
  <table class="table">
    <thead>
      <tr>
        <th v-for="col in columns" :key="col.key">{{ col.label }}</th>
        <th v-if="hasActions">Ações</th>
      </tr>
    </thead>
    <tbody>
      <tr v-if="loading">
        <td :colspan="columns.length + (hasActions ? 1 : 0)" class="muted">Carregando...</td>
      </tr>
      <tr v-else-if="!rows.length">
        <td :colspan="columns.length + (hasActions ? 1 : 0)" class="muted">{{ emptyText }}</td>
      </tr>
      <tr v-for="row in rows" v-else :key="row.id">
        <td v-for="col in columns" :key="col.key">
          <!-- Slot opcional por coluna; senão, mostra o valor cru -->
          <slot :name="`cell-${col.key}`" :row="row">{{ row[col.key] }}</slot>
        </td>
        <td v-if="hasActions">
          <slot name="actions" :row="row" />
        </td>
      </tr>
    </tbody>
  </table>
</template>
