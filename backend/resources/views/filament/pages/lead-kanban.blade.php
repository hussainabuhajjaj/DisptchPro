@php
    $kanbanUrl = route('admin.leads.kanban');
    $moveUrl = route('admin.leads.kanban.move', ['lead' => '__ID__']);
@endphp

<x-filament::page>
    <style>
        .kanban-grid {
            display: grid;
            gap: 1rem;
        }
        @media (min-width: 768px) { .kanban-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        @media (min-width: 1024px) { .kanban-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
    </style>

    <x-filament::section heading="Lead Kanban" description="Drag a card to move a lead between stages.">
        <div class="flex flex-wrap items-center gap-3 mb-3 text-sm">
            <x-filament::button id="kanban-reload" size="sm" color="gray" icon="heroicon-o-arrow-path">
                Reload
            </x-filament::button>
            <span id="kanban-status" class="text-gray-500"></span>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-1 text-emerald-800 text-xs">Hot</span>
                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-1 text-amber-800 text-xs">Warm</span>
                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-1 text-slate-700 text-xs">New/Cold</span>
            </div>
        </div>

        <div class="grid gap-3 md:grid-cols-3 mb-3">
            <label class="block">
                <span class="text-xs uppercase tracking-wide text-slate-500">Search</span>
                <input id="kanban-filter-text" type="text" class="mt-1 block w-full rounded-lg border-slate-300" placeholder="Name, company, owner...">
            </label>
            <label class="block">
                <span class="text-xs uppercase tracking-wide text-slate-500">Tag / Source</span>
                <input id="kanban-filter-tag" type="text" class="mt-1 block w-full rounded-lg border-slate-300" placeholder="Hot, Box Truck, Website...">
            </label>
            <div class="flex items-end gap-2">
                <button id="kanban-clear-filters" type="button" class="h-10 px-3 rounded-lg border border-slate-300 text-sm text-slate-700">Clear filters</button>
            </div>
        </div>

        <div id="kanban" class="kanban-grid">
            <template id="column-template">
                <div class="kanban-column rounded-xl bg-slate-50 border border-slate-200 shadow-sm dark:bg-slate-900/60 dark:border-slate-700 flex flex-col min-h-[260px]">
                    <div class="kanban-column__header flex items-center justify-between gap-2 px-3 py-2 border-b border-slate-200 dark:border-slate-700">
                        <div class="flex items-center gap-2 text-sm font-semibold text-slate-800 dark:text-slate-100">
                            <span class="stage-dot h-2 w-2 rounded-full bg-primary-500"></span>
                            <span class="stage-label">Stage</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="stage-count inline-flex items-center justify-center h-6 min-w-[1.5rem] rounded-full bg-slate-200 text-[11px] font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-200"></span>
                            <button type="button" class="text-slate-400 hover:text-primary-500 transition">
                                <x-heroicon-m-plus class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                    <div class="kanban-column__body flex-1 p-2 space-y-2 min-h-[180px] overflow-y-auto"></div>
                </div>
            </template>
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
            const filterTextEl = document.getElementById('kanban-filter-text');
            const filterTagEl = document.getElementById('kanban-filter-tag');
            const clearFiltersEl = document.getElementById('kanban-clear-filters');
            let filterText = '';
            let filterTag = '';

            const statusColor = (status) => {
                if (!status) return 'bg-slate-100 text-slate-700';
                const s = status.toLowerCase();
                if (['hot', 'qualified', 'agreement sent'].includes(s)) return 'bg-emerald-100 text-emerald-800';
                if (['warm', 'contacted'].includes(s)) return 'bg-amber-100 text-amber-800';
                if (['lost', 'inactive'].includes(s)) return 'bg-rose-100 text-rose-800';
                return 'bg-slate-100 text-slate-700';
            };

            const stageDot = (color) => {
                if (!color) return 'bg-primary-500';
                return '';
            };

            const initials = (name = '') => {
                if (!name) return '';
                return name.split(' ').map(p => p.charAt(0)).join('').slice(0, 2).toUpperCase();
            };

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
                    const cardsFiltered = (col.cards || []).filter(card => {
                        const textMatch = filterText
                            ? [card.name, card.company, card.owner, card.assignee, card.summary].some(val =>
                                  (val || '').toLowerCase().includes(filterText.toLowerCase())
                              )
                            : true;
                        const tagMatch = filterTag
                            ? ([...(card.tags || []), card.source].some(val =>
                                  (val || '').toLowerCase().includes(filterTag.toLowerCase())
                              ))
                            : true;
                        return textMatch && tagMatch;
                    });

                    const template = document.getElementById('column-template');
                    const colEl = template.content.firstElementChild.cloneNode(true);
                    colEl.dataset.stageId = col.id;

                    const header = colEl.querySelector('.kanban-column__header');
                    header.querySelector('.stage-label').textContent = col.label || 'Stage';
                    const dot = header.querySelector('.stage-dot');
                    if (col.color) {
                        dot.style.backgroundColor = col.color;
                    } else {
                        dot.classList.add(stageDot());
                    }
                    const count = cardsFiltered ? cardsFiltered.length : 0;
                    header.querySelector('.stage-count').textContent = count;
                    if (col.wip_limit) {
                        header.querySelector('.stage-count').title = `Limit ${col.wip_limit}`;
                        if (count >= col.wip_limit) {
                            header.querySelector('.stage-count').classList.add('bg-rose-100','text-rose-700');
                        }
                    }

                    const body = colEl.querySelector('.kanban-column__body');
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

                    if (!cardsFiltered.length) {
                        const empty = document.createElement('div');
                        empty.className = 'text-xs text-gray-400 italic';
                        empty.textContent = 'No leads in this stage';
                        body.appendChild(empty);
                    } else {
                        cardsFiltered.forEach(card => body.appendChild(makeCard(card)));
                    }

                    kanbanEl.appendChild(colEl);
                });
            }

            function makeCard(card) {
                const el = document.createElement('div');
                el.className = 'kanban-card border rounded-lg bg-white dark:bg-slate-800/70 px-3 py-2 text-sm cursor-move shadow-sm hover:border-primary-300 transition';
                el.draggable = true;
                el.dataset.leadId = card.id;
                el.addEventListener('dragstart', ev => {
                    ev.dataTransfer.setData('text/plain', card.id);
                });

                const header = document.createElement('div');
                header.className = 'flex items-start justify-between gap-2';

                const title = document.createElement('div');
                title.className = 'font-semibold leading-tight';
                title.textContent = card.name || 'Lead';

                const badge = document.createElement('span');
                badge.className = `inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold ${statusColor(card.status)}`;
                badge.textContent = (card.status || 'New').toUpperCase();

                header.appendChild(title);
                header.appendChild(badge);

                const body = document.createElement('div');
                body.className = 'mt-1 space-y-2';

                if (card.summary) {
                    const summary = document.createElement('div');
                    summary.className = 'text-xs text-slate-600 dark:text-slate-300 leading-snug';
                    summary.style.display = '-webkit-box';
                    summary.style.webkitBoxOrient = 'vertical';
                    summary.style.webkitLineClamp = '3';
                    summary.style.overflow = 'hidden';
                    summary.textContent = card.summary;
                    body.appendChild(summary);
                }

                const metaRow = document.createElement('div');
                metaRow.className = 'flex flex-wrap items-center gap-2 text-[11px] text-slate-500';

                const line1 = [card.company, card.owner || card.assignee].filter(Boolean).join(' · ');
                if (line1) {
                    const pill = document.createElement('span');
                    pill.className = 'inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5';
                    pill.textContent = line1;
                    metaRow.appendChild(pill);
                }
                if (card.created_at) {
                    const pill = document.createElement('span');
                    pill.className = 'inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5';
                    pill.textContent = card.created_at.split(' ')[0];
                    metaRow.appendChild(pill);
                }
                if (card.next_follow_up_at) {
                    const pill = document.createElement('span');
                    pill.className = 'inline-flex items-center gap-1 rounded-full bg-amber-100 text-amber-800 px-2 py-0.5';
                    pill.textContent = 'Next: ' + card.next_follow_up_at.split(' ')[0];
                    metaRow.appendChild(pill);
                }
                if (card.phone || card.email) {
                    const pill = document.createElement('span');
                    pill.className = 'inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5';
                    pill.textContent = [card.phone, card.email].filter(Boolean).join(' • ');
                    metaRow.appendChild(pill);
                }
                if (card.tags && card.tags.length) {
                    card.tags.slice(0, 2).forEach(tag => {
                        const pill = document.createElement('span');
                        pill.className = 'inline-flex items-center gap-1 rounded-full bg-indigo-50 text-indigo-700 px-2 py-0.5';
                        pill.textContent = tag;
                        metaRow.appendChild(pill);
                    });
                    if (card.tags.length > 2) {
                        const more = document.createElement('span');
                        more.className = 'inline-flex items-center gap-1 rounded-full bg-indigo-50 text-indigo-700 px-2 py-0.5';
                        more.textContent = `+${card.tags.length - 2}`;
                        metaRow.appendChild(more);
                    }
                }
                if (metaRow.children.length) {
                    body.appendChild(metaRow);
                }

                // Footer avatars
                if (card.owner || card.assignee) {
                    const footer = document.createElement('div');
                    footer.className = 'flex items-center gap-2';
                    [card.owner, card.assignee].filter(Boolean).forEach(name => {
                        const avatar = document.createElement('span');
                        avatar.className = 'inline-flex h-7 w-7 items-center justify-center rounded-full bg-slate-200 text-[11px] font-semibold text-slate-700';
                        avatar.textContent = initials(name);
                        footer.appendChild(avatar);
                    });
                    body.appendChild(footer);
                }

                el.appendChild(header);
                el.appendChild(body);
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
                    if (!res.ok) {
                        const err = await res.json().catch(() => ({}));
                        const msg = err.message || (err.errors ? Object.values(err.errors).flat().join(' ') : 'Failed');
                        throw new Error(msg);
                    }
                    statusEl.textContent = 'Saved';
                    await loadKanban();
                } catch (e) {
                    statusEl.textContent = e?.message || 'Save failed';
                }
            }

            reloadEl.addEventListener('click', loadKanban);
            filterTextEl.addEventListener('input', (e) => { filterText = e.target.value || ''; renderColumns([]); loadKanban(); });
            filterTagEl.addEventListener('input', (e) => { filterTag = e.target.value || ''; renderColumns([]); loadKanban(); });
            clearFiltersEl.addEventListener('click', () => {
                filterText = '';
                filterTag = '';
                filterTextEl.value = '';
                filterTagEl.value = '';
                loadKanban();
            });
            loadKanban();
        })();
    </script>
</x-filament::page>
