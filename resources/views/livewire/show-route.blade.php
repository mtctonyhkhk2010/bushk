<div>
    <x-header class="mb-0" title="{{ $route->name }}" subtitle="{{ $route->dest_tc }}" separator/>
    <div id="map" class="h-[35vh]" x-data="map" @go-to-position.window="goToPosition"></div>
    <div class="h-[50vh] overflow-y-scroll" x-data="stop_list"  @go-to-stop.window="goToStop">
{{--        <ul class="timeline timeline-vertical">--}}
            <template x-for="stop in stops[Object.keys(companies)[0]]">
                <div class="collapse bg-base-200"
                     :id="'stop_' + stop.pivot.sequence"
                     x-data="{
                            sequence: stop.pivot.sequence,
                            get expanded() {
                                return this.active === this.sequence
                            },
                            set expanded(value) {
                                this.active = value ? this.sequence : null
                            },
                        }">
                    <div class="collapse-title text-xl font-medium"
                         x-on:click="getETA(stop.pivot.sequence)"
                    >
                        <h5 x-text="(stop.pivot.sequence + 1) + '. ' + stop.name_tc"></h5>
                        <span x-show="stop.pivot.fare > 0" x-text="'$' + stop.pivot.fare"></span>
                    </div>
                    <div class="p-4" x-show="expanded" x-collapse>
                        <div class="loader" x-show="loading"></div>
                        <template x-for="eta in etas" >
                            <div x-show="!loading">
                                <span x-text="formatTime(eta.eta)"></span>
                                -
                                <span x-text="eta.co"></span> <span x-text="eta.remark"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
{{--        </ul>--}}
    </div>
</div>

@assets
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
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
</style>
@endassets

@script
<script>
    Alpine.data('map', () => ({
        stops_position: @js($stops_position),
        map: null,

        init() {
            this.map = L.map('map').setView([this.stops_position[0].latitude, this.stops_position[0].longitude], 16);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(this.map);
            this.stops_position.forEach((stop, sequence) => {
                L.marker([stop.latitude, stop.longitude]).addTo(this.map).on('mouseover', (e) => {
                    this.$dispatch('go-to-stop', sequence);
                });
            });
        },

        goToPosition(event) {
            const sequence = event.detail;
            this.map.panTo(new L.LatLng(this.stops_position[sequence].latitude, this.stops_position[sequence].longitude));
        },
    }));

    Alpine.data('stop_list', () => ({
        route_name: @js($route->name),
        service_type: @js($route->service_type),
        stops: @js($stops),
        companies: @js($route->companies->keyBy('id')),
        etas: [],
        active: null,
        loading: false,

        init() {
            this.$watch('etas', () => {
                this.etas = this.etas.sort((a, b) => {
                    return a.timestamp - b.timestamp;
                })
            });
        },

        goToStop(event) {
            const sequence = event.detail;
            document.getElementById("stop_" + sequence).scrollIntoView();
            this.getETA(sequence);
        },

        getETA(sequence) {
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
                if (company.co === 'kmb')
                {
                    path = `https://data.etabus.gov.hk/v1/transport/kmb/eta/${stop_code}/${this.route_name}/${this.service_type}`;
                }
                if (company.co === 'ctb')
                {
                    path = `https://rt.data.gov.hk//v2/transport/citybus/eta/CTB/${stop_code}/${this.route_name}`;
                }
                //console.log(path)
                delete axios.defaults.headers.common["X-Requested-With"];
                axios({
                    method: 'get',
                    url: path,
                })
                .then((response) => {
                    //console.log(response.data);
                    response.data.data.forEach((item) => {
                        if (item.eta === "" || item.eta === null) return;

                        this.etas.push({
                            timestamp: Date.parse(item.eta),
                            eta: item.eta,
                            co: item.co,
                            remark: item.rmk_tc,
                        });

                        this.loading = false;
                    })
                });


            }
        },

        formatTime(time) {
            const date = new Date(time);
            return this.padTo2Digits(date.getHours()) + ':' + this.padTo2Digits(date.getMinutes());
        },

        padTo2Digits(num) {
            return String(num).padStart(2, '0');
        },
    }));
</script>
@endscript
