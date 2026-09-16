document.addEventListener('DOMContentLoaded', function () {
    const galleryTrack = document.getElementById('gallery-track');
    const thumbs = document.querySelectorAll('#gallery-thumbs .gallery-thumb');
    const prevBtn = document.getElementById('gallery-prev');
    const nextBtn = document.getElementById('gallery-next');
    const counter = document.getElementById('gallery-counter');

    if (galleryTrack && thumbs.length) {
        const slideCount = thumbs.length;
        let currentIndex = 0;

        function goToSlide(index) {
            currentIndex = (index + slideCount) % slideCount;
            galleryTrack.style.transform = 'translateX(-' + (currentIndex * 100) + '%)';
            counter.textContent = (currentIndex + 1) + ' / ' + slideCount;
            thumbs.forEach(function (thumb, i) {
                thumb.classList.toggle('active', i === currentIndex);
            });
        }

        if (prevBtn) prevBtn.addEventListener('click', function () { goToSlide(currentIndex - 1); });
        if (nextBtn) nextBtn.addEventListener('click', function () { goToSlide(currentIndex + 1); });

        thumbs.forEach(function (thumb) {
            thumb.addEventListener('click', function () {
                goToSlide(parseInt(this.dataset.index, 10));
            });
        });

        let touchStartX = 0;
        galleryTrack.addEventListener('touchstart', function (event) {
            touchStartX = event.touches[0].clientX;
        });
        galleryTrack.addEventListener('touchend', function (event) {
            const diff = touchStartX - event.changedTouches[0].clientX;
            if (Math.abs(diff) > 40) {
                goToSlide(diff > 0 ? currentIndex + 1 : currentIndex - 1);
            }
        });
    }

    const pickupMapElement = document.getElementById('pickup-map');
    if (window.L && pickupMapElement) {
        const mapLat = Number(pickupMapElement.dataset.lat);
        const mapLng = Number(pickupMapElement.dataset.lng);
        const popupText = pickupMapElement.dataset.popupText || 'Vehicle location';
        const pickupMap = L.map('pickup-map').setView([mapLat, mapLng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(pickupMap);

        L.marker([mapLat, mapLng])
            .addTo(pickupMap)
            .bindPopup(popupText)
            .openPopup();
    }

    const authModal = document.getElementById('auth-modal');
    const openAuthBtn = document.getElementById('open-auth-modal-btn');
    if (authModal && openAuthBtn) {
        openAuthBtn.addEventListener('click', function () {
            authModal.showModal();
        });

        const closeAuthBtn = document.getElementById('close-auth-modal-btn');
        if (closeAuthBtn) {
            closeAuthBtn.addEventListener('click', function () {
                authModal.close();
            });
        }

        authModal.showModal();
    }

    const pickupRadio = document.querySelector('input[name="fulfillment_type"][value="pickup"]');
    const deliveryRadio = document.querySelector('input[name="fulfillment_type"][value="delivery"]');
    const pickupCard = document.getElementById('pickup-card');
    const deliveryCard = document.getElementById('delivery-card');
    const deliveryFields = document.getElementById('delivery-fields');

    if (pickupRadio && deliveryRadio) {
        function toggleDeliveryFields() {
            const isDelivery = deliveryRadio.checked;
            if (deliveryFields) deliveryFields.classList.toggle('hidden-field', !isDelivery);
            if (pickupCard) pickupCard.classList.toggle('selected', !isDelivery);
            if (deliveryCard) deliveryCard.classList.toggle('selected', isDelivery);
        }

        pickupRadio.addEventListener('change', toggleDeliveryFields);
        deliveryRadio.addEventListener('change', toggleDeliveryFields);
        toggleDeliveryFields();
    }

    const timeInput = document.getElementById('delivery_time');
    const confirmTimeBtn = document.getElementById('confirm-time-btn');
    const confirmedNote = document.getElementById('time-confirmed-note');

    if (timeInput && confirmTimeBtn && confirmedNote) {
        function markTimeUnconfirmed() {
            confirmTimeBtn.classList.remove('confirmed');
            confirmedNote.classList.remove('visible');
        }

        confirmTimeBtn.addEventListener('click', function () {
            if (!timeInput.value) {
                timeInput.focus();
                return;
            }
            confirmTimeBtn.classList.add('confirmed');
            confirmedNote.classList.add('visible');
        });

        timeInput.addEventListener('input', markTimeUnconfirmed);
    }

    const regionSelect = document.getElementById('delivery_region');
    const provinceSelect = document.getElementById('delivery_province');
    const citySelect = document.getElementById('delivery_city');
    const barangaySelect = document.getElementById('delivery_barangay');

    if (regionSelect && provinceSelect && citySelect && barangaySelect) {
        function setSelectMessage(select, message) {
            select.replaceChildren();
            const option = document.createElement('option');
            option.value = '';
            option.textContent = message;
            select.appendChild(option);
        }

        function setSelectOptions(select, placeholder, items, includeCode) {
            select.replaceChildren();
            const placeholderOption = document.createElement('option');
            placeholderOption.value = '';
            placeholderOption.textContent = placeholder;
            select.appendChild(placeholderOption);

            items.forEach(function (item) {
                const option = document.createElement('option');
                option.value = item.name;
                option.textContent = item.name;
                if (includeCode) option.dataset.code = item.code;
                select.appendChild(option);
            });
        }

        fetch('https://psgc.gitlab.io/api/regions/')
            .then(function (response) { return response.json(); })
            .then(function (regions) {
                setSelectOptions(regionSelect, 'Select Region', regions, true);
            });

        regionSelect.addEventListener('change', function () {
            const code = this.options[this.selectedIndex].dataset.code;
            setSelectMessage(provinceSelect, 'Loading...');
            setSelectMessage(citySelect, 'Select province first');
            setSelectMessage(barangaySelect, 'Select city first');

            if (!code) {
                setSelectMessage(provinceSelect, 'Select region first');
                provinceSelect.disabled = true;
                citySelect.disabled = true;
                barangaySelect.disabled = true;
                return;
            }

            provinceSelect.disabled = false;
            fetch('https://psgc.gitlab.io/api/regions/' + code + '/provinces/')
                .then(function (response) { return response.json(); })
                .then(function (provinces) {
                    setSelectOptions(provinceSelect, 'Select Province', provinces, true);
                });
        });

        provinceSelect.addEventListener('change', function () {
            const code = this.options[this.selectedIndex].dataset.code;
            setSelectMessage(citySelect, 'Loading...');
            setSelectMessage(barangaySelect, 'Select city first');

            if (!code) {
                setSelectMessage(citySelect, 'Select province first');
                citySelect.disabled = true;
                barangaySelect.disabled = true;
                return;
            }

            citySelect.disabled = false;
            fetch('https://psgc.gitlab.io/api/provinces/' + code + '/cities-municipalities/')
                .then(function (response) { return response.json(); })
                .then(function (cities) {
                    setSelectOptions(citySelect, 'Select City/Municipality', cities, true);
                });
        });

        citySelect.addEventListener('change', function () {
            const code = this.options[this.selectedIndex].dataset.code;
            setSelectMessage(barangaySelect, 'Loading...');

            if (!code) {
                setSelectMessage(barangaySelect, 'Select city first');
                barangaySelect.disabled = true;
                return;
            }

            barangaySelect.disabled = false;
            fetch('https://psgc.gitlab.io/api/cities-municipalities/' + code + '/barangays/')
                .then(function (response) { return response.json(); })
                .then(function (barangays) {
                    setSelectOptions(barangaySelect, 'Select Barangay', barangays, false);
                });
        });
    }

    const bookingModal = document.getElementById('booking-modal');
    const openBookingBtn = document.getElementById('open-booking-modal-btn');
    const closeBookingBtn = document.getElementById('close-booking-modal-btn');
    const bookingForm = document.getElementById('booking-form');

    if (bookingModal && openBookingBtn && closeBookingBtn && bookingForm) {
        openBookingBtn.addEventListener('click', function () {
            bookingModal.showModal();
        });

        closeBookingBtn.addEventListener('click', function () {
            bookingModal.close();
        });

        bookingModal.addEventListener('click', function (event) {
            if (event.target === bookingModal) {
                event.stopPropagation();
            }
        });

        bookingModal.addEventListener('cancel', function (event) {
            event.preventDefault();
        });

        const draftKey = 'WayGo_booking_draft_' + (document.querySelector('input[name="vehicle_id"]')?.value || '');

        function saveDraft() {
            const data = {};
            new FormData(bookingForm).forEach(function (value, key) {
                if (data[key]) {
                    if (!Array.isArray(data[key])) data[key] = [data[key]];
                    data[key].push(value);
                } else {
                    data[key] = value;
                }
            });
            sessionStorage.setItem(draftKey, JSON.stringify(data));
        }

        function restoreDraft() {
            const saved = sessionStorage.getItem(draftKey);
            if (!saved) return;

            const data = JSON.parse(saved);
            Object.keys(data).forEach(function (key) {
                const values = Array.isArray(data[key]) ? data[key] : [data[key]];
                const fields = bookingForm.querySelectorAll('[name="' + key + '"]');

                fields.forEach(function (field) {
                    if (field.type === 'checkbox' || field.type === 'radio') {
                        field.checked = values.includes(field.value);
                    } else {
                        field.value = values[0];
                    }
                });
            });

            if (deliveryRadio && deliveryRadio.checked) {
                deliveryRadio.dispatchEvent(new Event('change'));
            }
        }

        bookingForm.addEventListener('input', saveDraft);
        bookingForm.addEventListener('change', saveDraft);
        bookingForm.addEventListener('submit', function (e) {
            const fulfillmentType = document.querySelector('input[name="fulfillment_type"]:checked').value;
            
            document.querySelectorAll('.field-error').forEach(el => el.remove());
            document.querySelectorAll('.has-error').forEach(el => el.classList.remove('has-error'));

            if (fulfillmentType === 'delivery') {
                const errors = {};
                
                const deliveryTime = document.getElementById('delivery_time').value.trim();
                if (!deliveryTime) {
                    errors['delivery_time'] = 'Please choose a delivery time.';
                }

                const contactNumber = document.getElementById('contact_number').value.trim();
                if (!contactNumber) {
                    errors['contact_number'] = 'Contact number is required for delivery.';
                }

                const deliveryRegion = document.getElementById('delivery_region').value.trim();
                if (!deliveryRegion) {
                    errors['delivery_region'] = 'Region is required.';
                }

                const deliveryProvince = document.getElementById('delivery_province').value.trim();
                if (!deliveryProvince) {
                    errors['delivery_province'] = 'Province is required.';
                }

                const deliveryCity = document.getElementById('delivery_city').value.trim();
                if (!deliveryCity) {
                    errors['delivery_city'] = 'City is required.';
                }

                const deliveryBarangay = document.getElementById('delivery_barangay').value.trim();
                if (!deliveryBarangay) {
                    errors['delivery_barangay'] = 'Barangay is required.';
                }

                const deliveryAddress = document.getElementById('delivery_address_line').value.trim();
                if (!deliveryAddress) {
                    errors['delivery_address_line'] = 'Street/House No./Landmark is required.';
                }

                if (Object.keys(errors).length > 0) {
                    e.preventDefault();

                    Object.keys(errors).forEach(fieldName => {
                        const field = document.getElementById(fieldName);
                        if (field) {
                            field.classList.add('has-error');
                            field.style.borderColor = '#dc3545';
                            
                            const errorSpan = document.createElement('span');
                            errorSpan.className = 'field-error';
                            errorSpan.textContent = errors[fieldName];
                            
                            if (fieldName === 'delivery_time') {
                                const timeInputRow = field.closest('.time-input-row');
                                if (timeInputRow) {
                                    timeInputRow.parentNode.insertBefore(errorSpan, timeInputRow.nextSibling);
                                } else {
                                    field.parentNode.insertBefore(errorSpan, field.nextSibling);
                                }
                            } else {
                                field.parentNode.insertBefore(errorSpan, field.nextSibling);
                            }
                        }
                    });

                    return false;
                }
            }

            e.preventDefault();
            
            bookingModal.close();
            
            if (typeof showProcessingModal === 'function') {
                showProcessingModal();
            }
            
            setTimeout(() => {
                bookingForm.submit();
            }, 100);
            
            sessionStorage.removeItem(draftKey);
        });

        const deliveryFieldNames = [
            'delivery_time',
            'contact_number',
            'delivery_region',
            'delivery_province',
            'delivery_city',
            'delivery_barangay',
            'delivery_address_line'
        ];

        deliveryFieldNames.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (field) {
                field.addEventListener('input', function () {
                    clearFieldError(this, fieldName);
                });
                
                field.addEventListener('change', function () {
                    clearFieldError(this, fieldName);
                });
                
                field.addEventListener('blur', function () {
                    clearFieldError(this, fieldName);
                });
            }
        });

        function clearFieldError(field, fieldName) {
            field.classList.remove('has-error');
            field.style.borderColor = '';  // Remove inline border color
            
            let errorToRemove = field.nextElementSibling;
            if (fieldName === 'delivery_time') {
                const timeInputRow = field.closest('.time-input-row');
                if (timeInputRow) {
                    errorToRemove = timeInputRow.nextElementSibling;
                }
            }
            
            if (errorToRemove && errorToRemove.classList.contains('field-error')) {
                errorToRemove.remove();
            }
        }

        restoreDraft();
    }
});