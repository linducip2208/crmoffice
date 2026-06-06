<script setup>
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    client: Object,
    invoices: Array,
    projects: Array,
    tickets: Array,
});

const logout = () => router.post(route('portal.logout'));
</script>

<template>
    <Head title="Portal Dashboard" />

    <div class="min-h-screen">
        <header class="bg-white border-b border-slate-200">
            <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500">Portal</p>
                    <h1 class="text-lg font-semibold">{{ client.company_name }}</h1>
                </div>
                <button @click="logout" class="text-sm text-slate-600 hover:text-slate-900">Logout</button>
            </div>
        </header>

        <main class="max-w-6xl mx-auto px-6 py-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            <section class="bg-white rounded-xl border border-slate-200 p-5">
                <h2 class="font-semibold mb-3">Recent invoices</h2>
                <ul class="text-sm space-y-2">
                    <li v-for="inv in invoices" :key="inv.id" class="flex justify-between">
                        <span>{{ inv.number }}</span>
                        <span class="text-slate-500">{{ inv.status }}</span>
                    </li>
                    <li v-if="!invoices.length" class="text-slate-400">No invoices yet.</li>
                </ul>
            </section>

            <section class="bg-white rounded-xl border border-slate-200 p-5">
                <h2 class="font-semibold mb-3">Projects</h2>
                <ul class="text-sm space-y-2">
                    <li v-for="p in projects" :key="p.id" class="flex justify-between">
                        <span>{{ p.name }}</span>
                        <span class="text-slate-500">{{ p.progress }}%</span>
                    </li>
                    <li v-if="!projects.length" class="text-slate-400">No projects visible to you.</li>
                </ul>
            </section>

            <section class="bg-white rounded-xl border border-slate-200 p-5">
                <h2 class="font-semibold mb-3">Tickets</h2>
                <ul class="text-sm space-y-2">
                    <li v-for="t in tickets" :key="t.id" class="flex justify-between">
                        <span class="truncate">{{ t.subject }}</span>
                    </li>
                    <li v-if="!tickets.length" class="text-slate-400">No tickets yet.</li>
                </ul>
            </section>
        </main>
    </div>
</template>
