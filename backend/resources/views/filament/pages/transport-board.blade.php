@php
    $statusColors = [
        'draft' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
        'posted' => 'bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-200',
        'assigned' => 'bg-sky-100 text-sky-800 dark:bg-sky-500/20 dark:text-sky-200',
        'in_transit' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-500/20 dark:text-indigo-200',
        'delivered' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-200',
        'completed' => 'bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-200',
        'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-200',
    ];
    $statusOptions = array_keys($statusColors);
    $dispatchers = collect($loads)->map(fn($l) => ['id' => $l['dispatcher_id'] ?? null, 'name' => $l['dispatcher'] ?? null])
        ->filter(fn($d) => $d['id'] && $d['name'])
        ->unique('id')
        ->sortBy('name')
        ->values();
    $drivers = collect($loads)->pluck('driver')->filter()->unique()->sort()->values();
@endphp

@once
    @push('styles')
        <link
            rel="stylesheet"
            href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
            crossorigin=""
        />
        <style>
            /* Lightweight styles so the page looks good even without Tailwind. */
            :root {
                --tms-border: #e5e7eb;
                --tms-bg: #ffffff;
                --tms-muted: #6b7280;
                --tms-card-radius: 14px;
                --tms-primary: #2563eb;
            }
            .tms-grid { display: grid; gap: 12px; }
            @media (min-width: 768px) { .tms-grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
            .tms-card {
                border: 1px solid var(--tms-border);
                border-radius: var(--tms-card-radius);
                background: var(--tms-bg);
                padding: 14px;
                box-shadow: 0 5px 18px rgba(0,0,0,0.03);
            }
            .tms-legend-item { display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--tms-muted); }
            .tms-filter-bar { border: 1px solid var(--tms-border); border-radius: var(--tms-card-radius); padding: 10px 12px; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; background: var(--tms-bg); }
            .tms-filter-bar input,
            .tms-filter-bar select { border: 1px solid var(--tms-border); border-radius: 10px; padding: 7px 10px; font-size: 13px; }
            .tms-btn { border: 1px solid var(--tms-primary); color: var(--tms-primary); background: transparent; border-radius: 10px; padding: 7px 12px; font-size: 12px; cursor: pointer; }
            .tms-btn-primary { background: var(--tms-primary); color: #fff; box-shadow: 0 6px 18px rgba(37,99,235,0.25); }
            .tms-btn:hover { filter: brightness(0.95); }
            .tms-split { display: grid; gap: 14px; }
            @media (min-width: 1024px) { .tms-split { grid-template-columns: 2fr 1fr; } }
            .tms-load-list { max-height: 560px; overflow-y: auto; padding-right: 6px; }
            .tms-load-card {
                border: 1px solid var(--tms-border);
                border-radius: 12px;
                background: #f8fafc;
                padding: 12px;
                cursor: pointer;
                transition: border-color 120ms ease, box-shadow 120ms ease;
            }
            .tms-load-card:hover { border-color: var(--tms-primary); box-shadow: 0 6px 16px rgba(37,99,235,0.12); }
            .tms-badge { display: inline-flex; align-items: center; padding: 4px 8px; border-radius: 999px; font-size: 11px; font-weight: 600; }
            .tms-kv { font-size: 12px; color: var(--tms-muted); }
            #tms-map { min-height: 520px; border: 1px solid var(--tms-border); border-radius: var(--tms-card-radius); overflow: hidden; }
            .tms-legend-toggle { cursor: pointer; color: var(--tms-primary); font-size: 12px; }
            .tms-hidden { display: none !important; }
            .tms-highlight { border-color: var(--tms-primary); box-shadow: 0 0 0 2px rgba(37,99,235,0.3); }
            .tms-map-alert { position: absolute; top: 12px; right: 12px; background: #fff; padding: 8px 12px; border-radius: 10px; border: 1px solid var(--tms-border); box-shadow: 0 6px 18px rgba(0,0,0,0.08); font-size: 12px; }
            .tms-status-bar { display:flex; flex-wrap:wrap; gap:8px; align-items:center; padding:10px 12px; border:1px solid var(--tms-border); border-radius: var(--tms-card-radius); background: var(--tms-bg); box-shadow: 0 4px 14px rgba(0,0,0,0.04); }
            .tms-chip { display:inline-flex; align-items:center; gap:6px; padding:6px 10px; border-radius:999px; font-size:12px; border:1px solid var(--tms-border); background:#f8fafc; color:#111827; }
            .tms-chip.bad { border-color:#fecdd3; color:#b91c1c; background:#fff1f2; }
            .tms-chip.warn { border-color:#fcd34d; color:#92400e; background:#fffbeb; }
            .tms-chip.good { border-color:#bbf7d0; color:#065f46; background:#ecfdf3; }
            .tms-chip.muted { border-color:#e5e7eb; color:#4b5563; background:#f9fafb; }
            .tms-sla-label {
                background:#fff;
                border:1px solid #e5e7eb;
                border-radius:999px;
                padding:4px 8px;
                font-size:11px;
                font-weight:600;
                box-shadow:0 4px 10px rgba(0,0,0,0.08);
                color:#111827;
                line-height:1.2;
            }
            .tms-stop-label {
                background:#111827;
                color:#f8fafc;
                padding:2px 6px;
                border-radius:6px;
                font-size:11px;
                border:1px solid #1f2937;
            }
            .tms-city-label {
                background:#0f172a;
                color:#e2e8f0;
                padding:3px 7px;
                border-radius:8px;
                font-size:12px;
                border:1px solid rgba(255,255,255,0.2);
                box-shadow:0 6px 14px rgba(0,0,0,0.2);
                white-space: nowrap;
            }
            .tms-mini-btn {
                border:1px solid var(--tms-border);
                border-radius:8px;
                padding:4px 8px;
                font-size:11px;
                background:#fff;
                cursor:pointer;
            }
            .tms-mini-select {
                border:1px solid var(--tms-border);
                border-radius:8px;
                padding:3px 6px;
                font-size:11px;
                background:#fff;
            }
            .tms-issues {
                display:flex;
                gap:10px;
                align-items:center;
                padding:10px 12px;
                border:1px solid var(--tms-border);
                border-radius: var(--tms-card-radius);
                background: #fff7ed;
                color:#7c2d12;
                box-shadow:0 4px 14px rgba(0,0,0,0.04);
            }
        </style>
    @endpush

    @push('scripts')
        <script
            src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""
        ></script>
        <script>
            const TmsMap = (() => {
                let loads = @json($loads);
                const dispatcherOptions = @json($dispatchers);
                const currentUserId = {{ auth()->id() ?? 'null' }};
                const currentUserName = @json(optional(auth()->user())->name);
                const statusColorMap = {
                    draft: '#6b7280',
                    posted: '#f59e0b',
                    assigned: '#0ea5e9',
                    in_transit: '#6366f1',
                    delivered: '#10b981',
                    completed: '#22c55e',
                    cancelled: '#ef4444',
                };
            const stopTypeColors = {
                pickup: '#22c55e',
                delivery: '#2563eb',
                fuel: '#f97316',
                break: '#eab308',
                rest: '#a855f7',
                inspection: '#ef4444',
                service: '#0ea5e9',
                lodging: '#f472b6',
                detention: '#ef4444',
            };
                const poiLayers = {
                    fuel: L.layerGroup(),
                    service: L.layerGroup(),
                    lodging: L.layerGroup(),
                };
                let lastPoiFetchBounds = null;
                let poiFetchInFlight = false;

                let map = null;
                let layerStore = {};
                let currentFiltered = loads;
                let loadIndex = {};
                let selectedLoadId = null;
                let connectionMode = 'polling';
                let geoMarker = null;
                let savedViews = [];
                let areaBounds = null;
                let areaRect = null;
                let areaSelecting = false;
                let areaStart = null;
                let cityLabelLayer = null;

                let booted = false;

                const reindex = () => {
                    loadIndex = Object.fromEntries(loads.map((l) => [String(l.id), l]));
                };
                reindex();

                function throttle(fn, wait) {
                    let timeout = null;
                    return (...args) => {
                        if (timeout) return;
                        timeout = setTimeout(() => {
                            timeout = null;
                            fn(...args);
                        }, wait);
                    };
                }

                const initEcho = () => {
                    try {
                        const hasKey = window.Echo?.connector?.pusher?.config?.key;
                        if (!hasKey) return;
                        if (window.Echo && typeof window.Echo.private === 'function') {
                            window.Echo.private('tms-loads').listen('.MapUpdated', () => {
                                fetchLatest(true);
                            });
                            setConnectionMode('realtime');
                        } else if (window.Echo && typeof window.Echo.channel === 'function') {
                            window.Echo.channel('tms-loads').listen('.MapUpdated', () => {
                                fetchLatest(true);
                            });
                            setConnectionMode('realtime');
                        }
                        const conn = window.Echo?.connector?.pusher?.connection;
                        conn?.bind('error', () => setMapAlert('Realtime connection lost. Falling back to polling.', 'error'));
                        conn?.bind('state_change', (s) => {
                            if (s.current !== 'connected') setMapAlert(`Realtime: ${s.current}`, 'error');
                        });
                    } catch (e) {
                        // ignore
                    }
                };

                const setStatusFilter = (statusValue) => {
                    const statusEl = document.getElementById('tms-status-filter');
                    if (statusEl) {
                        statusEl.value = statusValue;
                        applyFilters();
                    }
                };

                const routeCache = new Map();
                const osrmCooldownMs = 1500;
                let osrmInFlight = false;
                let lastOsrmAt = 0;
                const setMapAlert = (message, tone = 'info') => {
                    const el = document.querySelector('.tms-map-alert');
                    if (!el) return;
                    el.textContent = message;
                    el.style.color = tone === 'error' ? '#b91c1c' : '#4b5563';
                };

                const fetchOsrmRoute = async (coordsList) => {
                    if (!coordsList || coordsList.length < 2) return null;
                    const key = coordsList.map((c) => c.join(',')).join(';');
                    if (routeCache.has(key)) return routeCache.get(key);

                    const now = Date.now();
                    if (osrmInFlight || now - lastOsrmAt < osrmCooldownMs) {
                        return null; // throttle to avoid 429s
                    }
                    lastOsrmAt = now;
                    osrmInFlight = true;

                    const url = `https://router.project-osrm.org/route/v1/driving/${coordsList
                        .map(([lat, lng]) => `${lng},${lat}`)
                        .join(';')}?overview=full&geometries=geojson`;
                    try {
                        const res = await fetch(url);
                        if (!res.ok) {
                            throw new Error(`osrm_${res.status}`);
                        }
                        const data = await res.json();
                        const route = data?.routes?.[0];
                        if (!route?.geometry?.coordinates) throw new Error('no coords');
                        const poly = route.geometry.coordinates.map(([lng, lat]) => [lat, lng]);
                        const summary = {
                            distance_km: Math.round(route.distance / 100) / 10,
                            duration_hr: Math.round(route.duration / 36) / 100,
                        };
                        const payload = { poly, summary };
                        routeCache.set(key, payload);
                        return payload;
                    } catch (e) {
                        // Keep straight-line polyline; warn quietly.
                        setMapAlert('Routing service is busy; showing straight lines. Retrying later.', 'error');
                        return null;
                    } finally {
                        osrmInFlight = false;
                    }
                };

                const determineRouteColor = (load) => {
                    if (load.route_status === 'late') return '#ef4444';
                    if (load.route_status === 'at_risk') return '#f59e0b';
                    const hasDetention = (load.sla_flags || []).some((f) => f.toLowerCase().includes('detention'));
                    if (hasDetention) return '#f97316';
                    return statusColorMap[load.status] || '#16a34a';
                };

                const exportCsv = () => {
                    const headers = ['Load #', 'Status', 'Client', 'Carrier', 'Driver', 'Dispatcher', 'Lane', 'Start', 'End'];
                    const rows = currentFiltered.map((l) => [
                        l.load_number || '',
                        l.status || '',
                        l.client || '',
                        l.carrier || '',
                        l.driver || '',
                        l.dispatcher || '',
                        l.lane || '',
                        l.start_date || '',
                        l.end_date || '',
                    ]);
                    const csv = [headers, ...rows]
                        .map((row) => row.map((val) => `"${String(val).replace(/"/g, '""')}"`).join(','))
                        .join('\n');
                    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = 'tms-loads.csv';
                    link.click();
                    URL.revokeObjectURL(url);
                };

                const fetchPoisForView = async () => {
                    const m = ensureMap();
                    if (!m) return;
                    if (poiFetchInFlight) return;
                    if (m.getZoom() < 4) {
                        setMapAlert('Zoom to 4+ to load fuel/service/lodging POIs.');
                        return;
                    }
                    const bounds = m.getBounds();
                    const bbox = [bounds.getSouth(), bounds.getWest(), bounds.getNorth(), bounds.getEast()];
                    const bboxKey = bbox.map((v) => v.toFixed(2)).join(',');
                    if (lastPoiFetchBounds === bboxKey) return;
                    lastPoiFetchBounds = bboxKey;
                    poiFetchInFlight = true;
                    try {
                        const query = `[out:json][timeout:10];(node["amenity"="fuel"](${bbox.join(',')});node["shop"="car_repair"](${bbox.join(',')});node["tourism"="hotel"](${bbox.join(',')}););out center 120;`;
                        const res = await fetch('https://overpass-api.de/api/interpreter', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `data=${encodeURIComponent(query)}`,
                        });
                        const data = await res.json();
                        renderPois(data?.elements || []);
                    } catch (e) {
                        // silent
                    } finally {
                        poiFetchInFlight = false;
                    }
                };

                const renderPois = (elements) => {
                    Object.values(poiLayers).forEach((layer) => layer.clearLayers());
                    elements.forEach((el) => {
                        if (!el.lat || !el.lon) return;
                        const tags = el.tags || {};
                        let kind = null;
                        if (tags.amenity === 'fuel') kind = 'fuel';
                        else if (tags.shop === 'car_repair') kind = 'service';
                        else if (tags.tourism === 'hotel') kind = 'lodging';
                        if (!kind || !poiLayers[kind]) return;
                        const marker = L.marker([el.lat, el.lon], {
                            icon: L.divIcon({
                                className: 'poi-pin',
                                html: `<div style="width:14px;height:14px;border-radius:9999px;background:${stopTypeColors[kind] || '#f97316'};border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,0.3);"></div>`,
                                iconSize: [14, 14],
                            }),
                        }).bindPopup(
                            `<strong>${tags.name || kind}</strong><br/>${tags.brand || ''}<br/><button data-poi-add="${kind}" class="tms-btn" style="margin-top:6px;">Add to selected load</button>`
                        );
                        marker.addTo(poiLayers[kind]);
                        marker.on('popupopen', (e) => {
                            const btn = e?.popup?._contentNode?.querySelector?.('[data-poi-add]');
                            if (btn) {
                                btn.addEventListener('click', () => addPoiAsStop(kind, tags, [el.lat, el.lon]), { once: true });
                            }
                        });
                    });
                    applyPoiVisibility();
                };

                const addPoiAsStop = (kind, tags, coords) => {
                    if (!selectedLoadId) {
                        alert('Select a load card first, then click the POI again.');
                        return;
                    }
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
                    const body = {
                        load_id: selectedLoadId,
                        type: kind,
                        name: tags.name || tags.brand || kind,
                        city: tags['addr:city'] || '',
                        state: tags['addr:state'] || '',
                        lat: coords[0],
                        lng: coords[1],
                    };
                    fetch('/admin/tms-add-stop', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf || '',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(body),
                    })
                        .then(async (res) => {
                            if (!res.ok) {
                                const text = await res.text();
                                throw new Error(text || 'Request failed');
                            }
                            return res.json();
                        })
                        .then(() => {
                            fetchLatest(true);
                            alert('Stop added to load ' + selectedLoadId);
                        })
                        .catch((err) => alert('Could not add stop. Make sure migrations are run and load/stops allow this type. ' + err));
                };

                const applyPoiVisibility = () => {
                    const fuelOn = document.getElementById('tms-poi-fuel')?.checked;
                    const serviceOn = document.getElementById('tms-poi-service')?.checked;
                    const lodgingOn = document.getElementById('tms-poi-lodging')?.checked;
                    toggleLayer(poiLayers.fuel, fuelOn);
                    toggleLayer(poiLayers.service, serviceOn);
                    toggleLayer(poiLayers.lodging, lodgingOn);
                };

                const toggleLayer = (layer, on) => {
                    const m = ensureMap();
                    if (!m || !layer) return;
                    if (on && !m.hasLayer(layer)) {
                        layer.addTo(m);
                    }
                    if (!on && m.hasLayer(layer)) {
                        m.removeLayer(layer);
                    }
                };

                const fetchLatest = async (forceFit = false) => {
                    try {
                        const res = await fetch('/admin/tms-map-data', { headers: { 'Accept': 'application/json' } });
                        if (!res.ok) return;
                        const data = await res.json();
                        if (data?.loads) {
                            refreshData(data.loads, forceFit);
                        }
                    } catch (e) {
                        setMapAlert('Could not refresh map data.', 'error');
                    }
                };

                const copyCoords = async (coords) => {
                    if (!coords) return;
                    const text = `${coords[0].toFixed(5)}, ${coords[1].toFixed(5)}`;
                    try {
                        if (navigator.clipboard?.writeText) {
                            await navigator.clipboard.writeText(text);
                            setMapAlert(`Copied ${text}`);
                        } else {
                            setMapAlert(text);
                        }
                    } catch (_) {
                        setMapAlert(text);
                    }
                };

                const parseLatLng = (text) => {
                    if (!text) return null;
                    const parts = text.split(',').map((p) => p.trim());
                    if (parts.length === 2) {
                        const lat = parseFloat(parts[0]);
                        const lng = parseFloat(parts[1]);
                        if (!isNaN(lat) && !isNaN(lng)) return [lat, lng];
                    }
                    return null;
                };

                const searchLocation = async () => {
                    const query = document.getElementById('tms-location-query')?.value?.trim();
                    const m = ensureMap();
                    if (!m || !query) return;
                    const direct = parseLatLng(query);
                    if (direct) {
                        m.setView(direct, 10);
                        copyCoords(direct);
                        return;
                    }
                    try {
                        const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`, {
                            headers: { 'Accept': 'application/json' },
                        });
                        const data = await res.json();
                        const hit = data?.[0];
                        if (hit?.lat && hit?.lon) {
                            const coords = [parseFloat(hit.lat), parseFloat(hit.lon)];
                            m.setView(coords, 10);
                            copyCoords(coords);
                        } else {
                            setMapAlert('No results for that query.', 'error');
                        }
                    } catch (e) {
                        setMapAlert('Search failed.', 'error');
                    }
                };

                const loadSavedViews = () => {
                    try {
                        savedViews = JSON.parse(localStorage.getItem('tmsSavedViews') || '[]');
                    } catch (_) {
                        savedViews = [];
                    }
                    const select = document.getElementById('tms-bookmark-select');
                    if (!select) return;
                    select.innerHTML = '<option value=\"\">Saved views</option>';
                    savedViews.forEach((view) => {
                        const opt = document.createElement('option');
                        opt.value = view.name;
                        opt.textContent = view.name;
                        select.appendChild(opt);
                    });
                };

                const saveCurrentView = () => {
                    const name = document.getElementById('tms-bookmark-name')?.value?.trim();
                    const m = ensureMap();
                    if (!m || !name) return;
                    const center = m.getCenter();
                    const zoom = m.getZoom();
                    const existingIndex = savedViews.findIndex((v) => v.name === name);
                    const view = { name, lat: center.lat, lng: center.lng, zoom };
                    if (existingIndex >= 0) {
                        savedViews[existingIndex] = view;
                    } else {
                        savedViews.push(view);
                    }
                    localStorage.setItem('tmsSavedViews', JSON.stringify(savedViews));
                    loadSavedViews();
                    setMapAlert(`Saved view "${name}"`);
                };

                const applySavedView = () => {
                    const select = document.getElementById('tms-bookmark-select');
                    const name = select?.value;
                    if (!name) return;
                    const view = savedViews.find((v) => v.name === name);
                    const m = ensureMap();
                    if (!m || !view) return;
                    m.setView([view.lat, view.lng], view.zoom);
                    setMapAlert(`Loaded view "${name}"`);
                };

                const deleteSavedView = () => {
                    const select = document.getElementById('tms-bookmark-select');
                    const name = select?.value;
                    if (!name) return;
                    savedViews = savedViews.filter((v) => v.name !== name);
                    localStorage.setItem('tmsSavedViews', JSON.stringify(savedViews));
                    loadSavedViews();
                    setMapAlert(`Deleted view "${name}"`);
                };

                const assignDispatcher = async (loadId, dispatcherId) => {
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
                    await fetch('/admin/tms-assign-dispatcher', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ load_id: loadId, dispatcher_id: dispatcherId }),
                    });
                    fetchLatest(true);
                };

                const logCheckCall = async (loadId) => {
                    const status = prompt('Enter status (dispatched, en_route, arrived_pickup, loaded, arrived_delivery, unloaded, delayed, issue, check_call):', 'check_call');
                    if (!status) return;
                    const note = prompt('Note (optional):', '');
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
                    await fetch('/admin/tms-check-call', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ load_id: loadId, status, note }),
                    });
                    fetchLatest(true);
                };

                const ensureMap = () => {
                    const mapEl = document.getElementById('tms-map');
                    if (!mapEl || typeof L === 'undefined') return null;
                    if (map) return map;
                    mapEl.dataset.ready = '1';
                    const baseLayers = {
                        'OSM': L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; OpenStreetMap contributors',
                        }),
                        'Carto Light': L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                            maxZoom: 19,
                            attribution: '&copy; OpenStreetMap contributors & Carto',
                        }),
                        'Toner': L.tileLayer('https://stamen-tiles-{s}.a.ssl.fastly.net/toner-lite/{z}/{x}/{y}.png', {
                            maxZoom: 18,
                            attribution: 'Map tiles by Stamen Design, CC BY 3.0 — Map data © OpenStreetMap',
                            subdomains: 'abcd',
                        }),
                        'Streets (Esri)': L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
                            maxZoom: 19,
                            attribution: 'Source: Esri, HERE, Garmin, FAO, NOAA, USGS, © OpenStreetMap contributors',
                        }),
                    };
                    map = L.map(mapEl, {
                        center: [39.5, -98.35],
                        zoom: 4,
                        layers: [baseLayers['OSM']],
                    });
                    L.control.layers(baseLayers, {}).addTo(map);
                    Object.values(poiLayers).forEach((layer) => layer.addTo(map));
                    cityLabelLayer = L.layerGroup().addTo(map);
                    map.on('moveend', throttle(fetchPoisForView, 1500));
                    map.on('click', (e) => {
                        const { lat, lng } = e.latlng;
                        copyCoords([lat, lng]);
                    });
                    document.getElementById('tms-poi-refresh')?.addEventListener('click', () => {
                        lastPoiFetchBounds = null;
                        fetchPoisForView();
                    });
                    return map;
                };

                const clearLayers = () => {
                    Object.values(layerStore).forEach(({ polyline, stopMarkers = [], truckMarker, labelMarker, startMarker, endMarker }) => {
                        if (polyline) polyline.remove();
                        stopMarkers.forEach((m) => m.remove());
                        if (truckMarker) truckMarker.remove();
                        if (labelMarker) labelMarker.remove();
                        if (startMarker) startMarker.remove();
                        if (endMarker) endMarker.remove();
                    });
                    layerStore = {};
                    cityLabelLayer?.clearLayers();
                };

                const renderLayers = (filtered) => {
                    const m = ensureMap();
                    if (!m) return;
                    clearLayers();
                    const bounds = L.latLngBounds();

                    filtered.forEach((load) => {
                        const stopPoints = (load.stops || []).filter((s) => s.coords);
                        const coordsList = load.route_polyline && load.route_polyline.length
                            ? load.route_polyline
                            : stopPoints.map((stop) => stop.coords).filter(Boolean);

                        const stopMarkers = stopPoints.map((stop) => {
                            const color = stopTypeColors[stop.type] || statusColorMap[load.status] || '#2563eb';
                            const marker = L.circleMarker(stop.coords, {
                                radius: 6,
                                color,
                                weight: 2,
                                fillColor: color,
                                fillOpacity: 0.7,
                            }).addTo(m);

                            const labelText = [stop.city, stop.state].filter(Boolean).join(', ') || (stop.type ? stop.type.toUpperCase() : 'Stop');
                            const popupDetails = [
                                `<strong>${load.load_number}</strong>`,
                                load.lane || '',
                                `${load.client || ''} · ${load.carrier || ''}`,
                                load.driver ? `Driver: ${load.driver}` : '',
                                load.dispatcher ? `Disp: ${load.dispatcher}` : '',
                                stop.type ? `Stop: ${stop.type}` : '',
                                stop.city && stop.state ? `${stop.city}, ${stop.state}` : '',
                                load.start_date && load.end_date ? `Window: ${load.start_date} → ${load.end_date}` : '',
                                load.distance_miles ? `Approx: ${load.distance_miles} mi` : '',
                                load.eta_hours ? `ETA: ~${load.eta_hours} hr` : '',
                                `SLA: ${load.route_status || 'on_time'}`,
                                load.notes ? `<em>${load.notes}</em>` : '',
                                load.edit_url ? `<a href="${load.edit_url}" class="text-primary" target="_blank" rel="noreferrer">Open load ↗</a>` : '',
                                `<a href="https://www.google.com/maps?q=&layer=c&cbll=${stop.coords[0]},${stop.coords[1]}" target="_blank" rel="noreferrer">Street View ↗</a>`,
                            ]
                                .filter(Boolean)
                                .join('<br/>');
                            marker.bindPopup(popupDetails);
                            marker.bindTooltip(labelText, { permanent: true, direction: 'top', className: 'tms-stop-label' });
                            marker.on('mouseover', () => highlightLoad(load.id, true));
                            marker.on('mouseout', () => highlightLoad(load.id, false));
                            bounds.extend(stop.coords);
                            return marker;
                        });

                        let polyline = null;
                        if (coordsList.length >= 2) {
                            polyline = L.polyline(coordsList, { color: determineRouteColor(load), weight: 4, opacity: 0.8 })
                                .addTo(m)
                                .bindPopup(
                                    `<strong>${load.load_number}</strong><br/>${load.lane || ''}` +
                                    (load.route_distance_km ? `<br/>~${load.route_distance_km} km` : '') +
                                    (load.route_duration_hr ? ` · ~${load.route_duration_hr} hr` : '')
                                );
                            polyline.on('mouseover', () => highlightLoad(load.id, true));
                            polyline.on('mouseout', () => highlightLoad(load.id, false));
                            bounds.extend(polyline.getBounds());

                            // If no server polyline, asynchronously fetch and update
                            if (!load.route_polyline?.length) {
                                fetchOsrmRoute(coordsList).then((route) => {
                                    if (!route?.poly?.length) return;
                                    const color = determineRouteColor(load);
                                    if (layerStore[load.id]?.polyline) {
                                        layerStore[load.id].polyline.remove();
                                    }
                                    const realLine = L.polyline(route.poly, { color, weight: 4, opacity: 0.85 }).addTo(m);
                                    realLine.on('mouseover', () => highlightLoad(load.id, true));
                                    realLine.on('mouseout', () => highlightLoad(load.id, false));
                                    realLine.bindPopup(
                                        `<strong>${load.load_number}</strong><br/>${load.lane || ''}` +
                                        (route.summary ? `<br/>~${route.summary.distance_km} km · ~${route.summary.duration_hr} hr` : '')
                                    );
                                    layerStore[load.id].polyline = realLine;
                                    if (route.summary) {
                                        layerStore[load.id].summary = route.summary;
                                    }
                                });
                            }
                        }

                        let truckMarker = null;
                        if (load.truck_position) {
                            truckMarker = L.marker(load.truck_position, {
                                icon: L.divIcon({
                                    className: 'truck-pin',
                                    html: '<div style="width:12px;height:12px;border-radius:9999px;background:#f97316;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,0.25);"></div>',
                                    iconSize: [12, 12],
                                }),
                            })
                                .addTo(m)
                                .bindPopup(
                                    `<strong>${load.load_number}</strong><br/>Truck position<br/>${load.lane || ''}` +
                                    (load.notes ? `<br/><em>${load.notes}</em>` : '')
                                );
                            truckMarker.on('mouseover', () => highlightLoad(load.id, true));
                            truckMarker.on('mouseout', () => highlightLoad(load.id, false));
                            bounds.extend(load.truck_position);
                        }

                        // SLA label placed near route center
                        let labelMarker = null;
                        const labelCoords = polyline?.getBounds()?.getCenter() || (stopPoints[Math.floor(stopPoints.length / 2)]?.coords);
                        const status = load.route_status || 'on_time';
                        const statusColor = status === 'late' ? '#ef4444' : status === 'at_risk' ? '#f59e0b' : '#10b981';
                        const statusText = status === 'late' ? 'Late' : status === 'at_risk' ? 'At risk' : 'On time';
                        if (labelCoords) {
                            labelMarker = L.marker(labelCoords, {
                                icon: L.divIcon({
                                    className: 'tms-sla-label',
                                    html: `<span style="border-color:${statusColor};color:${statusColor}">${statusText}</span>`,
                                }),
                                interactive: false,
                            }).addTo(m);
                        }

                        // Explicit start/end markers
                        let startMarker = null;
                        let endMarker = null;
                        if (stopPoints.length) {
                            const first = stopPoints[0];
                            const last = stopPoints[stopPoints.length - 1];
                            if (first?.coords) {
                                const txt = ['Start', [first.city, first.state].filter(Boolean).join(', ')].filter(Boolean).join(' · ');
                                startMarker = L.marker(first.coords, {
                                    icon: L.divIcon({
                                        className: 'tms-sla-label',
                                        html: `<span style="background:#ecfdf3;border-color:#bbf7d0;color:#065f46">${txt}</span>`,
                                    }),
                                    interactive: false,
                                }).addTo(m);
                            }
                            if (last?.coords) {
                                const txt = ['Drop', [last.city, last.state].filter(Boolean).join(', ')].filter(Boolean).join(' · ');
                                endMarker = L.marker(last.coords, {
                                    icon: L.divIcon({
                                        className: 'tms-sla-label',
                                        html: `<span style="background:#fff1f2;border-color:#fecdd3;color:#b91c1c">${txt}</span>`,
                                    }),
                                    interactive: false,
                                }).addTo(m);
                            }
                        }

                        layerStore[load.id] = {
                            polyline,
                            stopMarkers,
                            truckMarker,
                            labelMarker,
                            startMarker,
                            endMarker,
                            bounds: coordsList.length ? L.latLngBounds(coordsList) : null,
                        };
                    });

                    fitAll(bounds);
                };

                const fitAll = (boundsOverride = null) => {
                    const m = ensureMap();
                    if (!m) return;
                    let b = boundsOverride;
                    if (!b || !b.isValid()) {
                        b = L.latLngBounds();
                        Object.values(layerStore).forEach(({ bounds, truckMarker }) => {
                            if (bounds && bounds.isValid()) b.extend(bounds);
                            if (truckMarker) b.extend(truckMarker.getLatLng());
                        });
                    }
                    if (b.isValid()) {
                        m.fitBounds(b.pad(0.2));
                    } else {
                        const center = [39.5, -98.35];
                        m.setView(center, 4);
                    }
                };

                const focusLoad = (loadId) => {
                    const entry = layerStore[loadId];
                    if (!entry) return;
                    const m = ensureMap();
                    if (!m) return;
                    const bounds = entry.bounds || entry.polyline?.getBounds();
                    if (bounds && bounds.isValid()) {
                        m.fitBounds(bounds.pad(0.3), { maxZoom: 10 });
                    }
                    const marker = entry.stopMarkers?.[0] ?? entry.truckMarker;
                    if (marker) marker.openPopup();
                };

                const fitTrucks = () => {
                    const m = ensureMap();
                    if (!m) return;
                    const b = L.latLngBounds();
                    Object.values(layerStore).forEach(({ truckMarker }) => {
                        if (truckMarker) b.extend(truckMarker.getLatLng());
                    });
                    if (b.isValid()) {
                        m.fitBounds(b.pad(0.2));
                    } else {
                        setMapAlert('No truck positions yet.');
                    }
                };

                const locateMe = () => {
                    const m = ensureMap();
                    if (!m || !navigator.geolocation) {
                        setMapAlert('Geolocation not available.');
                        return;
                    }
                    navigator.geolocation.getCurrentPosition(
                        (pos) => {
                            const coords = [pos.coords.latitude, pos.coords.longitude];
                            if (geoMarker) geoMarker.remove();
                            geoMarker = L.marker(coords, {
                                icon: L.divIcon({
                                    className: 'tms-sla-label',
                                    html: `<span style="background:#e0f2fe;border-color:#93c5fd;color:#075985">You</span>`,
                                }),
                                interactive: false,
                            }).addTo(m);
                            m.setView(coords, 10);
                            setMapAlert('Centered on your location.');
                        },
                        () => setMapAlert('Unable to fetch your location.', 'error'),
                        { enableHighAccuracy: false, timeout: 5000 }
                    );
                };

                const applyFilters = () => {
                    const status = document.getElementById('tms-status-filter')?.value || '';
                    const search = (document.getElementById('tms-search')?.value || '').toLowerCase().trim();
                    const dispatcher = document.getElementById('tms-dispatcher-filter')?.value || '';
                    const driver = document.getElementById('tms-driver-filter')?.value || '';
                    const dateStart = document.getElementById('tms-date-start')?.value || '';
                    const dateEnd = document.getElementById('tms-date-end')?.value || '';
                    const noDispatcher = document.getElementById('tms-no-dispatcher')?.checked;
                    const noCcHrs = parseInt(document.getElementById('tms-no-cc-hrs')?.value || '0', 10);
                    const slaSoon = document.getElementById('tms-sla-soon')?.checked;

                    const filtered = loads.filter((load) => {
                        if (status && load.status !== status) return false;
                        if (dispatcher && String(load.dispatcher_id || '') !== dispatcher) return false;
                        if (driver && load.driver !== driver) return false;
                        if (noDispatcher && load.dispatcher) return false;
                        if (areaBounds && !isInArea(load)) return false;

                        if (dateStart) {
                            const loadEnd = load.end_date || load.start_date;
                            if (!loadEnd || loadEnd < dateStart) return false;
                        }
                        if (dateEnd) {
                            const loadStart = load.start_date || load.end_date;
                            if (!loadStart || loadStart > dateEnd) return false;
                        }

                        if (search) {
                            const haystack = [
                                load.load_number,
                                load.client,
                                load.carrier,
                                load.driver,
                                load.dispatcher,
                                load.lane,
                            ]
                                .join(' ')
                                .toLowerCase();
                            if (!haystack.includes(search)) return false;
                        }
                        if (noCcHrs && noCcHrs > 0) {
                            const last = load.last_event_time ? new Date(load.last_event_time) : null;
                            const cutoff = new Date();
                            cutoff.setHours(cutoff.getHours() - noCcHrs);
                            if (last && last > cutoff) return false;
                            // if no last, treat as stale -> keep
                        }
                        if (slaSoon) {
                            const end = load.end_date ? new Date(load.end_date) : null;
                            if (!end) return false;
                            const now = new Date();
                            const soon = new Date();
                            soon.setHours(soon.getHours() + 24);
                            if (end < now || end > soon) return false;
                        }
                        return true;
                    });

                    currentFiltered = filtered;
                    renderLayers(filtered);
                    renderCityLabels(filtered);
                    filterList({ status, search, dispatcher, driver, dateStart, dateEnd });
                    updateStats(filtered.length);
                };

                const filterList = ({ status, search, dispatcher, driver, dateStart, dateEnd }) => {
                    document.querySelectorAll('.tms-load-card').forEach((card) => {
                        const cardStatus = card.dataset.status;
                        const haystack = card.dataset.search || '';
                        const cardDispatcher = card.dataset.dispatcher || '';
                        const cardDriver = card.dataset.driver || '';
                        const cardStart = card.dataset.start || '';
                        const cardEnd = card.dataset.end || '';

                        let ok = true;
                        if (status && cardStatus !== status) ok = false;
                        if (dispatcher && cardDispatcher !== dispatcher) ok = false;
                        if (driver && cardDriver !== driver) ok = false;
                        if (dateStart && cardEnd && cardEnd < dateStart) ok = false;
                        if (dateEnd && cardStart && cardStart > dateEnd) ok = false;
                        if (search && !haystack.includes(search)) ok = false;
                        if (areaBounds) {
                            const load = loadIndex[card.dataset.loadId];
                            if (!isInArea(load)) ok = false;
                        }
                        if (document.getElementById('tms-no-dispatcher')?.checked && cardDispatcher) ok = false;
                        const hrs = parseInt(document.getElementById('tms-no-cc-hrs')?.value || '0', 10);
                        if (hrs && hrs > 0) {
                            const load = loadIndex[card.dataset.loadId];
                            const last = load?.last_event_time ? new Date(load.last_event_time) : null;
                            const cutoff = new Date();
                            cutoff.setHours(cutoff.getHours() - hrs);
                            if (last && last > cutoff) ok = false;
                        }
                        if (document.getElementById('tms-sla-soon')?.checked) {
                            const load = loadIndex[card.dataset.loadId];
                            const end = load?.end_date ? new Date(load.end_date) : null;
                            if (!end) ok = false;
                            const now = new Date();
                            const soon = new Date();
                            soon.setHours(soon.getHours() + 24);
                            if (end && (end < now || end > soon)) ok = false;
                        }

                        card.classList.toggle('hidden', !ok);
                    });
                };

                const updateStats = (filteredCount) => {
                    const total = loads.length;
                    const atRisk = loads.filter((l) => l.route_status === 'at_risk').length;
                    const late = loads.filter((l) => l.route_status === 'late').length;
                    const inTransit = loads.filter((l) => l.status === 'in_transit').length;
                    const text = (id, value) => {
                        const el = document.getElementById(id);
                        if (el) el.textContent = value;
                    };
                    text('tms-stat-total', `Total: ${total}`);
                    text('tms-stat-at-risk', `At risk: ${atRisk}`);
                    text('tms-stat-late', `Late: ${late}`);
                    text('tms-stat-intransit', `In transit: ${inTransit}`);
                    text('tms-stat-filtered', `Filtered: ${filteredCount}`);
                    text('tms-issues-late', `Late: ${late}`);
                    text('tms-issues-atrisk', `At risk: ${atRisk}`);
                    const upd = document.getElementById('tms-updated-at');
                    if (upd) upd.textContent = `Last updated: ${new Date().toLocaleTimeString()}`;
                };

                const setConnectionMode = (mode) => {
                    connectionMode = mode;
                    const el = document.getElementById('tms-conn-chip');
                    if (!el) return;
                    el.textContent = mode === 'realtime' ? 'Mode: realtime' : 'Mode: polling';
                    el.classList.remove('good', 'muted');
                    el.classList.add(mode === 'realtime' ? 'good' : 'muted');
                };

                const isInArea = (load) => {
                    if (!areaBounds || !load) return true;
                    const stops = load.stops || [];
                    const anyStop = stops.some((s) => s.coords && areaBounds.contains(L.latLng(s.coords[0], s.coords[1])));
                    const truck = load.truck_position && areaBounds.contains(L.latLng(load.truck_position[0], load.truck_position[1]));
                    return anyStop || truck;
                };

                const jumpToIssue = (status) => {
                    const target = currentFiltered.find((l) => l.route_status === status);
                    if (!target) {
                        setMapAlert(`No ${status === 'late' ? 'late' : 'at risk'} loads right now.`);
                        return;
                    }
                    focusLoad(target.id);
                    const card = document.querySelector(`.tms-load-card[data-load-id="${target.id}"]`);
                    if (card) {
                        document.querySelectorAll('.tms-load-card').forEach((c) => c.classList.remove('tms-highlight'));
                        card.classList.add('tms-highlight');
                        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                };

                const renderCityLabels = (filtered) => {
                    if (!cityLabelLayer) return;
                    cityLabelLayer.clearLayers();
                    const seen = new Set();
                    filtered.forEach((load) => {
                        (load.stops || []).forEach((stop) => {
                            if (!stop.coords || !stop.city) return;
                            const key = `${stop.city},${stop.state || ''}`.toLowerCase();
                            if (seen.has(key)) return;
                            seen.add(key);
                            cityLabelLayer.addLayer(L.marker(stop.coords, {
                                icon: L.divIcon({
                                    className: 'tms-city-label',
                                    html: `<span>${stop.city}${stop.state ? ', ' + stop.state : ''}</span>`,
                                }),
                                interactive: false,
                            }));
                        });
                    });
                };

                const highlightLoad = (loadId, active) => {
                    const entry = layerStore[loadId];
                    const load = loadIndex[String(loadId)];
                    const color = load ? statusColorMap[load.status] || '#2563eb' : '#2563eb';
                    if (entry?.polyline) entry.polyline.setStyle({ weight: active ? 5 : 3, opacity: active ? 0.9 : 0.7 });
                    entry?.stopMarkers?.forEach((m) => m.setStyle({ weight: active ? 3 : 2, radius: active ? 7 : 6, color }));
                    if (entry?.truckMarker) entry.truckMarker.setZIndexOffset(active ? 999 : 0);
                    const card = document.querySelector(`.tms-load-card[data-load-id="${loadId}"]`);
                    if (card) card.classList.toggle('tms-highlight', !!active);
                };

                const attachEvents = () => {
                    const searchEl = document.getElementById('tms-search');
                    const statusEl = document.getElementById('tms-status-filter');
                    const fitEl = document.getElementById('tms-fit-all');
                    const fitSelectedEl = document.getElementById('tms-fit-selected');
                    const clearSelectionEl = document.getElementById('tms-clear-selection');
                    const refreshEl = document.getElementById('tms-refresh');
                    const exportEl = document.getElementById('tms-export');
                    const printEl = document.getElementById('tms-print');
                    const dispatcherEl = document.getElementById('tms-dispatcher-filter');
                    const driverEl = document.getElementById('tms-driver-filter');
                    const dateStartEl = document.getElementById('tms-date-start');
                    const dateEndEl = document.getElementById('tms-date-end');
                    const legendToggle = document.querySelector('[data-target="tms-legend-body"]');
                    const poiFuelEl = document.getElementById('tms-poi-fuel');
                    const poiServiceEl = document.getElementById('tms-poi-service');
                    const poiLodgingEl = document.getElementById('tms-poi-lodging');
                    const lateEl = document.getElementById('tms-late-only');
                    const atRiskEl = document.getElementById('tms-atrisk-only');
                    const showAllEl = document.getElementById('tms-show-all');
                    const fitTrucksEl = document.getElementById('tms-fit-trucks');
                    const locateEl = document.getElementById('tms-locate');
                    const locationSearchEl = document.getElementById('tms-location-search');
                    const locationQueryEl = document.getElementById('tms-location-query');
                    const bookmarkSaveEl = document.getElementById('tms-bookmark-save');
                    const bookmarkLoadEl = document.getElementById('tms-bookmark-load');
                    const bookmarkDeleteEl = document.getElementById('tms-bookmark-delete');
                    const areaSelectEl = document.getElementById('tms-area-select');
                    const areaClearEl = document.getElementById('tms-area-clear');
                    const jumpLateEl = document.getElementById('tms-jump-late');
                    const jumpAtRiskEl = document.getElementById('tms-jump-atrisk');
                    const noDispatcherEl = document.getElementById('tms-no-dispatcher');
                    const noCcEl = document.getElementById('tms-no-cc-hrs');
                    const slaSoonEl = document.getElementById('tms-sla-soon');

                    searchEl?.addEventListener('input', () => applyFilters());
                    statusEl?.addEventListener('change', () => applyFilters());
                    dispatcherEl?.addEventListener('change', () => applyFilters());
                    driverEl?.addEventListener('change', () => applyFilters());
                    dateStartEl?.addEventListener('change', () => applyFilters());
                    dateEndEl?.addEventListener('change', () => applyFilters());
                    noDispatcherEl?.addEventListener('change', () => applyFilters());
                    noCcEl?.addEventListener('input', () => applyFilters());
                    slaSoonEl?.addEventListener('change', () => applyFilters());
                    fitEl?.addEventListener('click', () => fitAll());
                    fitSelectedEl?.addEventListener('click', () => {
                        const selected = document.querySelector('.tms-load-card.tms-highlight');
                        if (selected) focusLoad(selected.dataset.loadId);
                    });
                    clearSelectionEl?.addEventListener('click', () => {
                        document.querySelectorAll('.tms-load-card').forEach((c) => c.classList.remove('tms-highlight'));
                        fitAll();
                    });
                    refreshEl?.addEventListener('click', () => fetchLatest(true));
                    exportEl?.addEventListener('click', () => exportCsv());
                    printEl?.addEventListener('click', () => window.print());
                    legendToggle?.addEventListener('click', () => {
                        const target = document.getElementById(legendToggle.dataset.target);
                        target?.classList.toggle('tms-hidden');
                    });
                    poiFuelEl?.addEventListener('change', applyPoiVisibility);
                    poiServiceEl?.addEventListener('change', applyPoiVisibility);
                    poiLodgingEl?.addEventListener('change', applyPoiVisibility);
                    lateEl?.addEventListener('click', () => setStatusFilter('late'));
                    atRiskEl?.addEventListener('click', () => setStatusFilter('at_risk'));
                    showAllEl?.addEventListener('click', () => setStatusFilter(''));
                    fitTrucksEl?.addEventListener('click', () => fitTrucks());
                    locateEl?.addEventListener('click', () => locateMe());
                    locationSearchEl?.addEventListener('click', () => searchLocation());
                    locationQueryEl?.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            searchLocation();
                        }
                    });
                    bookmarkSaveEl?.addEventListener('click', () => saveCurrentView());
                    bookmarkLoadEl?.addEventListener('click', () => applySavedView());
                    bookmarkDeleteEl?.addEventListener('click', () => deleteSavedView());
                    areaSelectEl?.addEventListener('click', () => startAreaSelection());
                    areaClearEl?.addEventListener('click', () => clearAreaFilter());
                    jumpLateEl?.addEventListener('click', () => jumpToIssue('late'));
                    jumpAtRiskEl?.addEventListener('click', () => jumpToIssue('at_risk'));

                    document.querySelectorAll('.tms-load-card').forEach((card) => {
                        card.addEventListener('click', () => {
                            document.querySelectorAll('.tms-load-card').forEach((c) => c.classList.remove('tms-highlight'));
                            card.classList.add('tms-highlight');
                            selectedLoadId = card.dataset.loadId;
                            focusLoad(card.dataset.loadId);
                        });
                        card.addEventListener('mouseenter', () => highlightLoad(card.dataset.loadId, true));
                        card.addEventListener('mouseleave', () => highlightLoad(card.dataset.loadId, false));

                        const assignMeBtn = card.querySelector('[data-assign-me]');
                        const assignBtn = card.querySelector('[data-assign-selected]');
                        const unassignBtn = card.querySelector('[data-unassign]');
                        const ccBtn = card.querySelector('[data-check-call]');
                        const dispatcherPick = card.querySelector('[data-dispatcher-pick]');

                        assignMeBtn?.addEventListener('click', (e) => {
                            e.stopPropagation();
                            if (!currentUserId) {
                                alert('No current user to assign.');
                                return;
                            }
                            assignDispatcher(card.dataset.loadId, currentUserId);
                        });
                        assignBtn?.addEventListener('click', (e) => {
                            e.stopPropagation();
                            const val = dispatcherPick?.value;
                            if (!val) {
                                alert('Pick a dispatcher first.');
                                return;
                            }
                            assignDispatcher(card.dataset.loadId, val);
                        });
                        unassignBtn?.addEventListener('click', (e) => {
                            e.stopPropagation();
                            assignDispatcher(card.dataset.loadId, null);
                        });
                        ccBtn?.addEventListener('click', (e) => {
                            e.stopPropagation();
                            logCheckCall(card.dataset.loadId);
                        });
                    });
                };

                const boot = () => {
                    if (booted) return;
                    booted = true;
                    queueMicrotask(() => {
                        ensureMap();
                        loadSavedViews();
                        attachEvents();
                        applyFilters();
                        initEcho();
                        setInterval(() => fetchLatest(), 30000);
                        setConnectionMode(connectionMode);
                    });
                };

                const refreshData = (newLoads, forceFit = false) => {
                    if (!Array.isArray(newLoads)) return;
                    loads = newLoads;
                    currentFiltered = loads;
                    reindex();
                    applyFilters();
                    if (forceFit) fitAll();
                };

                window.addEventListener('tms-map-data', (event) => {
                    if (event?.detail?.loads) refreshData(event.detail.loads, true);
                });

                const startAreaSelection = () => {
                    const m = ensureMap();
                    if (!m) return;
                    if (areaSelecting) return;
                    areaSelecting = true;
                    areaStart = null;
                    setMapAlert('Click and drag to draw an area.');
                    m.dragging.disable();

                    const onDown = (e) => {
                        areaStart = e.latlng;
                        if (areaRect) {
                            m.removeLayer(areaRect);
                            areaRect = null;
                        }
                    };

                    const onMove = (e) => {
                        if (!areaStart) return;
                        const b = L.latLngBounds(areaStart, e.latlng);
                        if (!areaRect) {
                            areaRect = L.rectangle(b, { color: '#2563eb', weight: 1, fillOpacity: 0.06 }).addTo(m);
                        } else {
                            areaRect.setBounds(b);
                        }
                    };

                    const onUp = (e) => {
                        if (!areaStart) {
                            cleanup();
                            return;
                        }
                        const b = L.latLngBounds(areaStart, e.latlng);
                        areaBounds = b;
                        if (!areaRect) {
                            areaRect = L.rectangle(b, { color: '#2563eb', weight: 1, fillOpacity: 0.06 }).addTo(m);
                        }
                        cleanup();
                        applyFilters();
                        setMapAlert('Area filter applied.');
                    };

                    const cleanup = () => {
                        areaSelecting = false;
                        areaStart = null;
                        m.dragging.enable();
                        m.off('mousedown', onDown);
                        m.off('mousemove', onMove);
                        m.off('mouseup', onUp);
                    };

                    m.on('mousedown', onDown);
                    m.on('mousemove', onMove);
                    m.on('mouseup', onUp);
                };

                const clearAreaFilter = () => {
                    const m = ensureMap();
                    areaBounds = null;
                    areaSelecting = false;
                    areaStart = null;
                    if (areaRect && m) {
                        m.removeLayer(areaRect);
                    }
                    areaRect = null;
                    if (m) m.dragging.enable();
                    applyFilters();
                    setMapAlert('Area filter cleared.');
                };

                return { boot, refreshData };
            })();

            document.addEventListener('DOMContentLoaded', () => TmsMap.boot());
            document.addEventListener('livewire:init', () => TmsMap.boot());
            document.addEventListener('livewire:navigated', () => TmsMap.boot());
        </script>
    @endpush
@endonce

<x-filament::page>
    <x-filament::section heading="TMS Overview" description="Realtime snapshot for loads, statuses, and legend.">
        <div class="space-y-4">
        <div class="tms-grid tms-grid-3">
            <div class="tms-card">
                <div class="text-xs uppercase tracking-[0.12em] text-gray-500 dark:text-gray-400">Loads</div>
                <div class="mt-1 text-2xl font-semibold">{{ count($loads) }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Visible on the map</div>
            </div>
            <div class="tms-card">
                <div class="text-xs uppercase tracking-[0.12em] text-gray-500 dark:text-gray-400">Statuses</div>
                <div class="mt-1 text-xs flex flex-wrap gap-2">
                    @foreach ($statusColors as $status => $color)
                        <span class="inline-flex items-center rounded-full px-2 py-1 {{ $color }} text-[11px]">
                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                        </span>
                    @endforeach
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pins/lines use status colors.</div>
            </div>
            <x-filament::section class="!p-3">
                <div class="flex items-center justify-between">
                    <div class="text-xs uppercase tracking-[0.12em] text-gray-500 dark:text-gray-400">Legend</div>
                    <button type="button" class="tms-legend-toggle text-xs text-primary" data-target="tms-legend-body">Hide/Show</button>
                </div>
                <div id="tms-legend-body" class="mt-2 space-y-2 text-xs text-gray-600 dark:text-gray-300">
                    <div class="flex items-start gap-2">
                        <span class="w-3 h-3 rounded-full bg-sky-500 inline-block mt-0.5"></span>
                        <div>
                            <div class="font-semibold">Stops</div>
                            <div class="text-[11px] text-gray-500">Pickup and delivery pins; hover for city/state.</div>
                        </div>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="w-4 h-[2px] bg-emerald-600 inline-block mt-2"></span>
                        <div>
                            <div class="font-semibold">Lane polyline</div>
                            <div class="text-[11px] text-gray-500">Planned route between first and last stops.</div>
                        </div>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="w-3 h-3 rounded-full bg-orange-500 inline-block border border-white shadow mt-0.5"></span>
                        <div>
                            <div class="font-semibold">Truck position</div>
                            <div class="text-[11px] text-gray-500">Approximate truck location based on status.</div>
                        </div>
                    </div>
                </div>
            </x-filament::section>
        </div>

        <div class="tms-status-bar">
            <span class="tms-chip good" id="tms-stat-total">Total: {{ count($loads) }}</span>
            <span class="tms-chip warn" id="tms-stat-at-risk">At risk: 0</span>
            <span class="tms-chip bad" id="tms-stat-late">Late: 0</span>
            <span class="tms-chip muted" id="tms-stat-intransit">In transit: 0</span>
            <span class="tms-chip muted" id="tms-stat-filtered">Filtered: {{ count($loads) }}</span>
            <span class="tms-chip muted" id="tms-conn-chip">Mode: polling</span>
            <span class="text-xs text-gray-500" id="tms-updated-at">Last updated: just now</span>
        </div>

        <div class="tms-issues">
            <span class="font-semibold text-sm">Top issues</span>
            <span id="tms-issues-late">Late: 0</span>
            <x-filament::icon-button id="tms-jump-late" icon="heroicon-m-arrow-down-circle" size="sm" label="Jump to late" />
            <span id="tms-issues-atrisk">At risk: 0</span>
            <x-filament::icon-button id="tms-jump-atrisk" icon="heroicon-m-exclamation-circle" size="sm" label="Jump to at risk" color="warning" />
        </div>

        </div>
    </x-filament::section>

    <x-filament::section heading="Filters & Tools" description="Narrow loads, manage POIs, and apply quick actions.">
        <div class="tms-filter-bar space-y-2">
            <div class="flex flex-wrap gap-2 items-center">
                <label class="text-xs text-gray-500 dark:text-gray-400">Status</label>
                <select id="tms-status-filter" class="rounded-lg border px-2 py-1 text-sm bg-white dark:bg-slate-800">
                    <option value="">All</option>
                    @foreach ($statusOptions as $status)
                        <option value="{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
                <label class="text-xs text-gray-500 dark:text-gray-400 ml-2">Dispatcher</label>
                <select id="tms-dispatcher-filter" class="rounded-lg border px-2 py-1 text-sm bg-white dark:bg-slate-800">
                    <option value="">All</option>
                    @foreach ($dispatchers as $dispatcher)
                        <option value="{{ $dispatcher['id'] }}">{{ $dispatcher['name'] }}</option>
                    @endforeach
                </select>
                <label class="text-xs text-gray-500 dark:text-gray-400 ml-2">Driver</label>
                <select id="tms-driver-filter" class="rounded-lg border px-2 py-1 text-sm bg-white dark:bg-slate-800">
                    <option value="">All</option>
                    @foreach ($drivers as $driver)
                        <option value="{{ $driver }}">{{ $driver }}</option>
                    @endforeach
                </select>
                <label class="text-xs text-gray-500 dark:text-gray-400 ml-2">Search</label>
                <input id="tms-search" type="text" class="rounded-lg border px-3 py-1 text-sm bg-white dark:bg-slate-800" placeholder="Load #, client, carrier, lane">
                <label class="text-xs text-gray-500 dark:text-gray-400 ml-2">Date from</label>
                <input id="tms-date-start" type="date" class="rounded-lg border px-3 py-1 text-sm bg-white dark:bg-slate-800">
                <label class="text-xs text-gray-500 dark:text-gray-400 ml-2">to</label>
                <input id="tms-date-end" type="date" class="rounded-lg border px-3 py-1 text-sm bg-white dark:bg-slate-800">
            </div>

            <details class="w-full bg-white dark:bg-slate-900 rounded-lg border px-3 py-2">
                <summary class="cursor-pointer text-xs text-gray-600 dark:text-gray-300">Advanced filters & tools</summary>
                <div class="flex flex-wrap gap-2 items-center mt-2">
                    <label class="inline-flex items-center gap-1 text-xs text-gray-600 dark:text-gray-300">
                        <input type="checkbox" id="tms-poi-fuel" class="rounded border-gray-300" checked> Fuel
                    </label>
                    <label class="inline-flex items-center gap-1 text-xs text-gray-600 dark:text-gray-300">
                        <input type="checkbox" id="tms-poi-service" class="rounded border-gray-300" checked> Service
                    </label>
                    <label class="inline-flex items-center gap-1 text-xs text-gray-600 dark:text-gray-300">
                        <input type="checkbox" id="tms-poi-lodging" class="rounded border-gray-300" checked> Lodging
                    </label>
                    <button id="tms-poi-refresh" type="button" class="tms-btn" title="Refresh POIs">Refresh POIs</button>
                    <span class="text-[11px] text-gray-500">Zoom to 4+ to fetch POIs</span>
                    <label class="inline-flex items-center gap-1 text-xs text-gray-600 dark:text-gray-300 ml-2">
                        <input type="checkbox" id="tms-no-dispatcher" class="rounded border-gray-300"> Unassigned dispatcher
                    </label>
                    <label class="text-xs text-gray-500 dark:text-gray-400 ml-2">No check-call (hrs)</label>
                    <input id="tms-no-cc-hrs" type="number" min="1" step="1" class="rounded-lg border px-3 py-1 text-sm bg-white dark:bg-slate-800 w-20" placeholder="24">
                    <label class="inline-flex items-center gap-1 text-xs text-gray-600 dark:text-gray-300 ml-2">
                        <input type="checkbox" id="tms-sla-soon" class="rounded border-gray-300"> Window within 24h
                    </label>
                    <div class="flex flex-wrap gap-2 ml-auto">
                        <button id="tms-late-only" type="button" class="tms-btn">Late</button>
                        <button id="tms-atrisk-only" type="button" class="tms-btn">At risk</button>
                        <button id="tms-show-all" type="button" class="tms-btn">All</button>
                    </div>
                </div>
            </details>

            <div class="flex flex-wrap gap-2 items-center w-full">
                <label class="text-xs text-gray-500 dark:text-gray-400">Jump to</label>
                <input id="tms-location-query" type="text" class="rounded-lg border px-3 py-1 text-sm bg-white dark:bg-slate-800" placeholder="City, address, or lat,lng">
                <x-filament::icon-button id="tms-location-search" icon="heroicon-m-magnifying-glass" size="sm" label="Go" />
                <label class="text-xs text-gray-500 dark:text-gray-400 ml-4">Bookmark view</label>
                <input id="tms-bookmark-name" type="text" class="rounded-lg border px-3 py-1 text-sm bg-white dark:bg-slate-800" placeholder="Name">
                <x-filament::icon-button id="tms-bookmark-save" icon="heroicon-m-bookmark" size="sm" label="Save view" />
                <select id="tms-bookmark-select" class="rounded-lg border px-2 py-1 text-sm bg-white dark:bg-slate-800">
                    <option value="">Saved views</option>
                </select>
                <x-filament::icon-button id="tms-bookmark-load" icon="heroicon-m-play" size="sm" label="Load" />
                <x-filament::icon-button id="tms-bookmark-delete" icon="heroicon-m-trash" size="sm" color="danger" label="Delete" />
            </div>

            <div class="flex gap-2 flex-wrap w-full">
                <x-filament::icon-button id="tms-fit-trucks" icon="heroicon-m-arrows-pointing-out" size="sm" label="Fit trucks" />
                <x-filament::icon-button id="tms-locate" icon="heroicon-m-map-pin" size="sm" label="Locate me" />
                <x-filament::icon-button id="tms-area-select" icon="heroicon-m-rectangle-group" size="sm" label="Area select" />
                <x-filament::icon-button id="tms-area-clear" icon="heroicon-m-x-mark" size="sm" label="Clear area" color="gray" />
                <x-filament::icon-button id="tms-clear-selection" icon="heroicon-m-backspace" size="sm" label="Clear selection" color="gray" />
                <x-filament::icon-button id="tms-fit-selected" icon="heroicon-m-arrows-pointing-out" size="sm" label="Fit selected" />
                <x-filament::icon-button id="tms-fit-all" icon="heroicon-m-arrows-pointing-out" size="sm" label="Fit all" />
                <x-filament::icon-button id="tms-refresh" icon="heroicon-m-arrow-path" size="sm" color="primary" label="Refresh map" />
                <x-filament::icon-button id="tms-export" icon="heroicon-m-arrow-down-tray" size="sm" label="Export CSV" />
                <x-filament::icon-button id="tms-print" icon="heroicon-m-printer" size="sm" label="Print" />
            </div>
        </div>

    </x-filament::section>

    <x-filament::section heading="Map & Loads" description="Select a load to focus and manage dispatch actions.">
        <div class="tms-split">
            <div class="tms-card overflow-hidden">
                <div class="border-b px-4 py-3 flex items-center justify-between">
                    <div class="text-sm font-semibold">Route map</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Leaflet + OSM</div>
                </div>
                <div class="relative">
                    <div id="tms-map" class="w-full" style="height:520px;min-height:520px" wire:ignore></div>
                    <div class="tms-map-alert text-gray-600 dark:text-gray-300">
                        Select a load card, then click a fuel/service/lodging POI to add it as a stop.
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <div class="tms-card p-3">
                    <div class="text-sm font-semibold mb-2">Loads</div>
                    <div class="space-y-2 tms-load-list" id="tms-load-list">
                        @forelse ($loads as $load)
                            @php
                                $searchHaystack = strtolower(
                                    implode(' ', [
                                        $load['load_number'],
                                        $load['client'],
                                        $load['carrier'],
                                        $load['driver'] ?? '',
                                        $load['dispatcher'] ?? '',
                                        $load['lane'] ?? '',
                                    ])
                                );
                            @endphp
                            <div
                                class="tms-load-card rounded-xl border bg-gray-50 dark:bg-slate-800/80 p-3 space-y-2 cursor-pointer hover:border-primary hover:shadow"
                                data-load-id="{{ $load['id'] }}"
                                data-status="{{ $load['status'] }}"
                                data-search="{{ $searchHaystack }}"
                                data-dispatcher="{{ $load['dispatcher_id'] ?? '' }}"
                                data-driver="{{ $load['driver'] ?? '' }}"
                                data-start="{{ $load['start_date'] ?? '' }}"
                                data-end="{{ $load['end_date'] ?? '' }}"
                            >
                                <div class="flex items-center justify-between gap-2">
                                    <div>
                                        <div class="text-sm font-semibold">{{ $load['load_number'] }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-300">
                                            {{ $load['client'] ?? 'Client' }} · {{ $load['carrier'] ?? 'Carrier' }}
                                        </div>
                                <div class="text-xs text-gray-500 dark:text-gray-300">{{ $load['lane'] ?? '—' }}</div>
                                <div class="text-[11px] text-gray-500 dark:text-gray-400">
                                    Driver: {{ $load['driver'] ?? '—' }} · Disp: {{ $load['dispatcher'] ?? '—' }}
                                    @if (!empty($load['start_date']) || !empty($load['end_date']))
                                        · {{ $load['start_date'] ?? '—' }} → {{ $load['end_date'] ?? '—' }}
                                    @endif
                                    @if (!empty($load['distance_miles']) || !empty($load['eta_hours']))
                                        · {{ $load['distance_miles'] ? $load['distance_miles'].' mi' : '' }} {{ $load['eta_hours'] ? 'ETA ~'.$load['eta_hours'].'h' : '' }}
                                    @endif
                                    @if (!empty($load['last_event']))
                                        · Last: {{ $load['last_event'] }} @ {{ $load['last_event_time'] ?? '' }}
                                    @endif
                                </div>
                                    </div>
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold {{ $statusColors[$load['status']] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst(str_replace('_', ' ', $load['status'])) }}
                                    </span>
                                </div>
                                <div class="flex flex-wrap gap-2 text-[11px] font-semibold">
                                    @if (!empty($load['route_status']) && $load['route_status'] !== 'on_time')
                                        <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-red-700">
                                            {{ $load['route_status'] === 'late' ? 'Late risk' : 'At risk (window near)' }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-emerald-700">
                                            On time
                                        </span>
                                    @endif
                                    @if (!empty($load['accessorial_total']))
                                        <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-amber-800">
                                            Accessorials ${{ number_format($load['accessorial_total'], 2) }}
                                        </span>
                                    @endif
                                    @if (!empty($load['sla_flags']))
                                        @foreach ($load['sla_flags'] as $flag)
                                            <span class="inline-flex items-center rounded-full bg-red-50 px-2 py-0.5 text-red-700 border border-red-200">
                                                {{ $flag }}
                                            </span>
                                        @endforeach
                                    @endif
                                </div>
                                <div class="flex flex-wrap gap-2 text-[11px] text-gray-600 dark:text-gray-300">
                                    @forelse ($load['stops'] as $stop)
                                        <span class="inline-flex items-center rounded-full bg-white dark:bg-slate-700 px-2 py-1 border">
                                            {{ strtoupper($stop['type'] ?? '') }} · {{ $stop['city'] ?? '—' }}, {{ $stop['state'] ?? '' }}
                                        </span>
                                        @if(($stop['type'] ?? '') === 'pickup' && !empty($load['detention_pickup_minutes']))
                                            <span class="inline-flex items-center rounded-full bg-red-50 px-2 py-0.5 text-red-700 border border-red-200">
                                                Detention {{ $load['detention_pickup_hours'] ?? round($load['detention_pickup_minutes']/60,2) }}h
                                            </span>
                                        @endif
                                        @if(($stop['type'] ?? '') === 'delivery' && !empty($load['detention_delivery_minutes']))
                                            <span class="inline-flex items-center rounded-full bg-red-50 px-2 py-0.5 text-red-700 border border-red-200">
                                                Detention {{ $load['detention_delivery_hours'] ?? round($load['detention_delivery_minutes']/60,2) }}h
                                            </span>
                                        @endif
                                    @empty
                                        <span class="text-xs text-gray-500">No stops</span>
                                    @endforelse
                                </div>
                                @if (!empty($load['notes']))
                                    <div class="text-[11px] text-gray-500 dark:text-gray-300 italic">{{ $load['notes'] }}</div>
                                @endif
                                @if (!empty($load['edit_url']))
                                    <div>
                                        <a href="{{ $load['edit_url'] }}" target="_blank" rel="noreferrer" class="text-xs text-primary">Open load ↗</a>
                                    </div>
                                @endif
                                <div class="flex flex-wrap gap-2 items-center text-[11px] mt-2">
                                    <select class="tms-mini-select" data-dispatcher-pick>
                                        <option value="">Pick dispatcher</option>
                                        @foreach ($dispatchers as $dispatcher)
                                            <option value="{{ $dispatcher['id'] }}">{{ $dispatcher['name'] }}</option>
                                        @endforeach
                                    </select>
                                    <button class="tms-mini-btn" data-assign-me>Assign me</button>
                                    <button class="tms-mini-btn" data-assign-selected>Assign</button>
                                    <button class="tms-mini-btn" data-unassign>Unassign</button>
                                    <button class="tms-mini-btn" data-check-call>Log check-call</button>
                                </div>
                            </div>
                        @empty
                            <div class="text-sm text-gray-500">No loads available for map view.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament::page>
