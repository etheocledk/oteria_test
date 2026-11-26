<script setup>
const props = defineProps({
    value: { type: String, required: true },
    label: { type: String, required: true },
});

const classes = {
    open: 'bg-sky-100 text-sky-800',
    in_progress: 'bg-indigo-100 text-indigo-800',
    resolved: 'bg-emerald-100 text-emerald-800',
    closed: 'bg-slate-200 text-slate-600',
};
</script>

<template>
    <span class="inline-block rounded-full px-2 py-0.5 text-xs font-medium" :class="classes[props.value]">
        {{ props.label }}
    </span>
</template>
