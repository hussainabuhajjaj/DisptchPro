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
                        } else if (window.Echo && typeof window.Echo.channel === 'function') {
                            window.Echo.channel('tms-loads').listen('.MapUpdated', () => {
                                fetchLatest(true);
                            });
                        }
                    } catch (e) {
                        // ignore
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
                        // silent fail
                    }
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
                    };
                    map = L.map(mapEl, {
                        center: [39.5, -98.35],
                        zoom: 4,
                        layers: [baseLayers['OSM']],
                    });
                    L.control.layers(baseLayers, {}).addTo(map);
                    Object.values(poiLayers).forEach((layer) => layer.addTo(map));
                    map.on('moveend', throttle(fetchPoisForView, 1500));
                    document.getElementById('tms-poi-refresh')?.addEventListener('click', () => {
                        lastPoiFetchBounds = null;
                        fetchPoisForView();
                    });
                    return map;
                };

                const clearLayers = () => {
                    Object.values(layerStore).forEach(({ polyline, stopMarkers = [], truckMarker }) => {
                        if (polyline) polyline.remove();
                        stopMarkers.forEach((m) => m.remove());
                        if (truckMarker) truckMarker.remove();
                    });
                    layerStore = {};
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

                        layerStore[load.id] = {
                            polyline,
                            stopMarkers,
                            truckMarker,
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

                const applyFilters = () => {
                    const status = document.getElementById('tms-status-filter')?.value || '';
                    const search = (document.getElementById('tms-search')?.value || '').toLowerCase().trim();
                    const dispatcher = document.getElementById('tms-dispatcher-filter')?.value || '';
                    const driver = document.getElementById('tms-driver-filter')?.value || '';
                    const dateStart = document.getElementById('tms-date-start')?.value || '';
                    const dateEnd = document.getElementById('tms-date-end')?.value || '';

                    const filtered = loads.filter((load) => {
                        if (status && load.status !== status) return false;
                        if (dispatcher && load.dispatcher !== dispatcher) return false;
                        if (driver && load.driver !== driver) return false;

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
                        return true;
                    });

                    currentFiltered = filtered;
                    renderLayers(filtered);
                    filterList({ status, search, dispatcher, driver, dateStart, dateEnd });
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

                        card.classList.toggle('hidden', !ok);
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

                    searchEl?.addEventListener('input', () => applyFilters());
                    statusEl?.addEventListener('change', () => applyFilters());
                    dispatcherEl?.addEventListener('change', () => applyFilters());
                    driverEl?.addEventListener('change', () => applyFilters());
                    dateStartEl?.addEventListener('change', () => applyFilters());
                    dateEndEl?.addEventListener('change', () => applyFilters());
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

                    document.querySelectorAll('.tms-load-card').forEach((card) => {
                        card.addEventListener('click', () => {
                            document.querySelectorAll('.tms-load-card').forEach((c) => c.classList.remove('tms-highlight'));
                            card.classList.add('tms-highlight');
                            selectedLoadId = card.dataset.loadId;
                            focusLoad(card.dataset.loadId);
                        });
                        card.addEventListener('mouseenter', () => highlightLoad(card.dataset.loadId, true));
                        card.addEventListener('mouseleave', () => highlightLoad(card.dataset.loadId, false));
                    });
                };

                const boot = () => {
                    if (booted) return;
                    booted = true;
                    queueMicrotask(() => {
                        ensureMap();
                        attachEvents();
                        applyFilters();
                        initEcho();
                        setInterval(() => fetchLatest(), 30000);
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

                return { boot, refreshData };
            })();

            document.addEventListener('DOMContentLoaded', () => TmsMap.boot());
            document.addEventListener('livewire:init', () => TmsMap.boot());
            document.addEventListener('livewire:navigated', () => TmsMap.boot());
        </script>
    @endpush
@endonce

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
    $dispatchers = collect($loads)->pluck('dispatcher')->filter()->unique()->sort()->values();
    $drivers = collect($loads)->pluck('driver')->filter()->unique()->sort()->values();
@endphp

<x-filament::page>
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
            <div class="tms-card">
                <div class="flex items-center justify-between">
                    <div class="text-xs uppercase tracking-[0.12em] text-gray-500 dark:text-gray-400">Legend</div>
                    <span class="tms-legend-toggle" data-target="tms-legend-body">Toggle</span>
                </div>
                <div id="tms-legend-body" class="mt-2 space-y-1 text-xs text-gray-600 dark:text-gray-300">
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-sky-500 inline-block"></span> Stops</div>
                    <div class="flex items-center gap-2"><span class="w-4 h-[2px] bg-emerald-600 inline-block"></span> Lane polyline</div>
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-orange-500 inline-block border border-white shadow"></span> Truck position</div>
                </div>
            </div>
        </div>

        <div class="tms-filter-bar">
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
                        <option value="{{ $dispatcher }}">{{ $dispatcher }}</option>
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
                <label class="inline-flex items-center gap-1 text-xs text-gray-600 dark:text-gray-300 ml-2">
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
            </div>
            <div class="flex gap-2" style="margin-left:auto;">
                <button id="tms-clear-selection" type="button" class="tms-btn">Clear selection</button>
                <button id="tms-fit-selected" type="button" class="tms-btn">Fit selected</button>
                <button id="tms-fit-all" type="button" class="tms-btn">Fit all</button>
                <button id="tms-refresh" type="button" class="tms-btn tms-btn-primary">Refresh map</button>
                <button id="tms-export" type="button" class="tms-btn">Export CSV</button>
                <button id="tms-print" type="button" class="tms-btn">Print</button>
            </div>
        </div>

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
                                data-dispatcher="{{ $load['dispatcher'] ?? '' }}"
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
                            </div>
                        @empty
                            <div class="text-sm text-gray-500">No loads available for map view.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament::page>
