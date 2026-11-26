<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import FormField from '@/Components/FormField.vue';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Connexion" />
    <h2 class="mb-6 text-xl font-semibold">Connexion</h2>

    <form class="space-y-4" @submit.prevent="submit">
        <FormField label="Adresse e-mail" id="email" :error="form.errors.email">
            <input id="email" v-model="form.email" type="email" autocomplete="username" required autofocus class="input" />
        </FormField>

        <FormField label="Mot de passe" id="password" :error="form.errors.password">
            <input id="password" v-model="form.password" type="password" autocomplete="current-password" required class="input" />
        </FormField>

        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input v-model="form.remember" type="checkbox" class="rounded" />
            Se souvenir de moi
        </label>

        <button type="submit" class="btn-primary w-full" :disabled="form.processing">Se connecter</button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-600">
        Pas encore de compte ?
        <Link href="/register" class="font-medium text-indigo-600 hover:underline">Créer un compte</Link>
    </p>
</template>
