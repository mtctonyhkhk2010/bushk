<div>
    <x-header title="{{ $route->name }}" subtitle="{{ $route->dest_tc }}" separator/>
    <div id="map" class="h-[35vh]" x-data="map">

    </div>
    <div x-data="stop_list">
        <ul class="timeline timeline-vertical">
            <template x-for="stop in stops[1]">
                <li x-on:click="getETA(stop.pivot.sequence)">
                    <div class="timeline-middle">
                        <span x-text="stop.pivot.sequence + 1"></span>
                    </div>
                    <div class="timeline-end timeline-box">
                        <h5 x-text="stop.name_tc"></h5>
                        <span x-text="'$' + stop.pivot.fare"></span>
                    </div>
                    <div class="collapse-content">
                        <p>hello</p>
                    </div>
                    <hr/>
                </li>
            </template>
        </ul>
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
@endassets

@script
<script>
    Alpine.data('map', () => ({
        stops_position: @js($stops_position),

        init() {
            let map = L.map('map').setView([this.stops_position[0].latitude, this.stops_position[0].longitude], 16);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);
            this.stops_position.forEach((stop) => {
                L.marker([stop.latitude, stop.longitude]).addTo(map);
            });
        },
    }));

    Alpine.data('stop_list', () => ({
        route_name: @js($route->name),
        service_type: @js($route->service_type),
        stops: @js($stops),
        companies: @js($route->companies->keyBy('id')),
        etas: [],

        getETA(sequence) {
            this.etas = [];
            for (let key in this.companies) {
                if (!this.companies.hasOwnProperty(key)) continue;

                const company = this.companies[key];

                console.log(this.stops[company.id])
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
                console.log(path)
                delete axios.defaults.headers.common["X-Requested-With"];
                axios({
                    method: 'get',
                    url: path,
                })
                .then((response) => {
                    console.log(response.data);
                    response.data.data.forEach((item) => {
                        this.etas.push({
                            eta: item.eta,
                            co: item.co,
                        });
                    })
                });
            }
        },
    }));
</script>
@endscript
