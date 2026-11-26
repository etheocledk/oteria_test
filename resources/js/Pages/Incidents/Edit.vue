<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import IncidentForm from '@/Components/IncidentForm.vue';

const props = defineProps({
    incident: { type: Object, required: true },
    options: { type: Object, required: true },
});

const form = useForm({
    title: props.incident.title,
    description: props.incident.description,
    severity: props.incident.severity,
    status: props.incident.status,
});

const submit = () => form.put(`/incidents/${props.incident.id}`);
</script>

<template>
    <Head :title="`Modifier l'incident #${incident.id}`" />
    <h1 class="mb-6 text-2xl font-bold">Modifier l'incident #{{ incident.id }}</h1>

    <IncidentForm :form="form" :options="options" show-status submit-label="Enregistrer" @submit="submit">
        <template #actions>
            <Link :href="`/incidents/${incident.id}`" class="btn-secondary">Annuler</Link>
        </template>
    </IncidentForm>
</template>
