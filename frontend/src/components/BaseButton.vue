<script setup>
// Botão reutilizável. variant: primary | danger | ghost.
defineProps({
  variant: { type: String, default: 'primary' },
  type: { type: String, default: 'button' },
  disabled: { type: Boolean, default: false },
  small: { type: Boolean, default: false },
})
</script>

<template>
  <button
    :type="type"
    :disabled="disabled"
    class="btn"
    :class="[
      variant === 'danger' && 'btn--danger',
      variant === 'ghost' && 'btn--ghost',
      small && 'btn--sm',
    ]"
  >
    <slot />
  </button>
</template>
