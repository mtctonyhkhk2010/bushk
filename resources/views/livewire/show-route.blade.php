<div>
    <x-custom-header class="mb-0">
        <x-slot:middle>
            {{ $route->name }} {{ $route->dest_tc }}
        </x-slot:middle>
        <x-slot:actions>
            @if(isset($reverse_route))
                <button class="btn btn-neutral" wire:navigate
                        href="/route/{{ $reverse_route->id }}/{{ $reverse_route->name }}"> <x-heroicon-o-arrow-uturn-down class="h-5 w-5"/>對頭線</button>
            @endif
        </x-slot:actions>
    </x-custom-header>
    <div class="h-[calc(100svh-112px)]">
        <div id="map" class="h-2/5" x-data="map" @go-to-position.window="goToPosition"></div>
        <div class="h-3/5 overflow-y-scroll" x-data="stop_list"
             @go-to-stop.window="goToStop">
            @foreach($stops[$this->route->companies->first()->id] as $stop)
                <livewire:show-route-stop :stop="$stop" :last_stop="$loop->iteration == $loop->count" :route_id="$route->id"/>
            @endforeach
        </div>
    </div>
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
                L.marker([stop.latitude, stop.longitude], {icon: stop_icon}).addTo(this.map).on('mouseover', (e) => {
                    this.$dispatch('go-to-stop', sequence);
                });
                polylinePoints.push([stop.latitude, stop.longitude]);
            });
            L.polyline(polylinePoints).addTo(this.map);

            this.getUserLocation()
        },

        getUserLocation() {
            navigator.permissions.query({name:'geolocation'})
                .then((result) => {
                        if (result.state === 'granted') {
                            this.trackUserPosition();
                            document.addEventListener('livewire:navigating', () => {
                                if(this.watch_position_id !== null)
                                {
                                    navigator.geolocation.clearWatch(this.watch_position_id);
                                }
                            });
                        } else {
                            console.log('Browser location services disabled', navigator);
                        }
                    }, () => {
                        console.log('Browser permissions services unavailable', navigator);
                    }
                );
            if (navigator.geolocation) {

            } else {
                console.log("Geolocation is not supported by this browser.");
            }
        },

        trackUserPosition() {
            this.watch_position_id = navigator.geolocation.watchPosition((position) => {
                //console.log(position.coords);
                this.current_latitude = position.coords.latitude;
                this.current_longitude = position.coords.longitude;

                const marker_style = `transform: scale(2) rotate(${(position.coords.heading ?? 360) - 45}deg)`
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
                    radius: position.coords.accuracy ?? 500
                }).addTo(this.map);
            }, () => {
                console.log('System/OS services disabled', navigator);
            });
        },

        goToNearestStop() {
            let stop_distance = []
            this.stops_position.forEach((stop) => {
                stop_distance.push(this.distance(stop.latitude, stop.longitude, this.current_latitude, this.current_longitude));
            });
            this.$dispatch('go-to-stop', stop_distance.indexOf(Math.min(...stop_distance)));
        },

        goToPosition(event) {
            const sequence = event.detail;
            this.map.panTo(new L.LatLng(this.stops_position[sequence].latitude, this.stops_position[sequence].longitude));
        },

        distance(lat1, lon1, lat2, lon2) {
            if ((lat1 === lat2) && (lon1 === lon2)) {
                return 0;
            }
            else {
                let radlat1 = Math.PI * lat1/180;
                let radlat2 = Math.PI * lat2/180;
                let theta = lon1-lon2;
                let radtheta = Math.PI * theta/180;
                let dist = Math.sin(radlat1) * Math.sin(radlat2) + Math.cos(radlat1) * Math.cos(radlat2) * Math.cos(radtheta);
                if (dist > 1) {
                    dist = 1;
                }
                dist = Math.acos(dist);
                dist = dist * 180/Math.PI;
                dist = dist * 60 * 1.1515;
                return dist * 1.609344;
            }
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
            })
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
            this.$dispatch('go-to-position', sequence);

            this.etas = [];
            for (let key in this.companies) {
                if (!this.companies.hasOwnProperty(key)) continue;

                const company = this.companies[key];

                //console.log(this.stops[company.id])
                let path;
                let stop_code = this.stops[company.id][sequence]['stop_code'];
                if (company.co === 'kmb') {
                    path = `https://data.etabus.gov.hk/v1/transport/kmb/eta/${stop_code}/${this.route_name}/${this.service_type}`;
                }
                if (company.co === 'ctb') {
                    path = `https://rt.data.gov.hk//v2/transport/citybus/eta/CTB/${stop_code}/${this.route_name}`;
                }
                if (company.co === 'gmb') {
                    path = `https://data.etagmb.gov.hk/eta/route-stop/${this.gtfs_id}/${stop_code}`;
                }
                //console.log(path)
                const response = await fetch(path);
                const data = await response.json();
                this.loading = false;

                if (company.co === 'kmb' || company.co === 'ctb') {
                    data.data.forEach((item) => {
                        if (item.eta === "" || item.eta === null || item.dir !== company.pivot.bound) return;

                        this.etas.push({
                            timestamp: Date.parse(item.eta),
                            eta: item.eta,
                            co: item.co,
                            remark: item.rmk_tc,
                        });
                    });
                }

                if (company.co === 'gmb') {
                    data.data.forEach((item) => {
                        if ((company.pivot.bound === "I" && item.route_seq === 1) ||
                            (company.pivot.bound === "O" && item.route_seq === 2)) return;

                        item.eta.forEach((eta) => {
                            if (eta.timestamp === "" || eta.timestamp === null) return;

                            this.etas.push({
                                timestamp: Date.parse(eta.timestamp),
                                eta: eta.timestamp,
                                co: 'gmb',
                                remark: eta.remarks_tc,
                            });
                        })
                    });
                }
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
