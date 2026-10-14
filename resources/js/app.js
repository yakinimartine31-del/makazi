import './bootstrap';

import Alpine from 'alpinejs';
import $ from 'jquery';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

L.Icon.Default.mergeOptions({
    iconRetinaUrl: new URL('leaflet/dist/images/marker-icon-2x.png', import.meta.url).href,
    iconUrl: new URL('leaflet/dist/images/marker-icon.png', import.meta.url).href,
    shadowUrl: new URL('leaflet/dist/images/marker-shadow.png', import.meta.url).href,
});

window.Alpine = Alpine;
window.$ = window.jQuery = $;

Alpine.start();

document.querySelectorAll('input[type="file"][data-max-file-size]').forEach((input) => {
    input.addEventListener('change', () => {
        const maxFileSize = Number(input.dataset.maxFileSize);
        const maxFiles = Number(input.dataset.maxFiles);
        const oversizedFile = Array.from(input.files ?? []).some((file) => file.size > maxFileSize);
        const selectedFiles = Array.from(input.files ?? []);

        if (maxFiles && input.files.length > maxFiles) {
            input.setCustomValidity(`You can upload a maximum of ${maxFiles} files.`);
        } else if (oversizedFile) {
            input.setCustomValidity('Each image or video must be no larger than 2 MB.');
        } else {
            input.setCustomValidity('');
        }

        if (input.hasAttribute('data-multiple-media')) {
            const preview = input.parentElement.querySelector('[data-media-preview]');
            const status = input.parentElement.querySelector('[data-media-selection-status]');
            if (!preview || !status) return;

            preview.querySelectorAll('img[src^="blob:"], video[src^="blob:"]').forEach((media) => {
                URL.revokeObjectURL(media.src);
            });
            preview.replaceChildren();
            status.textContent = selectedFiles.length
                ? `${selectedFiles.length} file${selectedFiles.length === 1 ? '' : 's'} selected.`
                : 'No media selected.';

            selectedFiles.forEach((file) => {
                const figure = document.createElement('figure');
                figure.className = 'owner-media-preview';
                const mediaUrl = URL.createObjectURL(file);
                const mediaElement = file.type.startsWith('video/')
                    ? document.createElement('video')
                    : document.createElement('img');

                mediaElement.src = mediaUrl;
                mediaElement.setAttribute('aria-label', file.name);
                if (mediaElement instanceof HTMLVideoElement) {
                    mediaElement.controls = true;
                    mediaElement.preload = 'metadata';
                } else {
                    mediaElement.alt = file.name;
                }

                const caption = document.createElement('figcaption');
                caption.textContent = file.name;
                figure.append(mediaElement, caption);
                preview.append(figure);
            });
        }
    });
});

const mapElements = Array.from(document.querySelectorAll('[data-map-picker]'));

function setMapStatus(element, message, isError = false) {
    const status = document.getElementById(element.dataset.status);

    if (status) {
        status.textContent = message;
        status.classList.toggle('text-danger', isError);
        status.classList.toggle('text-muted', !isError);
    }
}

mapElements.forEach((element) => {
    const latitudeInput = document.getElementById(element.dataset.latitudeInput);
    const longitudeInput = document.getElementById(element.dataset.longitudeInput);
    const preview = document.getElementById(element.dataset.preview);
    const mapLink = document.getElementById(element.dataset.mapLink);
    const latitude = Number(latitudeInput?.value);
    const longitude = Number(longitudeInput?.value);
    const hasSavedLocation = latitudeInput?.value !== '' && longitudeInput?.value !== ''
        && Number.isFinite(latitude) && Number.isFinite(longitude);
    const initialPosition = hasSavedLocation
        ? [latitude, longitude]
        : [-6.369028, 34.888822];
    const map = L.map(element).setView(initialPosition, hasSavedLocation ? 15 : 6);

    let hasTileError = false;
    const tileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19,
    });
    tileLayer.on('tileerror', () => {
        if (hasTileError) return;
        hasTileError = true;
        setMapStatus(element, 'Map tiles could not be loaded. Check your connection; the Google Maps preview below can still show the address.', true);
    });
    tileLayer.addTo(map);

    const marker = L.marker(initialPosition, { draggable: true });

    const updateGoogleMapPreview = () => {
        if (!mapLink) return;

        const hasCoordinates = latitudeInput.value !== '' && longitudeInput.value !== ''
            && Number.isFinite(Number(latitudeInput.value)) && Number.isFinite(Number(longitudeInput.value));
        const address = ['landmark', 'area_id', 'ward_id', 'district_id', 'region_id']
            .map((id) => {
                const field = document.getElementById(id);
                if (field instanceof HTMLSelectElement) {
                    return field.value ? field.selectedOptions[0]?.textContent.trim() : '';
                }

                return field?.value.trim();
            })
            .filter(Boolean)
            .join(', ');
        const query = hasCoordinates
            ? `${latitudeInput.value},${longitudeInput.value}`
            : address || 'Tanzania';
        const encodedQuery = encodeURIComponent(query);
        const mapsUrl = `https://www.google.com/maps/search/?api=1&query=${encodedQuery}`;

        if (preview) {
            const embedUrl = `https://www.google.com/maps?q=${encodedQuery}&output=embed`;
            if (preview.src !== embedUrl) preview.src = embedUrl;
        }
        if (mapLink.href !== mapsUrl) mapLink.href = mapsUrl;
    };

    ['landmark', 'area_id', 'ward_id', 'district_id', 'region_id'].forEach((id) => {
        const field = document.getElementById(id);
        field?.addEventListener(field instanceof HTMLSelectElement ? 'change' : 'input', updateGoogleMapPreview);
    });
    updateGoogleMapPreview();

    if (hasSavedLocation) {
        marker.addTo(map);
        setMapStatus(element, `Selected location: ${latitudeInput.value}, ${longitudeInput.value}`);
    } else {
        setMapStatus(element, 'Click the map to place a pin.');
    }

    const updateLocation = (position) => {
        latitudeInput.value = position.lat.toFixed(8);
        longitudeInput.value = position.lng.toFixed(8);
        marker.setLatLng(position).addTo(map);
        setMapStatus(element, `Selected location: ${latitudeInput.value}, ${longitudeInput.value}`);
        updateGoogleMapPreview();
    };

    map.on('click', (event) => updateLocation(event.latlng));
    marker.on('dragend', (event) => updateLocation(event.target.getLatLng()));
});

import('admin-lte/node_modules/bootstrap/dist/js/bootstrap.bundle.min.js')
    .then(() => import('admin-lte/dist/js/adminlte.min.js'));
