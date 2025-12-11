<x-filament::page>
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <style>
            .leaflet-container { font: inherit; }
        </style>
    @endpush

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            const liveMapEndpoint = @js(route('admin.map-data'));

            function liveMapPage() {
                return {
                    loads: [],
                    markers: [],
                    map: null,
                    lastFetched: null,
                    polling: null,
                    hasData: false,
                    filterLate: false,
                    filterNoPing: false,
                    filterConflict: false,

                    init() {
                        if (this.map) {
                            this.map.off();
                            this.map.remove();
                            this.map = null;
                        }
                        if (!window.L || !this.$refs.map) {
                            console.error('Leaflet failed to load or map container missing.');
                            return;
                        }

                        this.map = L.map(this.$refs.map, { zoomControl: true, worldCopyJump: true }).setView([39.5, -98.35], 4);

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 18,
                            attribution: '&copy; OpenStreetMap',
                        }).addTo(this.map);

                        this.fetchData();
                        this.polling = setInterval(() => this.fetchData(), 60000);
                    },

                    destroy() {
                        if (this.polling) clearInterval(this.polling);
                    },

                    fetchData() {
                        fetch(liveMapEndpoint, {
                            method: 'GET',
                            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
                            credentials: 'same-origin',
                        })
                            .then((r) => (r.ok ? r.json() : Promise.reject()))
                            .then((payload) => {
                                this.loads = payload.loads ?? [];
                                this.hasData = this.loads.length > 0;
                                this.lastFetched = new Date();
                                this.redraw();
                            })
                            .catch(() => console.warn('Unable to refresh map data.'));
                    },

                    redraw() {
                        if (!this.map) return;
                        this.markers.forEach((m) => this.map.removeLayer(m));
                        this.markers = [];

                        const bounds = [];

                        const filtered = this.loads.filter((load) => {
                            const f = load.flags || {};
                            if (this.filterLate && !f.late_eta) return false;
                            if (this.filterNoPing && !f.no_recent_ping) return false;
                            if (this.filterConflict && !f.driver_conflict) return false;
                            return true;
                        });

                        filtered.forEach((load) => {
                            const lat = Number(load.last_lat);
                            const lng = Number(load.last_lng);
                            if (Number.isNaN(lat) || Number.isNaN(lng)) return;

                            const color = this.markerColor(load);
                            const marker = L.circleMarker([lat, lng], {
                                radius: 9,
                                weight: 2,
                                color,
                                fillColor: color,
                                fillOpacity: 0.85,
                            });

                            marker.bindPopup(this.popupContent(load));
                            marker.addTo(this.map);
                            this.markers.push(marker);
                            bounds.push([lat, lng]);

                            if (load.breadcrumbs && load.breadcrumbs.length > 1) {
                                const points = load.breadcrumbs.map((b) => [Number(b.lat), Number(b.lng)]);
                                const line = L.polyline(points, { color: '#94a3b8', weight: 2, opacity: 0.7 }).addTo(this.map);
                                this.markers.push(line);
                            }
                        });

                        if (bounds.length) {
                            this.map.fitBounds(bounds, { padding: [40, 40], maxZoom: 10 });
                        }
                    },

                    markerColor(load) {
                        const f = load.flags || {};
                        if (f.driver_conflict) return '#f97316';
                        if (f.no_recent_ping || f.no_recent_check_call || f.late_eta) return '#e11d48';
                        return '#10b981';
                    },

                    popupContent(load) {
                        const last = load.last_location_at ? new Date(load.last_location_at).toLocaleString() : '—';
                        const eta = load.eta_minutes ? `${load.eta_minutes} min` : '—';
                        const f = load.flags || {};
                        const flags = [];
                        if (f.late_eta) flags.push('Late ETA');
                        if (f.no_recent_ping) flags.push('No ping');
                        if (f.no_recent_check_call) flags.push('No check-call');
                        if (f.driver_conflict) flags.push('Driver conflict');
                        const flagsHtml = flags.length ? `<div class="text-xs text-red-600">${flags.join(' · ')}</div>` : '';
                        return `
                            <div class="space-y-1 text-sm">
                                <div class="font-semibold">Load ${load.load_number ?? load.id}</div>
                                <div>Driver: ${load.driver ?? 'Unassigned'}</div>
                                <div>Status: ${load.status ?? '—'}</div>
                                <div>ETA: ${eta}</div>
                                <div class="text-xs text-gray-500">Last ping: ${last}</div>
                                ${load.dispatcher ? `<div class="text-xs text-gray-500">Dispatcher: ${load.dispatcher}</div>` : ''}
                                ${flagsHtml}
                            </div>
                        `;
                    },

                    timeAgo(date) {
                        if (!date) return '—';
                        const diff = (Date.now() - new Date(date).getTime()) / 1000;
                        if (diff < 60) return `${Math.round(diff)}s ago`;
                        if (diff < 3600) return `${Math.round(diff / 60)}m ago`;
                        if (diff < 86400) return `${Math.round(diff / 3600)}h ago`;
                        return `${Math.round(diff / 86400)}d ago`;
                    },
                };
            }
        </script>
    @endpush

    <div x-data="liveMapPage()" x-init="init(); return () => destroy();" wire:ignore class="space-y-6">
        <div class="flex flex-wrap items-center gap-3">
            <x-filament::button icon="heroicon-m-arrow-path" color="primary" outlined x-on:click="fetchData">
                Refresh now
            </x-filament::button>
            <div class="text-sm text-gray-600">
                <span class="font-medium">Last update:</span>
                <span x-text="lastFetched ? timeAgo(lastFetched) : 'Loading…'"></span>
            </div>
            <div class="flex items-center gap-2 text-sm">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" x-model="filterLate">
                    <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-3 py-1 text-red-800">
                        <span class="h-2 w-2 rounded-full bg-red-500"></span>
                        Late ETA
                    </span>
                </label>
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" x-model="filterNoPing">
                    <span class="inline-flex items-center gap-1 rounded-full bg-yellow-100 px-3 py-1 text-yellow-800">
                        <span class="h-2 w-2 rounded-full bg-yellow-500"></span>
                        No ping
                    </span>
                </label>
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" x-model="filterConflict">
                    <span class="inline-flex items-center gap-1 rounded-full bg-orange-100 px-3 py-1 text-orange-800">
                        <span class="h-2 w-2 rounded-full bg-orange-500"></span>
                        Driver conflict
                    </span>
                </label>
                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-3 py-1 text-emerald-800">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    On track
                </span>
            </div>
        </div>

        <div x-ref="map" class="w-full rounded-xl border border-gray-200 shadow-sm bg-white" style="height:520px; min-height:520px;"></div>

        <div x-show="!hasData" x-cloak class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-600">
            No tracked loads yet. As soon as drivers send location updates, they’ll appear here. Ensure loads have a driver and location pings (via /api/driver/location).
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <template x-for="load in loads" :key="load.id">
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-sm font-semibold" x-text="`Load ${load.load_number ?? load.id}`"></div>
                                <div class="text-xs text-gray-500" x-text="load.dispatcher ? `Dispatcher: ${load.dispatcher}` : 'Unassigned dispatcher'"></div>
                                <div class="text-xs text-gray-500" x-text="load.driver ? `Driver: ${load.driver}` : 'Unassigned driver'"></div>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <span
                                    class="inline-flex items-center gap-1 rounded-full px-2 py-1 text-xs font-semibold"
                                    :class="(() => {
                                        const f = load.flags || {};
                                        if (f.no_recent_ping || f.no_recent_check_call || f.late_eta) return 'bg-red-100 text-red-800';
                                        if (f.driver_conflict) return 'bg-orange-100 text-orange-800';
                                        return 'bg-emerald-100 text-emerald-800';
                                    })()"
                                    x-text="(() => {
                                        const f = load.flags || {};
                                        if (f.no_recent_ping) return 'No ping';
                                        if (f.no_recent_check_call) return 'No check-call';
                                        if (f.late_eta) return 'Late ETA';
                                        if (f.driver_conflict) return 'Driver conflict';
                                        return 'Healthy';
                                    })()"
                                ></span>
                                <span class="rounded-full bg-gray-100 px-2 py-1 text-[11px] font-semibold text-gray-700" x-text="load.status ?? '—'"></span>
                            </div>
                        </div>

                    <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-gray-600">
                        <span class="rounded bg-gray-100 px-2 py-1" x-text="load.eta_minutes ? `ETA ${load.eta_minutes} min` : 'ETA n/a'"></span>
                        <span class="rounded bg-gray-100 px-2 py-1">
                            Ping:
                            <span x-text="load.last_location_at ? timeAgo(load.last_location_at) : 'n/a'"></span>
                        </span>
                        <template x-if="load.stops && load.stops.length">
                            <span class="rounded bg-gray-100 px-2 py-1" x-text="`${load.stops.length} stops`"></span>
                        </template>
                    </div>

                    <div class="mt-3 space-y-1 text-xs text-gray-600" x-show="load.stops && load.stops.length" x-cloak>
                        <template x-for="stop in load.stops" :key="stop.id">
                            <div class="flex items-center gap-2">
                                <span
                                    class="inline-flex h-2 w-2 rounded-full"
                                    :class="stop.type === 'dropoff' ? 'bg-indigo-500' : 'bg-emerald-500'"
                                ></span>
                                <span class="font-semibold capitalize" x-text="stop.type ?? 'stop'"></span>
                                <span x-text="stop.city && stop.state ? `${stop.city}, ${stop.state}` : '—'"></span>
                                <span class="text-gray-500" x-text="stop.date_from ? timeAgo(stop.date_from) : ''"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>
</x-filament::page>
