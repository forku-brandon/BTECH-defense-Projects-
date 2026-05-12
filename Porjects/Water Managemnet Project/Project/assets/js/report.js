document.addEventListener('DOMContentLoaded', () => {
    
    // Elements
    const reportForm = document.getElementById('reportForm');
    const typeRadios = document.querySelectorAll('input[name="issue_type"]');
    const billWarning = document.getElementById('billWarning');
    const description = document.getElementById('description');
    const charCount = document.getElementById('charCount');
    const getLocationBtn = document.getElementById('getLocationBtn');
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    const mapPreviewWrapper = document.getElementById('mapPreviewWrapper');
    const locationAccuracy = document.getElementById('locationAccuracy');
    const photoInput = document.getElementById('photos');
    const imagePreviewContainer = document.getElementById('imagePreviewContainer');
    const submitBtn = document.getElementById('submitBtn');

    let previewMap = null;
    let marker = null;

    // Handle Disruption Type selection to show warning
    typeRadios.forEach(radio => {
        radio.addEventListener('change', (e) => {
            if (e.target.value === 'water_suspension_bill') {
                billWarning.style.display = 'flex';
            } else {
                billWarning.style.display = 'none';
            }
        });
    });

    // Character counter for description
    if (description) {
        description.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
    }

    // Photo preview and validation
    if (photoInput) {
        photoInput.addEventListener('change', function(e) {
            imagePreviewContainer.innerHTML = ''; // Clear existing
            
            // Limit to 3 files
            if (this.files.length > 3) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Too many files',
                    text: 'You can only upload a maximum of 3 photos.'
                });
                // Reset input
                const dt = new DataTransfer();
                for (let i = 0; i < 3; i++) {
                    dt.items.add(this.files[i]);
                }
                this.files = dt.files;
            }

            Array.from(this.files).forEach(file => {
                // Check size (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    Swal.fire({
                        icon: 'error',
                        title: 'File too large',
                        text: `${file.name} exceeds the 5MB limit.`
                    });
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'img-preview';
                    imagePreviewContainer.appendChild(img);
                }
                reader.readAsDataURL(file);
            });
        });
    }

    // Geolocation handling
    const initPreviewMap = (lat, lng, accuracy) => {
        mapPreviewWrapper.style.display = 'block';
        
        if (!previewMap) {
            previewMap = L.map('mapPreview').setView([lat, lng], 16);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap'
            }).addTo(previewMap);

            marker = L.marker([lat, lng], { draggable: true }).addTo(previewMap);
            
            // Handle drag event to update coordinates
            marker.on('dragend', function(e) {
                const pos = marker.getLatLng();
                latInput.value = pos.lat.toFixed(6);
                lngInput.value = pos.lng.toFixed(6);
                locationAccuracy.innerHTML = "Location adjusted manually";
            });
        } else {
            previewMap.setView([lat, lng], 16);
            marker.setLatLng([lat, lng]);
        }

        // Show accuracy
        if (accuracy) {
            locationAccuracy.innerHTML = `Accuracy: &plusmn;${Math.round(accuracy)} meters`;
        }
        
        // Force map resize to fix Leaflet rendering issues in hidden containers
        setTimeout(() => { previewMap.invalidateSize(); }, 100);
    };

    if (getLocationBtn) {
        getLocationBtn.addEventListener('click', () => {
            const originalText = getLocationBtn.innerHTML;
            getLocationBtn.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Locating...';
            getLocationBtn.disabled = true;

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        const accuracy = position.coords.accuracy;

                        latInput.value = lat.toFixed(6);
                        lngInput.value = lng.toFixed(6);

                        initPreviewMap(lat, lng, accuracy);

                        getLocationBtn.innerHTML = '<i class="ph-bold ph-check"></i> Location Captured';
                        getLocationBtn.classList.remove('btn-outline');
                        getLocationBtn.classList.add('btn-primary');
                        getLocationBtn.disabled = false;
                    },
                    (error) => {
                        console.error("Geolocation error:", error);
                        getLocationBtn.innerHTML = originalText;
                        getLocationBtn.disabled = false;
                        
                        let errorMsg = "Could not get your location. ";
                        if (error.code === 1) errorMsg = "Permission denied. Please allow location access in your browser or drop a pin manually.";
                        else if (error.code === 2) errorMsg = "Location unavailable. Please check your GPS/Network settings.";
                        else if (error.code === 3) errorMsg = "Location request timed out.";

                        Swal.fire({
                            icon: 'warning',
                            title: 'Location Error',
                            text: errorMsg,
                            footer: 'We will center the map on Bamenda. Please drag the pin to your exact location.'
                        });

                        // Fallback to default Bamenda coordinates
                        latInput.value = MAP_CENTER_LAT;
                        lngInput.value = MAP_CENTER_LNG;
                        initPreviewMap(MAP_CENTER_LAT, MAP_CENTER_LNG, null);
                        locationAccuracy.innerHTML = "Default location. Please drag pin.";
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                );
            } else {
                Swal.fire('Error', 'Geolocation is not supported by your browser.', 'error');
                getLocationBtn.innerHTML = originalText;
                getLocationBtn.disabled = false;
            }
        });
    }

    // Form Submission
    if (reportForm) {
        reportForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Validation
            if (!latInput.value || !lngInput.value) {
                Swal.fire('Required', 'Please capture your location first.', 'warning');
                return;
            }

            const formData = new FormData(reportForm);
            
            // Show loading state
            submitBtn.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Submitting...';
            submitBtn.disabled = true;

            try {
                const response = await fetch('api/submit_report.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();

                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Report Submitted!',
                        html: `
                            <p>Thank you for reporting this issue.</p>
                            <div style="background: #f3f4f6; padding: 15px; border-radius: 8px; margin-top: 15px;">
                                <span style="font-size: 0.9rem; color: #6b7280; display: block; margin-bottom: 5px;">Your Tracking ID is:</span>
                                <strong style="font-size: 1.5rem; color: #0a58ca; letter-spacing: 1px;">${data.tracking_id}</strong>
                            </div>
                            <p style="font-size: 0.85rem; margin-top: 15px; color: #6b7280;">Please save this ID to check the status of your report later.</p>
                        `,
                        confirmButtonText: 'Check Status',
                        confirmButtonColor: '#0a58ca',
                        allowOutsideClick: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = `status.php?id=${data.tracking_id}`;
                        }
                    });
                } else {
                    Swal.fire('Error', data.message || 'Failed to submit report.', 'error');
                    submitBtn.innerHTML = '<i class="ph-bold ph-paper-plane-tilt"></i> Submit Report';
                    submitBtn.disabled = false;
                }
            } catch (error) {
                console.error("Submission error:", error);
                Swal.fire('Error', 'A network error occurred while submitting.', 'error');
                submitBtn.innerHTML = '<i class="ph-bold ph-paper-plane-tilt"></i> Submit Report';
                submitBtn.disabled = false;
            }
        });
    }
});
