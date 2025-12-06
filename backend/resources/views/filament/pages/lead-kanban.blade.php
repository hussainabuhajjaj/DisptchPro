@php
    $kanbanUrl = route('admin.leads.kanban');
    $moveUrl = route('admin.leads.kanban.move', ['lead' => '__ID__']);
@endphp

<x-filament::page>
    <x-filament::section heading="Lead Kanban" description="Drag a card to move a lead between stages.">
        <div id="kanban" class="grid gap-4 md:grid-cols-3 lg:grid-cols-4"></div>
        <div class="flex items-center gap-3 mt-3 text-sm">
            <x-filament::button id="kanban-reload" size="sm" color="gray">Reload</x-filament::button>
            <span id="kanban-status" class="text-gray-500"></span>
        </div>
    </x-filament::section>

    <x-filament-actions::modals />

    <script>
        (function () {
            const kanbanEl = document.getElementById('kanban');
            const statusEl = document.getElementById('kanban-status');
            const reloadEl = document.getElementById('kanban-reload');
            const fetchUrl = @js($kanbanUrl);
            const moveUrlTemplate = @js($moveUrl);

            async function loadKanban() {
                statusEl.textContent = 'Loading...';
                kanbanEl.innerHTML = '';
                try {
                    const res = await fetch(fetchUrl, { credentials: 'include' });
                    const data = await res.json();
                    renderColumns(data.columns || []);
                    statusEl.textContent = 'Loaded';
                } catch (e) {
                    statusEl.textContent = 'Load failed';
                }
            }

            function renderColumns(columns) {
                kanbanEl.innerHTML = '';
                columns.forEach(col => {
                    const colEl = document.createElement('div');
                    colEl.className = 'rounded-lg border bg-white dark:bg-gray-900 shadow-sm flex flex-col';
                    colEl.dataset.stageId = col.id;

                    const header = document.createElement('div');
                    header.className = 'px-3 py-2 border-b text-sm font-semibold flex items-center justify-between';
                    header.textContent = col.label || 'Stage';
                    const count = document.createElement('span');
                    count.className = 'text-xs text-gray-500 ml-2';
                    count.textContent = (col.cards || []).length;
                    header.appendChild(count);

                    const body = document.createElement('div');
                    body.className = 'p-2 space-y-2 min-h-[120px]';
                    body.addEventListener('dragover', ev => {
                        ev.preventDefault();
                        body.classList.add('ring-2', 'ring-primary-500');
                    });
                    body.addEventListener('dragleave', () => body.classList.remove('ring-2', 'ring-primary-500'));
                    body.addEventListener('drop', ev => {
                        ev.preventDefault();
                        body.classList.remove('ring-2', 'ring-primary-500');
                        const leadId = ev.dataTransfer.getData('text/plain');
                        moveLead(leadId, col.id);
                    });

                    (col.cards || []).forEach(card => {
                        body.appendChild(makeCard(card));
                    });

                    colEl.appendChild(header);
                    colEl.appendChild(body);
                    kanbanEl.appendChild(colEl);
                });
            }

            function makeCard(card) {
                const el = document.createElement('div');
                el.className = 'border rounded-md bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm cursor-move';
                el.draggable = true;
                el.dataset.leadId = card.id;
                el.addEventListener('dragstart', ev => {
                    ev.dataTransfer.setData('text/plain', card.id);
                });

                const title = document.createElement('div');
                title.className = 'font-semibold';
                title.textContent = card.name || 'Lead';
                const meta = document.createElement('div');
                meta.className = 'text-xs text-gray-500';
                meta.textContent = [card.company, card.owner || card.assignee].filter(Boolean).join(' · ');

                el.appendChild(title);
                el.appendChild(meta);
                return el;
            }

            async function moveLead(leadId, stageId) {
                statusEl.textContent = 'Saving...';
                try {
                    const url = moveUrlTemplate.replace('__ID__', leadId);
                    const res = await fetch(url, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        credentials: 'include',
                        body: JSON.stringify({ pipeline_stage_id: stageId }),
                    });
                    if (!res.ok) throw new Error('Failed');
                    statusEl.textContent = 'Saved';
                    await loadKanban();
                } catch (e) {
                    statusEl.textContent = 'Save failed';
                }
            }

            reloadEl.addEventListener('click', loadKanban);
            loadKanban();
        })();
    </script>
</x-filament::page>
