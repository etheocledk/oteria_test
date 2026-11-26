<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import IncidentForm from '@/Components/IncidentForm.vue';

defineProps({
    options: { type: Object, required: true },
});

const form = useForm({
    title: '',
    description: '',
    severity: 'medium',
});

const submit = () => form.post('/incidents');
</script>

<template>
    <Head title="Déclarer un incident" />
    <h1 class="mb-6 text-2xl font-bold">Déclarer un incident</h1>

    <IncidentForm :form="form" :options="options" submit-label="Déclarer" @submit="submit">
        <template #actions>
            <Link href="/incidents" class="btn-secondary">Annuler</Link>
        </template>
    </IncidentForm>
</template>
