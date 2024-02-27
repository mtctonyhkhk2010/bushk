navigator.permissions.query({name:'geolocation'})
    .then((result) => {
            if (result.state === 'granted') {
                window.watch_position_id = navigator.geolocation.watchPosition((position) => {
                    window.coords = position.coords;
                    const event = new CustomEvent("position-updated");
                    document.dispatchEvent(event);
                });
            } else {
                console.log('Browser location services disabled', navigator);
                navigator.geolocation.getCurrentPosition(() => {})
            }
        }, () => {
            console.log('Browser permissions services unavailable', navigator);
        }
    );
