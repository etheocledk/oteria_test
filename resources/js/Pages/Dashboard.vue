<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import SeverityBadge from '@/Components/SeverityBadge.vue';
import StatusBadge from '@/Components/StatusBadge.vue';

const props = defineProps({
    severityCounts: { type: Array, required: true },
    statusCounts: { type: Array, required: true },
    myOpenCount: { type: Number, required: true },
    unassignedCount: { type: Number, required: true },
    recentIncidents: { type: Array, required: true },
});

const openTotal = computed(() => props.severityCounts.reduce((sum, item) => sum + item.total, 0));
const maxSeverity = computed(() => Math.max(1, ...props.severityCounts.map((item) => item.total)));

const severityBarColor = {
    low: 'bg-slate-400',
    medium: 'bg-amber-400',
    high: 'bg-orange-500',
    critical: 'bg-red-600',
};
</script>

<template>
    <Head title="Tableau de bord" />

    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold">Tableau de bord</h1>
        <Link href="/incidents/create" class="btn-primary">Déclarer un incident</Link>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="card">
            <p class="text-sm text-slate-500">Incidents ouverts</p>
            <p class="mt-1 text-3xl font-bold">{{ openTotal }}</p>
        </div>
        <Link href="/incidents?mine=1" class="card hover:border-indigo-300">
            <p class="text-sm text-slate-500">Qui me sont affectés</p>
            <p class="mt-1 text-3xl font-bold">{{ myOpenCount }}</p>
        </Link>
        <div class="card">
            <p class="text-sm text-slate-500">Non affectés</p>
            <p class="mt-1 text-3xl font-bold" :class="{ 'text-red-600': unassignedCount > 0 }">{{ unassignedCount }}</p>
        </div>
    </div>

    <div class="mt-6 grid gap-4 lg:grid-cols-2">
        <section class="card">
            <h2 class="mb-4 font-semibold">Incidents ouverts par criticité</h2>
            <ul class="space-y-3">
                <li v-for="item in severityCounts" :key="item.value" class="flex items-center gap-3">
                    <Link :href="`/incidents?severity=${item.value}`" class="w-20 text-sm text-slate-600 hover:underline">
                        {{ item.label }}
                    </Link>
                    <div class="h-3 flex-1 rounded bg-slate-100">
                        <div
                            class="h-3 rounded"
                            :class="severityBarColor[item.value]"
                            :style="{ width: `${(item.total / maxSeverity) * 100}%` }"
                        />
                    </div>
                    <span class="w-8 text-right text-sm font-semibold">{{ item.total }}</span>
                </li>
            </ul>
        </section>

        <section class="card">
            <h2 class="mb-4 font-semibold">Répartition par statut</h2>
            <ul class="grid grid-cols-2 gap-3">
                <li v-for="item in statusCounts" :key="item.value">
                    <Link :href="`/incidents?status=${item.value}`" class="block rounded border p-3 hover:bg-slate-50">
                        <StatusBadge :value="item.value" :label="item.label" />
                        <p class="mt-2 text-2xl font-bold">{{ item.total }}</p>
                    </Link>
                </li>
            </ul>
        </section>
    </div>

    <section class="card mt-6">
        <h2 class="mb-4 font-semibold">Derniers incidents</h2>
        <p v-if="recentIncidents.length === 0" class="text-sm text-slate-500">Aucun incident déclaré.</p>
        <ul v-else class="divide-y">
            <li v-for="incident in recentIncidents" :key="incident.id" class="flex items-center gap-3 py-3">
                <SeverityBadge :value="incident.severity" :label="incident.severity_label" />
                <Link :href="`/incidents/${incident.id}`" class="flex-1 font-medium hover:text-indigo-600">
                    {{ incident.title }}
                </Link>
                <StatusBadge :value="incident.status" :label="incident.status_label" />
            </li>
        </ul>
    </section>
</template>
