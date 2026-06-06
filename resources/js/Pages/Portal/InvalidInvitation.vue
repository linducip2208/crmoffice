<script setup>
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Invitation Expired" />

    <div class="min-h-screen flex items-center justify-center p-6">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-sm border border-slate-200 p-8 text-center">
            <h1 class="text-xl font-semibold mb-2">Invitation invalid or expired</h1>
            <p class="text-sm text-slate-500 mb-6">Please ask your account manager to resend the invitation.</p>
            <Link :href="route('portal.login')" class="text-indigo-600 hover:underline text-sm">Back to login</Link>
        </div>
    </div>
</template>
