{{-- Phone-style notification preview. Inline styles: Filament CSS is pre-compiled. --}}
<div style="border-radius:1.25rem;background:linear-gradient(160deg,#1E3A8A,#3460C8);padding:1.25rem .9rem">
    <div style="display:flex;gap:.65rem;align-items:flex-start;border-radius:1rem;background:rgba(255,255,255,.95);padding:.75rem .8rem;box-shadow:0 10px 24px -12px rgba(0,0,0,.4)">
        <img src="/icons/icon-192.png" alt="" style="width:2.1rem;height:2.1rem;border-radius:.55rem;flex-shrink:0">
        <div style="min-width:0;flex:1;color:#0f172a">
            <div style="display:flex;justify-content:space-between;font-size:.68rem;opacity:.55">
                <span>Softia</span><span>now</span>
            </div>
            <p style="margin:.1rem 0 0;font-weight:700;font-size:.85rem;line-height:1.25;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $title ?: 'Your title' }}</p>
            <p style="margin:.15rem 0 0;font-size:.8rem;line-height:1.35;opacity:.8;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">{{ $body ?: 'Your message appears here.' }}</p>
        </div>
    </div>
</div>
<p style="margin-top:.6rem;font-size:.75rem;opacity:.6">Exact look varies by phone and browser.</p>
