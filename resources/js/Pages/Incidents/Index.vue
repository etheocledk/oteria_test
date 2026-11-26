<script setup>
import { reactive, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import SeverityBadge from '@/Components/SeverityBadge.vue';
import StatusBadge from '@/Components/StatusBadge.vue';

const props = defineProps({
    incidents: { type: Object, required: true },
    filters: { type: Object, required: true },
    options: { type: Object, required: true },
});

const filters = reactive({ ...props.filters });

let searchTimeout = null;

watch(filters, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        const query = Object.fromEntries(
            Object.entries(filters).filter(([, value]) => value !== '' && value !== false),
        );
        if (query.mine) {
            query.mine = 1;
        }
        router.get('/incidents', query, { preserveState: true, replace: true });
    }, 300);
});

const formatDate = (iso) => new Date(iso).toLocaleString('fr-FR', { dateStyle: 'short', timeStyle: 'short' });
</script>

<template>
    <Head title="Incidents" />

    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold">Incidents</h1>
        <Link href="/incidents/create" class="btn-primary">Déclarer un incident</Link>
    </div>

    <div class="card mb-4 flex flex-wrap items-center gap-3">
        <input v-model="filters.search" type="search" placeholder="Rechercher par titre…" class="input max-w-xs" />
        <select v-model="filters.severity" class="input w-auto">
            <option value="">Toutes criticités</option>
            <option v-for="option in options.severities" :key="option.value" :value="option.value">
                {{ option.label }}
            </option>
        </select>
        <select v-model="filters.status" class="input w-auto">
            <option value="">Tous statuts</option>
            <option v-for="option in options.statuses" :key="option.value" :value="option.value">
                {{ option.label }}
            </option>
        </select>
        <label class="flex items-center gap-2 text-sm">
            <input v-model="filters.mine" type="checkbox" class="rounded" />
            Mes incidents
        </label>
    </div>

    <div class="overflow-hidden rounded-lg border bg-white">
        <table class="w-full text-sm">
            <thead class="bg-slate-100 text-left text-slate-600">
                <tr>
                    <th class="px-4 py-3">#</th>
                    <th class="px-4 py-3">Titre</th>
                    <th class="px-4 py-3">Criticité</th>
                    <th class="px-4 py-3">Statut</th>
                    <th class="px-4 py-3">Affecté à</th>
                    <th class="px-4 py-3">Déclaré le</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <tr v-if="incidents.data.length === 0">
                    <td colspan="6" class="px-4 py-8 text-center text-slate-500">Aucun incident trouvé.</td>
                </tr>
                <tr v-for="incident in incidents.data" :key="incident.id" class="hover:bg-slate-50">
                    <td class="px-4 py-3 text-slate-500">{{ incident.id }}</td>
                    <td class="px-4 py-3">
                        <Link :href="`/incidents/${incident.id}`" class="font-medium hover:text-indigo-600">
                            {{ incident.title }}
                        </Link>
                    </td>
                    <td class="px-4 py-3"><SeverityBadge :value="incident.severity" :label="incident.severity_label" /></td>
                    <td class="px-4 py-3"><StatusBadge :value="incident.status" :label="incident.status_label" /></td>
                    <td class="px-4 py-3">
                        <span v-if="incident.assignee">{{ incident.assignee.name }}</span>
                        <span v-else class="text-slate-400">Non affecté</span>
                    </td>
                    <td class="px-4 py-3 text-slate-500">{{ formatDate(incident.created_at) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <Pagination :meta="incidents.meta" />
</template>
