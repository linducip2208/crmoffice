@php
use App\Models\Announcement;

$announcement = Announcement::query()
    ->where('audience', 'customers')
    ->where(function ($q) {
        $q->whereNull('publish_at')->orWhere('publish_at', '<=', now());
    })
    ->where(function ($q) {
        $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
    })
    ->orderByDesc('publish_at')
    ->orderByDesc('id')
    ->first();
@endphp

@if($announcement)
<div
    x-data="{
        dismissed: false,
        init() {
            this.dismissed = sessionStorage.getItem('ann_dismissed_{{ $announcement->id }}') === '1';
        },
        dismiss() {
            this.dismissed = true;
            sessionStorage.setItem('ann_dismissed_{{ $announcement->id }}', '1');
        }
    }"
    x-show="!dismissed"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 -translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-2"
    class="announcement-banner"
    style="background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #fff; padding: 12px 24px; display: flex; align-items: center; gap: 12px; font-size: 14px;"
>
    <svg style="flex-shrink:0;width:20px;height:20px;opacity:0.9" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 110-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38a.896.896 0 01-1.22-.384 18.006 18.006 0 01-1.896-5.814M10.34 6.66c-.253-.962-.584-1.892-.985-2.783-.247-.55-.06-1.21.463-1.511l.657-.38a.896.896 0 011.22.384 18.006 18.006 0 011.896 5.814M10.34 15.84c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38a.896.896 0 01-1.22-.384 18.006 18.006 0 01-1.896-5.814M10.34 6.66c-.253-.962-.584-1.892-.985-2.783-.247-.55-.06-1.21.463-1.511l.657-.38a.896.896 0 011.22.384 18.006 18.006 0 011.896 5.814M16.5 12a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" />
    </svg>
    <div style="flex:1;min-width:0">
        <strong style="margin-right:6px">{{ $announcement->title }}</strong>
        <span style="opacity:0.85">{{ $announcement->body }}</span>
    </div>
    <button type="button" @click="dismiss()" style="flex-shrink:0;background:rgba(255,255,255,0.15);border:none;color:#fff;width:28px;height:28px;border-radius:6px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:16px;line-height:1" title="Tutup">
        &times;
    </button>
</div>
@endif
