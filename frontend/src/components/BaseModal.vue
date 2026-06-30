<script setup>
// Modal reutilizável (usado na confirmação de exclusão e nos formulários).
defineProps({
  title: { type: String, default: '' },
})
defineEmits(['close'])
</script>

<template>
  <div class="modal-backdrop" @click.self="$emit('close')">
    <div class="modal">
      <h3 v-if="title" class="modal__title">{{ title }}</h3>
      <slot />
    </div>
  </div>
</template>
