<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import FlashMessage from '@/Components/FlashMessage.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const isAdmin = computed(() => page.props.auth.isAdmin);

const navigation = computed(() => [
    { label: 'Tableau de bord', href: '/dashboard', active: page.url.startsWith('/dashboard') },
    { label: 'Incidents', href: '/incidents', active: page.url.startsWith('/incidents') },
    ...(isAdmin.value
        ? [{ label: 'Utilisateurs', href: '/admin/users', active: page.url.startsWith('/admin') }]
        : []),
]);
</script>

<template>
    <div class="min-h-screen bg-slate-50 text-slate-900">
        <header class="bg-slate-900 text-white">
            <nav class="mx-auto flex max-w-6xl items-center gap-8 px-6 py-4">
                <Link href="/dashboard" class="flex items-center gap-2 text-lg font-semibold">
                    <span class="rounded bg-indigo-500 px-2 py-0.5 text-sm">SD</span>
                    SecureDesk
                </Link>
                <div class="flex gap-1">
                    <Link
                        v-for="item in navigation"
                        :key="item.href"
                        :href="item.href"
                        class="rounded px-3 py-2 text-sm font-medium"
                        :class="item.active ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white'"
                    >
                        {{ item.label }}
                    </Link>
                </div>
                <div v-if="user" class="ml-auto flex items-center gap-4 text-sm">
                    <span class="text-slate-300">
                        {{ user.name }}
                        <span class="ml-1 rounded bg-slate-700 px-2 py-0.5 text-xs">{{ user.role_label }}</span>
                    </span>
                    <Link href="/logout" method="post" as="button" class="text-slate-300 hover:text-white">
                        Déconnexion
                    </Link>
                </div>
            </nav>
        </header>
        <main class="mx-auto max-w-6xl px-6 py-8">
            <FlashMessage />
            <slot />
        </main>
    </div>
</template>
