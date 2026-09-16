document.addEventListener('DOMContentLoaded', function () {
    const categorySelect = document.getElementById('category');
    if (!categorySelect) return;

    const motorcycleTypeField = document.getElementById('motorcycle-type-field');
    const transmissionField = document.getElementById('transmission-field');
    const transmissionSelect = document.getElementById('transmission');
    const motorcycleTypeSelect = document.getElementById('motorcycle_type');
    const sportsField = document.getElementById('sports-field');
    const mapElement = document.getElementById('location-map');

    function toggleMotorcycleFields() {
        const isMotorcycle = categorySelect.value === 'motorcycle';

        if (motorcycleTypeField) {
            motorcycleTypeField.style.display = isMotorcycle ? 'block' : 'none';
        }

        if (transmissionField) {
            transmissionField.style.display = isMotorcycle ? 'none' : 'block';
        }

        if (transmissionSelect) {
            transmissionSelect.required = !isMotorcycle;
        }

        if (sportsField) {
            sportsField.style.display = isMotorcycle ? 'none' : 'block';
        }

        if (isMotorcycle && motorcycleTypeSelect) {
            syncTransmissionFromMotorcycleType();
        }
    }

    function syncTransmissionFromMotorcycleType() {
        if (!transmissionSelect || !motorcycleTypeSelect) return;
        transmissionSelect.value = motorcycleTypeSelect.value === 'scooter' ? 'automatic' : 'manual';
    }

    categorySelect.addEventListener('change', toggleMotorcycleFields);
    if (motorcycleTypeSelect) {
        motorcycleTypeSelect.addEventListener('change', syncTransmissionFromMotorcycleType);
    }
    toggleMotorcycleFields();

    if (!mapElement || !window.L) return;

    const map = L.map('location-map').setView([16.6159, 120.3209], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    let marker = null;
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');
    const locationNameInput = document.getElementById('location_name');
    const locateStatus = document.getElementById('locate-status');
    let geocodeRequestId = 0;
    let addressSearchTimeout = null;
    let lastSearchedAddress = '';
    const addressCacheKey = 'WayGo_address_geocode_cache';
    let addressCache = {};

    try {
        addressCache = JSON.parse(sessionStorage.getItem(addressCacheKey) || '{}');
    } catch (error) {
        addressCache = {};
    }

    function updateLocationName(lat, lng) {
        if (!locationNameInput) return;

        const requestId = ++geocodeRequestId;
        locationNameInput.value = 'Finding address...';
        if (locateStatus) locateStatus.textContent = 'Finding address...';

        const params = new URLSearchParams({
            format: 'jsonv2',
            lat: lat.toFixed(8),
            lon: lng.toFixed(8),
            addressdetails: '1'
        });

        fetch('https://nominatim.openstreetmap.org/reverse?' + params.toString(), {
            headers: { 'Accept': 'application/json' }
        })
            .then(function (response) {
                if (!response.ok) throw new Error('Address lookup failed.');
                return response.json();
            })
            .then(function (data) {
                if (requestId !== geocodeRequestId) return;
                locationNameInput.value = data.display_name || 'Address not found';
                if (locateStatus) locateStatus.textContent = 'Address found.';
            })
            .catch(function () {
                if (requestId !== geocodeRequestId) return;
                locationNameInput.value = '';
                if (locateStatus) locateStatus.textContent = 'Could not find the address. You can enter it manually.';
            });
    }

    function searchAddress(query) {
        const trimmedQuery = query.trim();
        const normalizedQuery = trimmedQuery.toLowerCase().replace(/\s+/g, ' ');
        if (!locationNameInput || normalizedQuery.length < 5 || normalizedQuery === lastSearchedAddress) return;

        lastSearchedAddress = normalizedQuery;

        if (addressCache[normalizedQuery]) {
            setPin(addressCache[normalizedQuery].lat, addressCache[normalizedQuery].lng, false);
            if (locateStatus) locateStatus.textContent = 'Location found and pinned.';
            return;
        }

        const requestId = ++geocodeRequestId;
        if (locateStatus) locateStatus.textContent = 'Searching for location...';

        const params = new URLSearchParams({
            format: 'jsonv2',
            q: trimmedQuery,
            countrycodes: 'ph',
            limit: '1',
            addressdetails: '1'
        });

        fetch('https://nominatim.openstreetmap.org/search?' + params.toString(), {
            headers: { 'Accept': 'application/json' }
        })
            .then(function (response) {
                if (!response.ok) throw new Error('Address search failed.');
                return response.json();
            })
            .then(function (results) {
                if (requestId !== geocodeRequestId) return;
                if (!results.length) throw new Error('Location not found.');

                const result = results[0];
                const coordinates = {
                    lat: parseFloat(result.lat),
                    lng: parseFloat(result.lon)
                };
                addressCache[normalizedQuery] = coordinates;
                try {
                    sessionStorage.setItem(addressCacheKey, JSON.stringify(addressCache));
                } catch (error) {
                }
                setPin(coordinates.lat, coordinates.lng, false);
                if (locateStatus) locateStatus.textContent = 'Location found and pinned.';
            })
            .catch(function () {
                if (requestId !== geocodeRequestId) return;
                if (locateStatus) locateStatus.textContent = 'Location not found. Try a more specific address.';
            });
    }

    function setPin(lat, lng, shouldFindAddress) {
        const latLng = [lat, lng];

        if (latitudeInput) latitudeInput.value = lat.toFixed(8);
        if (longitudeInput) longitudeInput.value = lng.toFixed(8);

        if (marker) {
            marker.setLatLng(latLng);
        } else {
            marker = L.marker(latLng).addTo(map);
        }

        map.setView(latLng, 15);

        if (shouldFindAddress !== false) {
            updateLocationName(lat, lng);
        }
    }

    const savedLat = latitudeInput ? parseFloat(latitudeInput.value) : NaN;
    const savedLng = longitudeInput ? parseFloat(longitudeInput.value) : NaN;
    if (Number.isFinite(savedLat) && Number.isFinite(savedLng)) {
        setPin(savedLat, savedLng, false);
    }

    map.on('click', function (event) {
        setPin(event.latlng.lat, event.latlng.lng);
    });

    if (locationNameInput) {
        locationNameInput.addEventListener('input', function () {
            clearTimeout(addressSearchTimeout);
            const query = this.value;
            addressSearchTimeout = setTimeout(function () {
                searchAddress(query);
            }, 1200);
        });

        locationNameInput.addEventListener('blur', function () {
            clearTimeout(addressSearchTimeout);
            searchAddress(this.value);
        });

        locationNameInput.addEventListener('keydown', function (event) {
            if (event.key !== 'Enter') return;
            event.preventDefault();
            clearTimeout(addressSearchTimeout);
            searchAddress(this.value);
        });
    }

    const locateBtn = document.getElementById('locate-btn');

    if (locateBtn) {
        locateBtn.addEventListener('click', function () {
            if (!navigator.geolocation) {
                if (locateStatus) locateStatus.textContent = 'Geolocation not supported.';
                return;
            }

            if (locateStatus) locateStatus.textContent = 'Locating...';

            navigator.geolocation.getCurrentPosition(
                function (position) {
                    setPin(position.coords.latitude, position.coords.longitude);
                    if (locateStatus) locateStatus.textContent = 'Location found.';
                },
                function (error) {
                    if (locateStatus) locateStatus.textContent = 'Could not locate: ' + error.message;
                },
                { enableHighAccuracy: false, timeout: 15000, maximumAge: 60000 }
            );
        });
    }
});
