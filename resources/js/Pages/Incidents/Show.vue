<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import SeverityBadge from '@/Components/SeverityBadge.vue';
import StatusBadge from '@/Components/StatusBadge.vue';

const props = defineProps({
    incident: { type: Object, required: true },
    assignableUsers: { type: Array, required: true },
});

const assignmentForm = useForm({
    assignee_id: props.incident.assignee?.id ?? null,
});

const commentForm = useForm({
    comment: '',
});

const confirmingDelete = ref(false);

const assign = () => assignmentForm.put(`/incidents/${props.incident.id}/assignment`, { preserveScroll: true });

const addComment = () =>
    commentForm.post(`/incidents/${props.incident.id}/comments`, {
        preserveScroll: true,
        onSuccess: () => commentForm.reset(),
    });

const destroy = () => router.delete(`/incidents/${props.incident.id}`);

const formatDate = (iso) => new Date(iso).toLocaleString('fr-FR', { dateStyle: 'medium', timeStyle: 'short' });
</script>

<template>
    <Head :title="`Incident #${incident.id}`" />

    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <Link href="/incidents" class="text-sm text-slate-500 hover:underline">← Retour aux incidents</Link>
            <h1 class="mt-1 text-2xl font-bold">#{{ incident.id }} · {{ incident.title }}</h1>
            <div class="mt-2 flex gap-2">
                <SeverityBadge :value="incident.severity" :label="incident.severity_label" />
                <StatusBadge :value="incident.status" :label="incident.status_label" />
            </div>
        </div>
        <div class="flex gap-2">
            <Link v-if="incident.can.update" :href="`/incidents/${incident.id}/edit`" class="btn-secondary">Modifier</Link>
            <template v-if="incident.can.delete">
                <button v-if="!confirmingDelete" type="button" class="btn-danger" @click="confirmingDelete = true">
                    Supprimer
                </button>
                <template v-else>
                    <button type="button" class="btn-danger" @click="destroy">Confirmer la suppression</button>
                    <button type="button" class="btn-secondary" @click="confirmingDelete = false">Annuler</button>
                </template>
            </template>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <section class="card">
                <h2 class="mb-3 font-semibold">Description</h2>
                <!-- Rendered as text: Vue escapes it, so user input cannot inject HTML (XSS). -->
                <p class="whitespace-pre-line text-slate-700">{{ incident.description }}</p>
            </section>

            <section class="card">
                <h2 class="mb-4 font-semibold">Historique</h2>

                <form class="mb-6" @submit.prevent="addComment">
                    <textarea
                        v-model="commentForm.comment"
                        rows="3"
                        maxlength="5000"
                        placeholder="Ajouter un commentaire…"
                        class="input"
                        required
                    />
                    <p v-if="commentForm.errors.comment" class="mt-1 text-sm text-red-600">{{ commentForm.errors.comment }}</p>
                    <div class="mt-2 flex justify-end">
                        <button type="submit" class="btn-primary" :disabled="commentForm.processing">Commenter</button>
                    </div>
                </form>

                <ol class="space-y-4 border-l-2 border-slate-200 pl-4">
                    <li v-for="activity in incident.activities" :key="activity.id">
                        <p class="text-sm">
                            <span class="font-medium">{{ activity.user ?? 'Utilisateur supprimé' }}</span>
                            <span class="text-slate-500"> · {{ activity.action_label }} · {{ formatDate(activity.created_at) }}</span>
                        </p>
                        <p class="mt-1 whitespace-pre-line text-sm text-slate-700">{{ activity.description }}</p>
                    </li>
                </ol>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="card text-sm">
                <h2 class="mb-3 font-semibold">Détails</h2>
                <dl class="space-y-2">
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-500">Déclaré par</dt>
                        <dd>{{ incident.reporter?.name }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-500">Déclaré le</dt>
                        <dd>{{ formatDate(incident.created_at) }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-500">Affecté à</dt>
                        <dd>{{ incident.assignee?.name ?? 'Personne' }}</dd>
                    </div>
                    <div v-if="incident.resolved_at" class="flex justify-between gap-2">
                        <dt class="text-slate-500">Résolu le</dt>
                        <dd>{{ formatDate(incident.resolved_at) }}</dd>
                    </div>
                </dl>
            </section>

            <section v-if="incident.can.assign" class="card text-sm">
                <h2 class="mb-3 font-semibold">Affectation</h2>
                <form class="space-y-3" @submit.prevent="assign">
                    <select v-model="assignmentForm.assignee_id" class="input">
                        <option :value="null">Personne</option>
                        <option v-for="user in assignableUsers" :key="user.id" :value="user.id">
                            {{ user.name }} ({{ user.role_label }})
                        </option>
                    </select>
                    <p v-if="assignmentForm.errors.assignee_id" class="text-red-600">{{ assignmentForm.errors.assignee_id }}</p>
                    <button type="submit" class="btn-primary w-full" :disabled="assignmentForm.processing">Affecter</button>
                </form>
            </section>
        </aside>
    </div>
</template>
