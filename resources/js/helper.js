window.fetchEta = async (company, stop_code, route_name = null, service_type = null, gtfs_id = null, bound = null, nlb_id = null) =>
{
    let path;
    let fetch_data = {};
    let etas = [];
    if (company === 'kmb') {
        path = `https://data.etabus.gov.hk/v1/transport/kmb/eta/${stop_code}/${route_name}/${service_type}`;
    }
    if (company === 'ctb') {
        path = `https://rt.data.gov.hk//v2/transport/citybus/eta/CTB/${stop_code}/${route_name}`;
    }
    if (company === 'gmb') {
        path = `https://data.etagmb.gov.hk/eta/route-stop/${gtfs_id}/${stop_code}`;
    }
    if (company === 'lrtfeeder') {
        path = `https://rt.data.gov.hk/v1/transport/mtr/bus/getSchedule`;
        fetch_data.headers = {
            "Content-Type": "application/json",
        };
        fetch_data.method = "POST";
        fetch_data.body = JSON.stringify({routeName: route_name, language: "zh"});
    }
    if (company === 'nlb') {
        path = `https://rt.data.gov.hk/v1/transport/nlb/stop.php?action=estimatedArrivals`;
        fetch_data.method = "POST";
        fetch_data.body = JSON.stringify({routeId: nlb_id, stopId: stop_code, language: "zh"});
    }

    const response = await fetch(path, fetch_data);
    const data = await response.json();

    if (company === 'kmb' || company === 'ctb') {
        data.data.forEach((item) => {
            if (item.eta === "" || item.eta === null || item.dir !== bound) return;

            etas.push({
                timestamp: Date.parse(item.eta),
                eta: item.eta,
                co: item.co,
                remark: item.rmk_tc,
            });
        });
    }

    if (company === 'gmb') {
        data.data.forEach((item) => {
            if ((bound === "I" && item.route_seq === 1) ||
                (bound === "O" && item.route_seq === 2)) return;

            item.eta.forEach((eta) => {
                if (eta.timestamp === "" || eta.timestamp === null) return;

                etas.push({
                    timestamp: Date.parse(eta.timestamp),
                    eta: eta.timestamp,
                    co: 'gmb',
                    remark: eta.remarks_tc,
                });
            })
        });
    }

    if (company === 'nlb') {
        data.estimatedArrivals.forEach((item) => {
            if (item.estimatedArrivalTime === "" || item.estimatedArrivalTime === null) return;

            etas.push({
                timestamp: Date.parse(item.estimatedArrivalTime),
                eta: item.estimatedArrivalTime,
                co: 'nlb',
                remark: '',
            });
        });
    }

    if (company === 'lrtfeeder') {
        data.busStop.forEach((item) => {
            if (item.busStopId === stop_code)
            {
                item.bus.forEach((bus) => {
                    let timeObject = new Date();
                    const milliseconds = bus.departureTimeInSecond * 1000;
                    timeObject = new Date(timeObject.getTime() + milliseconds);

                    etas.push({
                        timestamp: timeObject.getTime(),
                        eta: timeObject.toISOString(),
                        co: 'lrtfeeder',
                        remark: '',
                    });
                })
            }
        });
    }

    return etas;
}

window.distance = (lat1, lon1, lat2, lon2) => {
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
