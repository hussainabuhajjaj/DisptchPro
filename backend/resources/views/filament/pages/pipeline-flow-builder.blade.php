@php
    $flowFetchUrl = route('admin.pipeline.flow');
    $flowSaveUrl = route('admin.pipeline.flow.save');
@endphp

<x-filament::page>
    <div class="grid gap-6 lg:grid-cols-3">
        <x-filament::section heading="Pipeline Graph">
            <div id="flowforge-app" class="min-h-[320px] border rounded-lg bg-white dark:bg-gray-900 p-3">
                <div id="flowforge-loading" class="text-sm text-gray-500">Loading pipeline…</div>
            </div>
            <div id="flowforge-error" class="hidden text-sm text-amber-600 mt-3">
                Could not load FlowForge. Use the JSON editor to adjust stages/edges.
            </div>
        </x-filament::section>

        <x-filament::section class="lg:col-span-2" heading="JSON Editor (fallback)">
            <div class="space-y-3">
                <label class="text-xs text-gray-500">Nodes</label>
                <textarea id="nodes-json" class="w-full rounded-md border px-3 py-2 text-sm font-mono" rows="8" spellcheck="false"></textarea>
                <label class="text-xs text-gray-500">Edges</label>
                <textarea id="edges-json" class="w-full rounded-md border px-3 py-2 text-sm font-mono" rows="8" spellcheck="false"></textarea>
                <div class="flex items-center gap-3">
                    <x-filament::button id="save-flow" color="primary" size="sm">Save flow</x-filament::button>
                    <x-filament::button id="reload-flow" color="gray" size="sm">Reload</x-filament::button>
                    <label class="text-sm flex items-center gap-2">
                        <input type="checkbox" id="prune-missing" class="rounded border-gray-300">
                        Prune missing edges
                    </label>
                    <span id="save-status" class="text-xs text-gray-500"></span>
                </div>
            </div>
        </x-filament::section>
    </div>

    <x-filament-actions::modals />

    <script>
        (function () {
            const fetchUrl = @js($flowFetchUrl);
            const saveUrl = @js($flowSaveUrl);
            const nodesEl = document.getElementById('nodes-json');
            const edgesEl = document.getElementById('edges-json');
            const statusEl = document.getElementById('save-status');
            const loadingEl = document.getElementById('flowforge-loading');
            const errorEl = document.getElementById('flowforge-error');
            const pruneEl = document.getElementById('prune-missing');

            async function loadFlow() {
                statusEl.textContent = '';
                loadingEl.textContent = 'Loading pipeline…';
                try {
                    const res = await fetch(fetchUrl, { credentials: 'include' });
                    if (!res.ok) throw new Error('Failed to fetch');
                    const data = await res.json();
                    nodesEl.value = JSON.stringify(data.nodes ?? [], null, 2);
                    edgesEl.value = JSON.stringify(data.edges ?? [], null, 2);
                    statusEl.textContent = 'Loaded';
                } catch (e) {
                    statusEl.textContent = 'Load failed';
                    errorEl.classList.remove('hidden');
                } finally {
                    loadingEl.textContent = '';
                }
            }

            async function saveFlow() {
                statusEl.textContent = 'Saving…';
                try {
                    const payload = {
                        nodes: JSON.parse(nodesEl.value || '[]'),
                        edges: JSON.parse(edgesEl.value || '[]'),
                        prune_missing: pruneEl.checked,
                    };
                    const res = await fetch(saveUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        credentials: 'include',
                        body: JSON.stringify(payload),
                    });
                    if (!res.ok) throw new Error('Save failed');
                    statusEl.textContent = 'Saved';
                } catch (e) {
                    statusEl.textContent = 'Save failed';
                }
            }

            document.getElementById('save-flow').addEventListener('click', saveFlow);
            document.getElementById('reload-flow').addEventListener('click', loadFlow);

            // Optional: try to load FlowForge if present on CDN.
            function loadFlowForge() {
                const script = document.createElement('script');
                script.src = 'https://unpkg.com/flowforge/dist/flowforge.umd.js';
                script.async = true;
                script.onload = () => {
                    if (!window.FlowForge) {
                        errorEl.classList.remove('hidden');
                        return;
                    }
                    // Minimal render if library exposes a constructor.
                    try {
                        const app = new window.FlowForge({
                            target: document.getElementById('flowforge-app'),
                            fetchUrl,
                            saveUrl,
                            csrf: '{{ csrf_token() }}',
                        });
                        loadingEl.textContent = '';
                    } catch (err) {
                        errorEl.classList.remove('hidden');
                    }
                };
                script.onerror = () => errorEl.classList.remove('hidden');
                document.head.appendChild(script);
            }

            loadFlow();
            loadFlowForge();
        })();
    </script>
</x-filament::page>
