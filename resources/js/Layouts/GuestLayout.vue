<template>
    <div class="flex min-h-screen items-center justify-center bg-slate-900 px-4">
        <div class="w-full max-w-md">
            <div class="mb-6 text-center text-white">
                <span class="rounded bg-indigo-500 px-2 py-1 text-lg font-bold">SD</span>
                <h1 class="mt-3 text-2xl font-semibold">SecureDesk</h1>
                <p class="text-sm text-slate-400">Gestion des incidents de sécurité</p>
            </div>
            <div class="rounded-lg bg-white p-8 shadow-xl">
                <slot />
            </div>
        </div>
    </div>
</template>
