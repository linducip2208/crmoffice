<script setup>
import { useForm, Head } from '@inertiajs/vue3';

const props = defineProps({
    contact: Object,
    token: String,
});

const form = useForm({
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('portal.accept.store', props.token), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Accept Invitation" />

    <div class="min-h-screen flex items-center justify-center p-6">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
            <h1 class="text-2xl font-semibold mb-1">Welcome, {{ contact.first_name }}</h1>
            <p class="text-sm text-slate-500 mb-6">Set a password to access your customer portal.</p>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                    <input
                        v-model="form.password"
                        type="password"
                        required
                        minlength="8"
                        class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500"
                    />
                    <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Confirm Password</label>
                    <input
                        v-model="form.password_confirmation"
                        type="password"
                        required
                        minlength="8"
                        class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500"
                    />
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg py-2.5 disabled:opacity-50"
                >
                    Activate account
                </button>
            </form>
        </div>
    </div>
</template>
