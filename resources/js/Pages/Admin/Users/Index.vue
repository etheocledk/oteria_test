<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';

defineProps({
    users: { type: Object, required: true },
    roles: { type: Array, required: true },
});

const page = usePage();

const changeRole = (user, role) => {
    router.patch(`/admin/users/${user.id}`, { role }, { preserveScroll: true });
};
</script>

<template>
    <Head title="Utilisateurs" />
    <h1 class="mb-6 text-2xl font-bold">Utilisateurs</h1>

    <p v-if="page.props.errors.role" class="mb-4 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        {{ page.props.errors.role }}
    </p>

    <div class="overflow-hidden rounded-lg border bg-white">
        <table class="w-full text-sm">
            <thead class="bg-slate-100 text-left text-slate-600">
                <tr>
                    <th class="px-4 py-3">Nom</th>
                    <th class="px-4 py-3">E-mail</th>
                    <th class="px-4 py-3">Incidents affectés</th>
                    <th class="px-4 py-3">Rôle</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <tr v-for="user in users.data" :key="user.id">
                    <td class="px-4 py-3 font-medium">{{ user.name }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ user.email }}</td>
                    <td class="px-4 py-3">{{ user.assigned_incidents_count }}</td>
                    <td class="px-4 py-3">
                        <select
                            :value="user.role"
                            class="input w-auto"
                            :disabled="user.id === page.props.auth.user.id"
                            @change="changeRole(user, $event.target.value)"
                        >
                            <option v-for="role in roles" :key="role.value" :value="role.value">{{ role.label }}</option>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <Pagination :meta="users.meta" />
</template>
