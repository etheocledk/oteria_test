<script setup>
defineProps({
    label: { type: String, required: true },
    id: { type: String, required: true },
    error: { type: String, default: null },
});
</script>

<template>
    <div>
        <label :for="id" class="mb-1 block text-sm font-medium text-slate-700">{{ label }}</label>
        <slot />
        <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
    </div>
</template>
