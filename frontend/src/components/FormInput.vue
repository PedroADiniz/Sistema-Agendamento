<script setup>
// Campo de input com label, marcação de obrigatório (*) e exibição de erro.
defineProps({
  modelValue: { type: [String, Number], default: '' },
  label: { type: String, default: '' },
  type: { type: String, default: 'text' },
  required: { type: Boolean, default: false },
  error: { type: String, default: '' },
  placeholder: { type: String, default: '' },
  disabled: { type: Boolean, default: false },
})
defineEmits(['update:modelValue'])
</script>

<template>
  <div class="field">
    <label v-if="label">
      {{ label }}
      <span v-if="required" class="required">*</span>
    </label>
    <input
      :type="type"
      :value="modelValue"
      :placeholder="placeholder"
      :disabled="disabled"
      :class="{ 'is-invalid': !!error }"
      @input="$emit('update:modelValue', $event.target.value)"
    />
    <div v-if="error" class="error">{{ error }}</div>
  </div>
</template>
