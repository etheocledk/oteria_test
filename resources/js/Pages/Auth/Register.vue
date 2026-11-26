<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import FormField from '@/Components/FormField.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Inscription" />
    <h2 class="mb-6 text-xl font-semibold">Créer un compte</h2>

    <form class="space-y-4" @submit.prevent="submit">
        <FormField label="Nom" id="name" :error="form.errors.name">
            <input id="name" v-model="form.name" type="text" autocomplete="name" required autofocus class="input" />
        </FormField>

        <FormField label="Adresse e-mail" id="email" :error="form.errors.email">
            <input id="email" v-model="form.email" type="email" autocomplete="username" required class="input" />
        </FormField>

        <FormField label="Mot de passe" id="password" :error="form.errors.password">
            <input id="password" v-model="form.password" type="password" autocomplete="new-password" required class="input" />
            <p class="mt-1 text-xs text-slate-500">
                12 caractères minimum, avec majuscule, minuscule, chiffre et symbole.
            </p>
        </FormField>

        <FormField label="Confirmation du mot de passe" id="password_confirmation">
            <input
                id="password_confirmation"
                v-model="form.password_confirmation"
                type="password"
                autocomplete="new-password"
                required
                class="input"
            />
        </FormField>

        <button type="submit" class="btn-primary w-full" :disabled="form.processing">Créer le compte</button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-600">
        Déjà inscrit ?
        <Link href="/login" class="font-medium text-indigo-600 hover:underline">Se connecter</Link>
    </p>
</template>
