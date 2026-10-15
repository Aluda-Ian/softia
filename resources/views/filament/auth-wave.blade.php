{{-- Brand wave behind the admin sign-in (and other simple auth pages). Inline styles: Filament's CSS is pre-compiled. --}}
<div aria-hidden="true" style="position:fixed;inset:auto 0 0 0;height:38vh;pointer-events:none;z-index:0;background:linear-gradient(180deg,rgba(201,220,243,0) 0%,#C9DCF3 100%)">
    <svg viewBox="0 0 1440 120" preserveAspectRatio="none" style="position:absolute;left:0;right:0;bottom:22vh;width:100%;height:9vh;display:block;color:#C9DCF3">
        <path fill="currentColor" d="M0 56C240 16 520 0 820 34C1040 59 1180 104 1440 76V120H0Z" />
    </svg>
    <svg viewBox="0 0 1440 120" preserveAspectRatio="none" style="position:absolute;left:0;right:0;bottom:0;width:100%;height:24vh;display:block;color:#1E3A8A;transform:scaleX(-1)">
        <path fill="currentColor" d="M0 56C240 16 520 0 820 34C1040 59 1180 104 1440 76V120H0Z" />
    </svg>
</div>
<style>
    /* Keep the sign-in card above the wave. */
    .fi-simple-main { position: relative; z-index: 1; }
</style>
