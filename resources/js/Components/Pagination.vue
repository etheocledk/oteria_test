<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    meta: { type: Object, required: true },
});

/**
 * Laravel pagination labels contain HTML entities; decode the two known ones
 * instead of rendering them with v-html.
 */
const labelText = (label) =>
    label.replace('&laquo;', '«').replace('&raquo;', '»').replace('Previous', 'Précédent').replace('Next', 'Suivant');
</script>

<template>
    <nav v-if="meta.last_page > 1" class="mt-4 flex flex-wrap gap-1">
        <template v-for="link in meta.links" :key="link.label">
            <Link
                v-if="link.url"
                :href="link.url"
                preserve-scroll
                class="rounded border px-3 py-1 text-sm"
                :class="link.active ? 'border-indigo-600 bg-indigo-600 text-white' : 'bg-white hover:bg-slate-100'"
            >
                {{ labelText(link.label) }}
            </Link>
            <span v-else class="rounded border px-3 py-1 text-sm text-slate-400">{{ labelText(link.label) }}</span>
        </template>
    </nav>
</template>
