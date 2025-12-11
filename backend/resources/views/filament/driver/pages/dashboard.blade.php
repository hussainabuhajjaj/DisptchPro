<x-filament-panels::page>
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <style>
            .leaflet-container { font: inherit; }
        </style>
    @endpush
    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            function driverDashboard() {
                return {
                    token: @js(auth('driver')->user()->api_token ?? ''),
                    jobs: [],
                    status: 'Loading…',
                    map: null,
                    markers: [],
                    autoPingId: null,
                    autoPingIntervalMs: 60000,
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
                    api(path) { return path; },
                    initMap() {
                        if (!window.L || !this.$refs.map) return;
                    this.map = L.map(this.$refs.map, { zoomControl: true }).setView([39.5, -98.35], 4);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18, attribution: '&copy; OpenStreetMap' }).addTo(this.map);
                },
                drawMarkers() {
                    if (!this.map) return;
                        this.markers.forEach(m => this.map.removeLayer(m)); this.markers = [];
                        const bounds = [];
                        this.jobs.forEach(job => {
                            const lat = Number(job.last_lat), lng = Number(job.last_lng);
                            if (Number.isNaN(lat) || Number.isNaN(lng)) return;
                            const marker = L.circleMarker([lat, lng], { radius: 9, weight: 2, color: '#10b981', fillColor: '#10b981', fillOpacity: 0.85 }).addTo(this.map);
                            marker.bindPopup(`Load ${job.load_number ?? job.id}`);
                            this.markers.push(marker); bounds.push([lat, lng]);
                        });
                        if (bounds.length) this.map.fitBounds(bounds, { padding: [30, 30], maxZoom: 10 });
                    },
                    decorateJobs(data) {
                        this.jobs = (data.loads || []).map(job => {
                            const stops = (job.stops || []).map((s, idx) => ({ ...s, is_next: idx === 0 }));
                            const nextLabel = stops.length ? `${stops[0].type} ${stops[0].city || ''} ${stops[0].date_from || ''}`.trim() : null;
                            const lastPing = job.last_location_at ? new Date(job.last_location_at).toLocaleTimeString() : null;
                            const lastPingDate = job.last_location_at ? new Date(job.last_location_at) : null;
                            const noPing = lastPingDate ? (Date.now() - lastPingDate.getTime()) > 15 * 60 * 1000 : true;
                            const nextStopDate = stops.length && stops[0].date_from ? new Date(stops[0].date_from) : null;
                            let flag = null;
                            let flag_color = 'bg-slate-100 text-slate-700';
                            if (noPing) { flag = 'No ping >15m'; flag_color = 'bg-red-100 text-red-800'; }
                            else if (nextStopDate && Date.now() > nextStopDate.getTime()) { flag = 'Late to stop'; flag_color = 'bg-orange-100 text-orange-800'; }
                            return { ...job, stops, next_stop_label: nextLabel, last_ping: lastPing, last_ping_raw: lastPingDate, flag, flag_color };
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
                            const activeLoad = this.jobs[0]; if (!activeLoad) return;
                            let lat = activeLoad.last_lat, lng = activeLoad.last_lng;
                            if (navigator.geolocation) {
                                await new Promise((resolve) => {
                                    navigator.geolocation.getCurrentPosition(
                                        (pos) => { lat = pos.coords.latitude; lng = pos.coords.longitude; resolve(); },
                                        () => resolve(),
                                        { enableHighAccuracy: false, timeout: 2000 }
                                    );
                                });
                            }
                            const body = { load_id: activeLoad.id, lat, lng, eta_minutes: activeLoad.last_eta_minutes || null };
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
                        if (this.autoPingId) { clearInterval(this.autoPingId); this.autoPingId = null; this.status = 'Auto-ping stopped'; return; }
                        if (!this.jobs.length && !forceStart) { this.status = 'Load jobs first'; return; }
                        this.autoPingId = setInterval(() => this.sendPing(), this.autoPingIntervalMs);
                        this.status = 'Auto-ping started (60s)';
                    },
                    async rotateToken() {
                        this.status = 'Rotating token...';
                        try {
                            const res = await fetch(this.api('/api/driver/token'), {
                                method: 'POST',
                                headers: { 'X-Driver-Token': this.token, Accept: 'application/json' },
                            });
                            if (!res.ok) throw new Error('Rotate failed');
                            const data = await res.json();
                            this.token = data.token ?? this.token;
                            this.status = 'Token rotated';
                        } catch (e) {
                            this.status = e.message || 'Token rotate failed';
                        }
                    },
                };
            }
        </script>
    @endpush

    <div x-data="driverDashboard()" x-init="init()" wire:ignore class="space-y-6">
        <x-filament::section class="bg-gradient-to-r from-indigo-50 via-white to-emerald-50 border-none shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Driver Console</div>
                    <div class="text-sm text-slate-600">Auto-tracking on; use quick actions for status & pings.</div>
                </div>
                <div class="flex items-center gap-2">
                    <x-filament::button color="primary" icon="heroicon-m-arrow-path" x-on:click="refreshJobs">
                        Refresh loads
                    </x-filament::button>
                    <x-filament::button x-bind:color="autoPingId ? 'danger' : 'success'" icon="heroicon-m-signal" x-on:click="toggleAutoPing">
                        <span x-text="autoPingId ? 'Stop auto-ping' : 'Start auto-ping'"></span>
                    </x-filament::button>
                    <x-filament::button color="gray" icon="heroicon-m-map-pin" x-on:click="sendPing">
                        Send ping
                    </x-filament::button>
                    <x-filament::button color="secondary" icon="heroicon-m-key" x-on:click="rotateToken">
                        Rotate token
                    </x-filament::button>
                </div>
            </div>
            <div class="text-xs text-slate-600 mt-2" x-text="status"></div>
        </x-filament::section>

        <x-filament::section class="shadow-sm">
            <div class="flex items-center justify-between gap-3 mb-3">
                <div class="text-sm font-semibold text-slate-700">Live Map</div>
                <div class="text-xs text-slate-500">Markers show last pinged position per load</div>
            </div>
            <div x-ref="map" style="height: 380px; min-height: 380px;" class="w-full rounded-lg border border-slate-200"></div>
        </x-filament::section>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <template x-for="job in jobs" :key="job.id">
                <x-filament::section class="shadow-sm border-slate-200">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-sm font-semibold" x-text="`Load ${job.load_number ?? job.id}`"></div>
                            <div class="mt-1 flex flex-wrap items-center gap-2 text-[11px] font-semibold">
                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-1 text-slate-700" x-text="job.status ?? '—'"></span>
                                <template x-if="job.next_stop_label">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-1 text-emerald-700" x-text="job.next_stop_label"></span>
                                </template>
                                <template x-if="job.last_ping">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2 py-1 text-indigo-700" x-text="`Ping: ${job.last_ping}`"></span>
                                </template>
                                <template x-if="job.flag">
                                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-1" :class="job.flag_color" x-text="job.flag"></span>
                                </template>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-1 justify-end">
                            <template x-for="s in statuses" :key="s.value">
                                <x-filament::badge
                                    size="sm"
                                    class="cursor-pointer"
                                    :color="'gray'"
                                    x-bind:class="job.status === s.value ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-700'"
                                    x-on:click="updateStatus(job.id, s.value)"
                                >
                                    <span x-text="s.label"></span>
                                </x-filament::badge>
                            </template>
                        </div>
                    </div>

                    <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-gray-600">
                        <span class="inline-flex items-center gap-1 rounded bg-gray-100 px-2 py-1" x-text="job.eta_minutes ? `ETA ${job.eta_minutes} min` : 'ETA n/a'"></span>
                        <span class="inline-flex items-center gap-1 rounded bg-gray-100 px-2 py-1">
                            Ping:
                            <span x-text="job.last_location_at ? job.last_ping : 'n/a'"></span>
                        </span>
                        <template x-if="job.stops && job.stops.length">
                            <span class="inline-flex items-center gap-1 rounded bg-gray-100 px-2 py-1" x-text="`${job.stops.length} stops`"></span>
                        </template>
                    </div>

                    <div class="mt-3 space-y-2 text-xs text-gray-700" x-show="job.stops && job.stops.length" x-cloak>
                        <template x-for="stop in job.stops" :key="stop.id">
                            <div class="flex items-center gap-2 rounded px-2 py-1" :class="stop.is_next ? 'bg-emerald-50' : 'bg-slate-50'">
                                <span class="inline-flex h-2 w-2 rounded-full" :class="stop.type === 'delivery' ? 'bg-indigo-500' : 'bg-emerald-500'"></span>
                                <span class="font-semibold capitalize" x-text="stop.type ?? 'stop'"></span>
                                <span x-text="stop.city && stop.state ? `${stop.city}, ${stop.state}` : '—'"></span>
                                <span class="text-gray-500" x-text="stop.date_from ?? ''"></span>
                            </div>
                        </template>
                    </div>
                </x-filament::section>
            </template>
        </div>
    </div>
</x-filament-panels::page>
