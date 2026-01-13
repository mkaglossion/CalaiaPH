// Global variables
window.selectedAccommodations = {};

// Open and close modal helpers
window.openModal = function (id) {
    const modal = document.getElementById(id);
    if (!modal) return;

    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
};

window.closeModal = function (id) {
    const modal = document.getElementById(id);
    if (!modal) return;

    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
};

// Global functions
function incrementGuest(id) {
    const input = document.getElementById(id);
    let val = parseInt(input.value) || 1;
    if (val < 10) input.value = val + 1;
}

function decrementGuest(id) {
    const input = document.getElementById(id);
    let val = parseInt(input.value) || 1;
    if (val > 1) input.value = val - 1;
}

// Open variants modal
window.openVariantsModal = function (button) {
    const accommodationId = button.dataset.accommodationId;
    const accommodationName = button.dataset.accommodationName;
    const variants = JSON.parse(button.dataset.variants || '[]');

    document.getElementById('variants-modal-title').innerText = `Select ${accommodationName} Options`;

    window.activeAccommodation = {
        id: accommodationId,
        name: accommodationName
    };

    const container = document.getElementById('variants-container');
    container.innerHTML = '';

    const swiperIds = [];

    window.incrementGuest = function (id) {
        const input = document.getElementById(id);
        let val = parseInt(input.value) || 1;
        if (val < 10) input.value = val + 1;
    };

    window.decrementGuest = function (id) {
        const input = document.getElementById(id);
        let val = parseInt(input.value) || 1;
        if (val > 1) input.value = val - 1;
    };

    window.selectStaycation = function (index) {
        const guests = document.getElementById(`guests-${index}`).value;
        const pets = document.querySelector(`input[name="pets-${index}"]:checked`).value;

        const selectedVariant = variants[index];
        const variantName = selectedVariant.staycation?.name || selectedVariant.name;
        const variantId = selectedVariant.id || null;
        const price = parseFloat(selectedVariant.price || 0);

        const accId = window.activeAccommodation.id;
        const accName = window.activeAccommodation.name;

        if (!selectedAccommodations[accId]) {
            selectedAccommodations[accId] = {
                accommodationName: accName,
                variants: []
            };
        }

        const exists = selectedAccommodations[accId].variants
            .some(v => v.variantId === variantId);

        if (!exists) {
            selectedAccommodations[accId].variants.push({
                variantId,
                name: variantName,
                price,
                guests,
                pets
            });
        }

        console.log('--- Staycation Selected ---');
        console.log('Accommodation ID:', accId);
        console.log('Variant ID:', variantId);
        console.log('Variant Name:', variantName);
        console.log('Guests:', guests);
        console.log('Pets:', pets);
        console.log('---------------------------');

        if (typeof updateBookingSummary === 'function') {
            updateBookingSummary();
        }

        Swal.fire({
            icon: 'success',
            title: 'Staycation Selected',
            text: `You selected "${variantName}" with ${guests} guest(s), Pets: ${pets}`,
        });

        closeModal('variants-modal');
    };

    window.openVRMode = function () {
        closeModal('variants-modal');

        Swal.fire({
            icon: 'info',
            title: 'Under Development',
            text: 'This feature is under development!',
            confirmButtonText: 'OK'
        }).then(() => {
            openModal('variants-modal');
        });
    };

    window.openPhotoGallery = function(index) {
        closeModal('variants-modal');

        const variant = variants[index];
        const images = variant.staycation?.swiper_images || [];

        if (images.length === 0) {
            Swal.fire('No images available!').then(() => {
                openModal('variants-modal');
            });
            return;
        }

        const swiperId = `gallery-swiper-${index}`;
        const slidesHtml = images.map(img => `
            <div class="swiper-slide">
                <img src="${img.image_path}" class="w-full h-200 object-cover rounded">
            </div>
        `).join('');

        const html = `
            <div class="swiper ${swiperId}">
                <div class="swiper-wrapper">
                    ${slidesHtml}
                </div>
                <div class="swiper-pagination"></div>
                <div class="swiper-button-prev text-white! w-5! h-5!"></div>
                <div class="swiper-button-next text-white! w-5! h-5!"></div>
            </div>
        `;

        Swal.fire({
            title: 'Photo Gallery',
            html,
            showCloseButton: true,
            showConfirmButton: false,
            width: '70%',
            scrollbarPadding: false,
            didOpen: () => {
                new Swiper(`.${swiperId}`, {
                    loop: true,
                    autoplay: { delay: 3000, disableOnInteraction: false },
                    pagination: { el: `.${swiperId} .swiper-pagination`, clickable: true },
                    navigation: {
                        nextEl: `.${swiperId} .swiper-button-next`,
                        prevEl: `.${swiperId} .swiper-button-prev`
                    },
                });
            }
        }).then(() => {
            openModal('variants-modal');
        });
    };

    variants.forEach((variant, index) => {
        const staycation = variant.staycation || {};
        const texts = staycation.texts || [];
        const images = staycation.swiper_images || [];
        const name = staycation.name || variant.name || 'No Name';

        const features = texts
            .filter(t => t.title === 'Features')
            .map(t => `<li>${t.content}</li>`)
            .join('');

        const priceText =
            texts.find(t => t.title === 'Price')?.content ||
            `₱${variant.price || 0}/night`;

        const swiperId = `swiper-${index}`;
        swiperIds.push({ id: swiperId, images });

        const slidesHtml = images.length
            ? images.map(img => `
                <div class="swiper-slide">
                    <img src="${img.image_path}" class="w-full h-full object-cover rounded-t-xl">
                </div>
            `).join('')
            : `
                <div class="swiper-slide flex items-center justify-center bg-gray-200">
                    <span class="text-gray-600">No Image Available</span>
                </div>
            `;

        container.insertAdjacentHTML('beforeend', `
            <div class="rounded-xl overflow-hidden transition border border-gray-200 shadow-sm hover:shadow-md mb-4">

                <div class="swiper ${swiperId} h-48 md:h-44">
                    <div class="swiper-wrapper">${slidesHtml}</div>
                    <div class="swiper-pagination" style="--swiper-pagination-color: white;"></div>
                    <div class="swiper-button-prev text-white! w-5! h-5!"></div>
                    <div class="swiper-button-next text-white! w-5! h-5!"></div>
                </div>

                <div class="p-4 space-y-3">
                    <h3 class="font-semibold text-lg">${name}</h3>

                    <div>
                        <h4 class="text-sm font-semibold mb-1">Features</h4>
                        <ul class="list-disc pl-5 text-sm text-gray-600">
                            ${features}
                        </ul>
                    </div>

                    <div>
                        <label class="text-sm font-medium block mb-1">Number of Guests</label>
                        <div class="flex border rounded-lg overflow-hidden w-full">
                            <button class="px-3 bg-gray-100" onclick="decrementGuest('guests-${index}')">−</button>
                            <input id="guests-${index}" value="1" readonly class="flex-1 text-center border-none focus:ring-0">
                            <button class="px-3 bg-gray-100" onclick="incrementGuest('guests-${index}')">+</button>
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium block mb-1">Will you be bringing pets?</label>
                        <div class="flex gap-4">
                            <label class="flex gap-2 items-center">
                                <input type="radio" name="pets-${index}" value="no" checked>
                                <span class="text-sm">No pets</span>
                            </label>
                            <label class="flex gap-2 items-center">
                                <input type="radio" name="pets-${index}" value="yes">
                                <span class="text-sm">Bringing pets (+₱2,000/pet)</span>
                            </label>
                        </div>
                    </div>

                    <div class="font-semibold text-[#7a7d52]">${priceText}</div>

                    <div class="flex flex-col gap-2">
                        <button class="py-2 bg-[#8f5d54] text-white rounded-lg font-semibold hover:bg-[#7a4e48] transition"
                                onclick="selectStaycation(${index})">
                            Select This Staycation
                        </button>
                        <button class="py-2 bg-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-400 transition"
                                onclick="openPhotoGallery(${index})">
                            Photo Gallery
                        </button>
                        <button class="py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition"
                                onclick="openVRMode()">
                            VR Mode
                        </button>
                    </div>
                </div>
            </div>
        `);
    });

    openModal('variants-modal');

    setTimeout(() => {
        swiperIds.forEach(s => {
            if (s.images.length <= 1) return;

            new Swiper(`.${s.id}`, {
                loop: true,
                autoplay: { delay: 3000, disableOnInteraction: false },
                pagination: {
                    el: `.${s.id} .swiper-pagination`,
                    clickable: true,
                },
                navigation: {
                    nextEl: `.${s.id} .swiper-button-next`,
                    prevEl: `.${s.id} .swiper-button-prev`,
                },
                observer: true,
                observeParents: true,
            });
        });
    }, 150);
};

// Initialize global variable
window.selectedAccommodations = window.selectedAccommodations || {};

window.updateBookingSummary = function () {
    const listEl = document.getElementById('selected-accommodations-summary');
    const countEl = document.getElementById('selected-accommodations-count');
    const guestsEl = document.getElementById('total-guests');
    const priceEl = document.getElementById('total-price');
    const proceedBtn = document.getElementById('proceed-btn');

    let totalGuests = 0;
    let totalPrice = 0;
    let html = '';

    const accommodations = Object.values(window.selectedAccommodations || {});

    accommodations.forEach(acc => {
        acc.variants.forEach(variant => {
            const guests = parseInt(variant.guests) || 0;
            const price = parseFloat(variant.price) || 0;

            totalGuests += guests;
            totalPrice += price;

            html += `
                <div class="flex justify-between text-sm text-gray-700">
                    <span>${variant.name} (${guests} guest${guests > 1 ? 's' : ''})</span>
                    <span>₱ ${price.toLocaleString()}</span>
                </div>
            `;
        });
    });

    listEl.innerHTML = html;
    countEl.textContent = `${accommodations.reduce((sum, acc) => sum + acc.variants.length, 0)} selected`;
    guestsEl.textContent = totalGuests;
    priceEl.textContent = `₱ ${totalPrice.toLocaleString()}`;

    console.log('=== Booking Summary Data ===');
    accommodations.forEach(acc => {
        console.log(`Accommodation: ${acc.accommodationName}`);
        acc.variants.forEach(variant => {
            console.log(`  Variant: ${variant.name}`);
            console.log(`    Guests: ${variant.guests || 0}`);
            console.log(`    Pets: ${variant.pets || 'no'}`);
            console.log(`    Price: ₱${variant.price || 0}`);
        });
    });
    console.log('Total Guests:', totalGuests);
    console.log('Total Price: ₱', totalPrice);
    console.log('============================');

    listEl.style.maxHeight = html ? '500px' : '0';

    proceedBtn.disabled = !html;
};

// Other functions from the original JS
// ... (add the rest of the functions like proceedToActivities, etc.)

// For now, add a placeholder for proceedToActivities
window.proceedToActivities = function(event) {
    // Implementation here
    console.log('Proceed to activities');
};