<script setup>
import { useForm, Head } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('portal.login.attempt'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Portal Login" />

    <div class="min-h-screen flex items-center justify-center p-6">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
            <h1 class="text-2xl font-semibold mb-1">Customer Portal</h1>
            <p class="text-sm text-slate-500 mb-6">Sign in to view your projects, invoices and tickets.</p>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                    <input
                        v-model="form.email"
                        type="email"
                        autocomplete="email"
                        required
                        class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500"
                    />
                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                    <input
                        v-model="form.password"
                        type="password"
                        autocomplete="current-password"
                        required
                        class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500"
                    />
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input v-model="form.remember" type="checkbox" class="rounded text-indigo-600" />
                    Remember me
                </label>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg py-2.5 disabled:opacity-50"
                >
                    Sign in
                </button>
            </form>
        </div>
    </div>
</template>
