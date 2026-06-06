@props(['heading' => 'Get product updates', 'subheading' => 'One email per month. No spam.'])

<div class="bg-slate-50 border border-slate-200 rounded-xl p-6 max-w-md">
    <h3 class="font-semibold text-lg mb-1">{{ $heading }}</h3>
    <p class="text-sm text-slate-500 mb-4">{{ $subheading }}</p>

    <form id="newsletter-form" class="flex gap-2" onsubmit="return submitNewsletter(event)">
        <input
            type="email"
            name="email"
            required
            placeholder="you@example.com"
            class="flex-1 rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 px-3 py-2"
        />
        {{-- honeypot --}}
        <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" />
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg px-4 py-2">
            Subscribe
        </button>
    </form>

    <p id="newsletter-feedback" class="mt-3 text-sm hidden"></p>
</div>

<script>
async function submitNewsletter(e) {
    e.preventDefault();
    const form = e.target;
    const feedback = document.getElementById('newsletter-feedback');
    feedback.classList.add('hidden');

    const data = Object.fromEntries(new FormData(form));

    try {
        const res = await fetch('{{ route('newsletter.subscribe') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(data),
        });
        const json = await res.json();
        feedback.textContent = json.already_subscribed
            ? 'You are already subscribed — thanks!'
            : 'Thanks! You are subscribed.';
        feedback.className = 'mt-3 text-sm text-green-600';
        form.reset();
    } catch (err) {
        feedback.textContent = 'Something went wrong. Please try again.';
        feedback.className = 'mt-3 text-sm text-red-600';
    }
    return false;
}
</script>
