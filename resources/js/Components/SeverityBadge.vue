<script setup>
const props = defineProps({
    value: { type: String, required: true },
    label: { type: String, required: true },
});

const classes = {
    low: 'bg-slate-100 text-slate-700',
    medium: 'bg-amber-100 text-amber-800',
    high: 'bg-orange-100 text-orange-800',
    critical: 'bg-red-600 text-white',
};
</script>

<template>
    <span class="inline-block rounded px-2 py-0.5 text-xs font-semibold" :class="classes[props.value]">
        {{ props.label }}
    </span>
</template>
