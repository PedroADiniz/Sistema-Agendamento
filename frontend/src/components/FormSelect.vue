<script setup>
// Select com label, marcação de obrigatório (*) e exibição de erro.
// options: [{ value, label }]
defineProps({
  modelValue: { type: [String, Number, Boolean, null], default: '' },
  label: { type: String, default: '' },
  options: { type: Array, default: () => [] },
  required: { type: Boolean, default: false },
  error: { type: String, default: '' },
  disabled: { type: Boolean, default: false },
  placeholder: { type: String, default: '' },
})
defineEmits(['update:modelValue'])
</script>

<template>
  <div class="field">
    <label v-if="label">
      {{ label }}
      <span v-if="required" class="required">*</span>
    </label>
    <select
      :value="modelValue"
      :disabled="disabled"
      :class="{ 'is-invalid': !!error }"
      @change="$emit('update:modelValue', $event.target.value)"
    >
      <option v-if="placeholder" value="">{{ placeholder }}</option>
      <option v-for="opt in options" :key="String(opt.value)" :value="opt.value">
        {{ opt.label }}
      </option>
    </select>
    <div v-if="error" class="error">{{ error }}</div>
  </div>
</template>
