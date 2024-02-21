import './bootstrap';

window.fetchEta = async (company, stop_code, route_name = null, service_type = null, gtfs_id = null, bound = null) =>
{
    let path;
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

    const response = await fetch(path);
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
