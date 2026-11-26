{{-- Connection indicator, install prompt, iOS add-to-home-screen guidance and update banner. All behaviour in resources/js/pwa.js --}}
<div x-data="pwaStatus()" x-init="init()" x-cloak>
    <div x-show="!online" x-transition class="fixed inset-x-0 z-[95] flex justify-center px-3" style="top: calc(0.5rem + var(--safe-top))">
        <div class="flex items-center gap-2 rounded-full bg-night-900 px-4 py-2 text-xs font-semibold text-white shadow-pop"><x-lucide-wifi-off class="size-4 text-gold-300" />You're offline<template x-if="queued > 0"><span class="text-gold-300">· <span x-text="queued"></span> change<span x-show="queued > 1">s</span> will sync</span></template></div>
    </div>
    <div x-show="online && justSynced" x-transition class="fixed inset-x-0 z-[95] flex justify-center px-3" style="top: calc(0.5rem + var(--safe-top))">
        <div class="rounded-full bg-forest-800 px-4 py-2 text-xs font-semibold text-white shadow-pop">Back online — changes synced</div>
    </div>

    <div x-show="updateReady" x-transition class="fixed inset-x-3 z-[92] mx-auto max-w-md rounded-2xl bg-forest-900 p-4 text-white shadow-pop" style="bottom: calc(5rem + var(--safe-bottom))">
        <p class="text-sm font-semibold">A new version of SIRAJ is ready.</p>
        <div class="mt-3 flex gap-2"><button @click="applyUpdate()" class="btn btn-gold btn-sm">Update now</button><button @click="updateReady = false" class="btn btn-sm text-white/80">Later</button></div>
    </div>

    <div x-show="canInstall" x-transition class="fixed inset-x-3 z-[91] mx-auto max-w-md rounded-2xl bg-surface p-4 shadow-pop ring-1 ring-line" style="bottom: calc(5rem + var(--safe-bottom))">
        <div class="flex items-start gap-3">
            <img src="/icons/icon-192.png" alt="" class="size-12 rounded-xl">
            <div class="min-w-0 flex-1"><p class="font-semibold">Install SIRAJ</p><p class="text-sm text-muted">Add SIRAJ to your home screen for quick access and offline reading.</p></div>
            <button @click="dismissInstall()" class="text-muted" aria-label="Dismiss"><x-lucide-x class="size-5" /></button>
        </div>
        <div class="mt-3 flex gap-2"><button @click="install()" class="btn btn-primary btn-sm flex-1">Install app</button><button @click="dismissInstall()" class="btn btn-outline btn-sm">Not now</button></div>
    </div>

    <div x-show="showIosHint" x-transition class="fixed inset-x-3 z-[91] mx-auto max-w-md rounded-2xl bg-surface p-4 shadow-pop ring-1 ring-line" style="bottom: calc(5rem + var(--safe-bottom))">
        <div class="flex items-start gap-3">
            <img src="/icons/icon-192.png" alt="" class="size-12 rounded-xl">
            <div class="flex-1 text-sm"><p class="font-semibold">Install SIRAJ on iPhone</p><p class="text-muted">Tap the <strong>Share</strong> button in Safari, then choose <strong>Add to Home Screen</strong>.</p></div>
            <button @click="dismissInstall()" class="text-muted" aria-label="Dismiss"><x-lucide-x class="size-5" /></button>
        </div>
    </div>
</div>
