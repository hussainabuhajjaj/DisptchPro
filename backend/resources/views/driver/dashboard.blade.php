<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Dashboard</title>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tailwindcss/forms@0.5.7/dist/forms.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        body { font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background: linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%); }
        .card { border: 1px solid #e2e8f0; border-radius: 14px; background: #fff; padding: 16px; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.12); }
        .pill { display: inline-flex; align-items: center; gap: 6px; border-radius: 9999px; padding: 4px 10px; font-size: 12px; font-weight: 600; }
    </style>
</head>
<body class="p-4 md:p-6">
    <div x-data="driverDashboard()" class="max-w-6xl mx-auto space-y-4">
        <div class="card flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-sm uppercase tracking-wide text-slate-500">Welcome</div>
                <div class="text-xl font-semibold text-slate-800">{{ $driver->name }}</div>
                <div class="text-xs text-slate-500">Token auto-issued; tracking is automatic.</div>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" class="h-10 px-3 rounded bg-indigo-600 text-white text-sm font-semibold" @click="refreshJobs">Refresh loads</button>
                <button type="button" class="h-10 px-3 rounded" :class="autoPingId ? 'bg-rose-600 text-white' : 'bg-emerald-600 text-white'" @click="toggleAutoPing">
                    <span x-text="autoPingId ? 'Stop auto-ping' : 'Start auto-ping'"></span>
                </button>
            </div>
            <div class="text-sm text-slate-600" x-text="status"></div>
        </div>

        <div class="card">
            <h2 class="text-lg font-semibold mb-3">Map</h2>
            <div x-ref="map" style="height: 360px; min-height: 360px;" class="w-full rounded border border-slate-200"></div>
        </div>

        <div class="card space-y-3">
            <h2 class="text-lg font-semibold">Assigned Loads</h2>
            <template x-for="job in jobs" :key="job.id">
                <div class="rounded border border-slate-200 p-3 space-y-2">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <div class="font-semibold" x-text="`Load ${job.load_number ?? job.id}`"></div>
                            <div class="text-xs text-slate-500 flex flex-wrap items-center gap-2">
                                <span class="pill bg-slate-100 text-slate-700" x-text="job.status || '—'"></span>
                                <template x-if="job.next_stop_label">
                                    <span class="pill bg-emerald-50 text-emerald-700" x-text="job.next_stop_label"></span>
                                </template>
                                <template x-if="job.last_ping">
                                    <span class="pill bg-indigo-50 text-indigo-700" x-text="`Ping: ${job.last_ping}`"></span>
                                </template>
                            </div>
                        </div>
                        <div class="flex gap-2 flex-wrap justify-end">
                            <template x-for="s in statuses" :key="s.value">
                                <button
                                    type="button"
                                    class="px-2 py-1 rounded text-xs border"
                                    :class="job.status === s.value ? 'bg-indigo-600 text-white border-indigo-600' : 'border-slate-300 text-slate-700'"
                                    @click="updateStatus(job.id, s.value)"
                                >
                                    <span x-text="s.label"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                    <div class="text-sm text-slate-700" x-text="job.driver_notes || job.internal_notes || 'No notes'"></div>
                    <div class="space-y-1">
                        <template x-for="stop in job.stops" :key="stop.id">
                            <div class="text-xs text-slate-600 flex items-center gap-2" :class="stop.is_next ? 'bg-emerald-50 rounded px-2 py-1' : ''">
                                <span class="h-2 w-2 rounded-full" :class="stop.type === 'delivery' ? 'bg-emerald-500' : 'bg-indigo-500'"></span>
                                <span class="font-semibold capitalize" x-text="stop.type"></span>
                                <span x-text="stop.city && stop.state ? `${stop.city}, ${stop.state}` : ''"></span>
                                <span x-text="stop.date_from || ''"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <script>
        function driverDashboard() {
            return {
                baseUrl: window.location.origin,
                token: @js($token),
                driverId: @js($driver->id),
                jobs: [],
                status: 'Loading…',
                autoPingId: null,
                autoPingIntervalMs: 60000,
                map: null,
                markers: [],
                statuses: [
                    { value: 'en_route', label: 'En Route' },
                    { value: 'arrived_pickup', label: 'Arrived PU' },
                    { value: 'loaded', label: 'Loaded' },
                    { value: 'arrived_delivery', label: 'Arrived Del' },
                    { value: 'unloaded', label: 'Unloaded' },
                    { value: 'issue', label: 'Issue' },
                ],
                init() {
                    this.initMap();
                    this.refreshJobs();
                    this.toggleAutoPing(true);
                },
                api(path) {
                    return this.baseUrl.replace(/\/+$/, '') + path;
                },
                initMap() {
                    if (!window.L || !this.$refs.map) return;
                    this.map = L.map(this.$refs.map, { zoomControl: true }).setView([39.5, -98.35], 4);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 18,
                        attribution: '&copy; OpenStreetMap',
                    }).addTo(this.map);
                },
                drawMarkers() {
                    if (!this.map) return;
                    this.markers.forEach(m => this.map.removeLayer(m));
                    this.markers = [];
                    const bounds = [];
                    this.jobs.forEach(job => {
                        const lat = Number(job.last_lat);
                        const lng = Number(job.last_lng);
                        if (Number.isNaN(lat) || Number.isNaN(lng)) return;
                        const marker = L.circleMarker([lat, lng], {
                            radius: 9, weight: 2, color: '#10b981', fillColor: '#10b981', fillOpacity: 0.85,
                        }).addTo(this.map);
                        marker.bindPopup(`Load ${job.load_number ?? job.id}`);
                        this.markers.push(marker);
                        bounds.push([lat, lng]);
                    });
                    if (bounds.length) this.map.fitBounds(bounds, { padding: [30, 30], maxZoom: 10 });
                },
                decorateJobs(data) {
                    this.jobs = (data.loads || []).map(job => {
                        const stops = (job.stops || []).map((s, idx) => ({
                            ...s,
                            is_next: idx === 0,
                        }));
                        const nextLabel = stops.length ? `${stops[0].type} ${stops[0].city || ''} ${stops[0].date_from || ''}`.trim() : null;
                        const lastPing = job.last_location_at ? new Date(job.last_location_at).toLocaleTimeString() : null;
                        return { ...job, stops, next_stop_label: nextLabel, last_ping: lastPing };
                    });
                    this.drawMarkers();
                },
                async refreshJobs() {
                    this.status = 'Loading jobs...';
                    try {
                        const res = await fetch(this.api('/api/driver/jobs'), {
                            headers: { 'X-Driver-Token': this.token, 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
                        });
                        if (!res.ok) throw new Error('Auth failed or no jobs');
                        const data = await res.json();
                        this.decorateJobs(data);
                        this.status = `Loaded ${this.jobs.length} loads`;
                    } catch (e) {
                        this.status = e.message || 'Failed to load jobs';
                    }
                },
                async updateStatus(loadId, status) {
                    this.status = 'Updating status...';
                    try {
                        const res = await fetch(this.api(`/api/driver/jobs/${loadId}/status`), {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-Driver-Token': this.token },
                            body: JSON.stringify({ status }),
                        });
                        if (!res.ok) throw new Error('Update failed');
                        this.status = 'Status updated';
                        await this.refreshJobs();
                    } catch (e) {
                        this.status = e.message || 'Failed to update status';
                    }
                },
                async sendPing() {
                    try {
                        const activeLoad = this.jobs[0];
                        if (!activeLoad) return;
                        let lat = activeLoad.last_lat, lng = activeLoad.last_lng;
                        // attempt browser geolocation
                        if (navigator.geolocation) {
                            await new Promise((resolve) => {
                                navigator.geolocation.getCurrentPosition(
                                    (pos) => { lat = pos.coords.latitude; lng = pos.coords.longitude; resolve(); },
                                    () => resolve(),
                                    { enableHighAccuracy: false, timeout: 2000 }
                                );
                            });
                        }
                        const body = {
                            load_id: activeLoad.id,
                            lat,
                            lng,
                            eta_minutes: activeLoad.last_eta_minutes || null,
                        };
                        const res = await fetch(this.api('/api/driver/location'), {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-Driver-Token': this.token },
                            body: JSON.stringify(body),
                        });
                        if (!res.ok) throw new Error('Ping failed');
                        this.status = 'Ping sent';
                        await this.refreshJobs();
                    } catch (e) {
                        this.status = e.message || 'Ping failed';
                    }
                },
                toggleAutoPing(forceStart = false) {
                    if (this.autoPingId) {
                        clearInterval(this.autoPingId);
                        this.autoPingId = null;
                        this.status = 'Auto-ping stopped';
                        return;
                    }
                    if (!this.jobs.length && !forceStart) {
                        this.status = 'Load jobs first';
                        return;
                    }
                    this.autoPingId = setInterval(() => this.sendPing(), this.autoPingIntervalMs);
                    this.status = 'Auto-ping started (60s)';
                },
            };
        }
    </script>
</body>
</html>
