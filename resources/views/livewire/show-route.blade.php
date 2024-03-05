<div>
    <x-layouts.navbar>
        <x-slot:start>
            @if(isset($reverse_route))
            <div class="flex divide-x" wire:navigate href="/route/{{ $reverse_route->id }}/{{ $reverse_route->name }}">
                <div class="flex flex-col justify-items-center max-w-14">
                    <x-heroicon-o-arrow-uturn-down class="h-5 w-full"/>
                    <div class="w-full text-center">對頭線</div>
                </div>
            </div>
            @endif
        </x-slot:start>
        <x-slot:title>
            <span @if($is_mtr) style="border-bottom: 1px solid {{ $route->mtr_info->line_color }}; margin-bottom: -1px;" @endif>
                <span class="mr-1">{{ $is_mtr ? $route->mtr_info->line_name_tc : $route->name }}</span>
                <span class="mr-1 text-xs">往</span>
                <span>{{ $route->dest_tc }}</span>
            </span>
        </x-slot:title>
        <x-slot:end>
            <x-heroicon-o-arrows-right-left class="h-5 w-5 mr-3"
                                            wire:navigate
                                            href="/interchange/{{ $route->id }}"/>
            <x-heroicon-o-clock class="h-5 w-5 mr-3"
                                            wire:navigate
                                            href="/service-time/{{ $route->id }}"/>
            <livewire:toggle-favorite-route :route_id="$route->id"/>
        </x-slot:end>
    </x-layouts.navbar>
    <div class="h-[calc(100svh-112px-env(safe-area-inset-bottom))]">
        <div id="map" class="h-2/5" x-data="map"></div>
        <div class="h-3/5 overflow-y-scroll" x-data="stop_list">
            @foreach($stops[$this->route->companies->first()->id] as $stop)
                <livewire:show-route-stop :stop="$stop" :last_stop="$loop->iteration == $loop->count" :route_id="$route->id"/>
            @endforeach
        </div>
    </div>
    <x-offline/>
</div>

@assets
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>
<style>
    .loader {
        width: 50px;
        aspect-ratio: 1;
        border-radius: 50%;
        border: 8px solid;
        border-color: #cecece #0000;
        animation: l1 1s infinite;
    }
    @keyframes l1 {to{transform: rotate(.5turn)}}

    .marker {
        transform: scale(2);
        opacity: 0.7;
    }
</style>
@endassets

@script
<script>
    Alpine.data('map', () => ({
        stops_position: @js($stops_position),
        map: null,
        current_latitude: null,
        current_longitude: null,
        current_position_marker: null,
        current_position_accuracy_circle: null,
        first_load: true,
        watch_position_id: null,

        init() {
            const stop_icon_colour = '#ce2b5c'

            const stop_icon = L.divIcon({
                className: "stop_icon",
                iconAnchor: [6, 24],
                labelAnchor: [-6, 0],
                popupAnchor: [0, -36],
                html: `<svg xmlns="http://www.w3.org/2000/svg" class="marker" viewBox="0 0 384 512"><path fill="${stop_icon_colour}" d="M172.3 501.7C27 291 0 269.4 0 192 0 86 86 0 192 0s192 86 192 192c0 77.4-27 99-172.3 309.7-9.5 13.8-29.9 13.8-39.5 0zM192 272c44.2 0 80-35.8 80-80s-35.8-80-80-80-80 35.8-80 80 35.8 80 80 80z"/></svg>`
            });

            this.map = L.map('map').setView([this.stops_position[0].latitude, this.stops_position[0].longitude], 16);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(this.map);
            let polylinePoints = [];
            this.stops_position.forEach((stop, sequence) => {
                L.marker([stop.latitude, stop.longitude], {icon: stop_icon}).addTo(this.map).on('click', (e) => {
                    this.$dispatch('go-to-stop', sequence);
                });
                polylinePoints.push([stop.latitude, stop.longitude]);
            });
            L.polyline(polylinePoints).addTo(this.map);

            //this.getUserLocation()
            this.trackUserPosition();
            document.addEventListener("position-updated", (e) => {
                this.trackUserPosition();
            });

            document.addEventListener("go-to-position", (e) => {
                this.goToPosition(e);
            });
        },

        trackUserPosition() {
            if(window.coords === undefined) return;
            this.current_latitude = window.coords.latitude;
            this.current_longitude = window.coords.longitude;

            const marker_style = `transform: scale(2) rotate(${(window.coords.heading ?? 360) - 45}deg)`
            const location_icon = L.divIcon({
                className: "location_icon",
                iconAnchor: [6, 0],
                labelAnchor: [-6, 0],
                popupAnchor: [0, -36],
                html: `<svg xmlns="http://www.w3.org/2000/svg" style="${marker_style}" viewBox="0 0 448 512"><path d="M429.6 92.1c4.9-11.9 2.1-25.6-7-34.7s-22.8-11.9-34.7-7l-352 144c-14.2 5.8-22.2 20.8-19.3 35.8s16.1 25.8 31.4 25.8H224V432c0 15.3 10.8 28.4 25.8 31.4s30-5.1 35.8-19.3l144-352z"/></svg>`
            });

            if (this.first_load)
            {
                this.goToNearestStop();
                this.first_load = false;
            }
            else
            {
                this.map.removeLayer(this.current_position_marker);
                this.map.removeLayer(this.current_position_accuracy_circle);
            }

            this.current_position_marker = L.marker([this.current_latitude, this.current_longitude], {icon: location_icon}).addTo(this.map);
            this.current_position_accuracy_circle = L.circle([this.current_latitude, this.current_longitude], {
                color: 'blue',
                fillColor: '#24a3ff',
                fillOpacity: 0.3,
                radius: window.coords.accuracy ?? 500
            }).addTo(this.map);
        },

        goToNearestStop() {
            let stop_distance = []
            this.stops_position.forEach((stop) => {
                stop_distance.push(window.distance(stop.latitude, stop.longitude, this.current_latitude, this.current_longitude));
            });
            this.$nextTick(() => {
                document.dispatchEvent(new CustomEvent("go-to-stop", { detail: stop_distance.indexOf(Math.min(...stop_distance)) }));
            });
        },

        goToPosition(event) {
            const sequence = event.detail;
            if(this.stops_position[sequence] === undefined) return;
            this.map.panTo(new L.LatLng(this.stops_position[sequence].latitude, this.stops_position[sequence].longitude));
        }
    }));

    Alpine.data('stop_list', () => ({
        route_name: @js($route->name),
        gtfs_id: @js($route->gtfs_id),
        service_type: @js($route->service_type),
        stops: @js($stops),
        companies: @js($route->companies->keyBy('id')),
        etas: [],
        active: null,
        loading: false,
        getETAInterval: null,
        is_visible: false,

        init() {
            this.$watch('etas', () => {
                this.etas = this.etas.sort((a, b) => {
                    return a.timestamp - b.timestamp;
                })
            });

            this.$watch('active', () => {
                this.resetGetETA();

                //get eta every 60 second
                this.getETAInterval = setInterval(() => {
                    this.getETA(this.active);
                }, 30000);
            });

            document.addEventListener("visibilitychange", () => {
                this.is_visible = document.visibilityState === "visible";
                if(!this.is_visible && this.getETAInterval !== null) this.resetGetETA();
                if (this.is_visible && this.getETAInterval === null)
                {
                    this.getETA(this.active);
                    this.getETAInterval = setInterval(() => {
                        this.getETA(this.active);
                    }, 30000);
                }
            });

            document.addEventListener('livewire:navigating', () => {
                if(this.getETAInterval !== null) this.resetGetETA();
            });

            document.addEventListener('go-to-stop', (e) => {
                this.goToStop(e);
            });
        },

        resetGetETA() {
            clearInterval(this.getETAInterval);
            this.getETAInterval = null;
        },

        goToStop(event) {
            const sequence = event.detail;
            document.getElementById("stop_" + sequence).scrollIntoView();
            this.getETA(sequence);
        },

        async getETA(sequence) {
            //if (sequence === this.active && !force) return;
            this.active = sequence;
            this.loading = true;
            this.$nextTick(() => {
                document.dispatchEvent(new CustomEvent("go-to-position", { detail: sequence }));
            });

            this.etas = [];
            for (let key in this.companies) {
                if (!this.companies.hasOwnProperty(key)) continue;

                const company = this.companies[key];

                if(this.stops[company.id][sequence] === undefined) continue;

                const fetchEta = window.fetchEta(company.co, this.stops[company.id][sequence]['stop_code'], this.route_name,
                    this.service_type, this.gtfs_id, company.pivot.bound, @js($route->nlb_id), @js($route->dest_tc),
                    sequence, this.stops[company.id][0]['stop_code'] === this.stops[company.id][this.stops[company.id].length - 1]['stop_code']);

                fetchEta.then((temp_etas) => {
                    this.loading = false;
                    temp_etas.forEach((eta) => {
                        this.etas.push(eta);
                    });
                });
            }
        },

        formatTime(time) {
            const date = new Date(time);
            return this.padTo2Digits(date.getHours()) + ':' + this.padTo2Digits(date.getMinutes());
        },

        remainingTimeInMinutes(time) {
            const date = new Date(time);
            const now = new Date();
            const diffMs = (date - now); // milliseconds between now & Christmas
            const diffMins = Math.round(((diffMs % 86400000) % 3600000) / 60000); // minutes
            if (diffMins <= 0) return 0;
            return diffMins;
        },

        padTo2Digits(num) {
            return String(num).padStart(2, '0');
        },
    }));
</script>
@endscript
