<script setup>
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const message = ref(null);

watch(
    () => page.flash,
    (flash) => {
        message.value = flash?.success ?? null;
    },
    { immediate: true },
);
</script>

<template>
    <div
        v-if="message"
        class="mb-6 flex items-center justify-between rounded border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
    >
        <span>{{ message }}</span>
        <button type="button" class="text-emerald-600 hover:text-emerald-900" @click="message = null">✕</button>
    </div>
</template>
