{{-- Inline styles: Filament ships pre-compiled CSS, so custom utility classes aren't available without a build step. --}}
<x-filament-panels::page>
    <div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(14rem,1fr))">
        <x-filament::section compact>
            <x-slot name="heading">Database</x-slot>
            <div style="display:flex">
                @if ($pendingMigrations === null)
                    <x-filament::badge color="gray">Unknown</x-filament::badge>
                @elseif ($pendingMigrations > 0)
                    <x-filament::badge color="warning">{{ $pendingMigrations }} pending {{ Str::plural('migration', $pendingMigrations) }}</x-filament::badge>
                @else
                    <x-filament::badge color="success">Up to date</x-filament::badge>
                @endif
            </div>
        </x-filament::section>

        <x-filament::section compact>
            <x-slot name="heading">WebP image conversion</x-slot>
            <div style="display:flex">
                @if ($webpSupported)
                    <x-filament::badge color="success">Active</x-filament::badge>
                @else
                    <x-filament::badge color="warning">Unavailable: enable PHP's GD extension</x-filament::badge>
                @endif
            </div>
        </x-filament::section>

        <x-filament::section compact>
            <x-slot name="heading">Server</x-slot>
            <span style="font-size:.875rem">PHP {{ PHP_VERSION }} · Laravel {{ app()->version() }}</span>
        </x-filament::section>
    </div>

    {{-- Updates --}}
    <x-filament::section>
        <x-slot name="heading">Install an update</x-slot>
        <x-slot name="description">Upload a release package built with <code>php artisan softia:make-update</code>. Your .env, uploads and logs are never overwritten, and every replaced file is backed up.</x-slot>
        <x-slot name="afterHeader">
            @if ($zipSupported)
                {{ $this->uploadUpdateAction }}
            @endif
        </x-slot>

        @unless ($zipSupported)
            <x-filament::badge color="danger">PHP's zip extension is not enabled on this server.</x-filament::badge>
        @endunless

        <p style="font-size:.8rem;opacity:.6">Server upload limits: {{ $uploadLimit }}. Larger packages need these raised in cPanel › Select PHP Version › Options.</p>

        @if ($pendingUpdate)
            <div style="margin-top:1rem;border:1px solid rgb(30 58 138 / .2);border-radius:.75rem;padding:1rem">
                <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:1rem">
                    <div>
                        <p style="font-weight:700">Version {{ $pendingUpdate->version }} · {{ count($pendingUpdate->files) }} files</p>
                        @if ($pendingUpdate->notes)
                            <p style="font-size:.875rem;opacity:.75">{{ $pendingUpdate->notes }}</p>
                        @endif
                        <p style="font-size:.75rem;opacity:.55">Uploaded {{ $pendingUpdate->created_at->diffForHumans() }}{{ $pendingUpdate->user ? ' by '.$pendingUpdate->user->name : '' }}</p>
                    </div>
                    <div style="display:flex;gap:.5rem">
                        {{ ($this->discardUpdateAction)(['id' => $pendingUpdate->id]) }}
                        {{ ($this->installUpdateAction)(['id' => $pendingUpdate->id]) }}
                    </div>
                </div>
                <details style="margin-top:.75rem;font-size:.8rem">
                    <summary style="cursor:pointer;font-weight:600">Files in this update</summary>
                    <pre style="max-height:16rem;overflow:auto;white-space:pre-wrap;border-radius:.5rem;background:#0f172a;color:#e2e8f0;padding:.75rem;margin-top:.5rem">{{ implode("\n", $pendingUpdate->files) }}@if ($pendingUpdate->log)

{{ $pendingUpdate->log }}@endif</pre>
                </details>
            </div>
        @endif

        @if ($updates->isNotEmpty())
            <p style="margin-top:1.25rem;font-weight:600;font-size:.875rem">History</p>
            @foreach ($updates as $update)
                <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:1rem;padding:.75rem 0;{{ $loop->first ? '' : 'border-top:1px solid rgb(0 0 0 / .06);' }}">
                    <div>
                        <span style="font-weight:600">Version {{ $update->version }}</span>
                        <x-filament::badge :color="$update->status->getColor()" style="display:inline-flex;margin-left:.5rem">{{ $update->status->getLabel() }}</x-filament::badge>
                        <p style="font-size:.75rem;opacity:.55">{{ ($update->applied_at ?? $update->updated_at)->format('j M Y, H:i') }} · {{ count($update->files ?? []) }} files</p>
                        @if ($update->log)
                            <details style="font-size:.75rem;margin-top:.25rem">
                                <summary style="cursor:pointer">Log</summary>
                                <pre style="max-height:14rem;overflow:auto;white-space:pre-wrap;border-radius:.5rem;background:#0f172a;color:#e2e8f0;padding:.75rem;margin-top:.5rem">{{ $update->log }}</pre>
                            </details>
                        @endif
                    </div>
                    @if ($update->canRollBack())
                        {{ ($this->rollbackUpdateAction)(['id' => $update->id]) }}
                    @endif
                </div>
            @endforeach
        @endif
    </x-filament::section>

    {{-- Tasks --}}
    <x-filament::section>
        <x-slot name="heading">Tasks</x-slot>
        <x-slot name="description">Run these after uploading a new version of the site. No terminal needed.</x-slot>

        @foreach ($tasks as $key => $task)
            <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1rem 0;{{ $loop->first ? 'padding-top:0;' : 'border-top:1px solid rgb(0 0 0 / .06);' }}{{ $loop->last ? 'padding-bottom:0;' : '' }}">
                <div>
                    <p style="font-weight:600">{{ $task['label'] }}</p>
                    <p style="font-size:.875rem;opacity:.65">{{ $task['description'] }}</p>
                </div>
                {{ ($this->runTaskAction)(['task' => $key]) }}
            </div>
        @endforeach
    </x-filament::section>

    @if ($lastResult)
        <x-filament::section>
            <x-slot name="heading">{{ $lastResult['ok'] ? 'Completed' : 'Failed' }}: {{ $tasks[$lastResult['task']]['label'] }}</x-slot>
            <pre style="overflow-x:auto;white-space:pre-wrap;border-radius:.5rem;background:#0f172a;color:#e2e8f0;padding:1rem;font-size:.75rem">{{ $lastResult['output'] }}</pre>
        </x-filament::section>
    @endif
</x-filament-panels::page>
