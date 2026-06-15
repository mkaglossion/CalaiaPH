window.selectedAccommodations={},window.openModal=function(e){let t=document.getElementById(e);t&&(t.classList.remove(`hidden`),document.body.classList.add(`overflow-hidden`))},window.closeModal=function(e){let t=document.getElementById(e);t&&(t.classList.add(`hidden`),document.body.classList.remove(`overflow-hidden`))},window.openVariantsModal=function(e){let t=e.dataset.accommodationId,n=e.dataset.accommodationName,r=JSON.parse(e.dataset.variants||`[]`);document.getElementById(`variants-modal-title`).innerText=`Select ${n} Options`,window.activeAccommodation={id:t,name:n};let i=document.getElementById(`variants-container`);i.innerHTML=``;let a=[];window.incrementGuest=function(e){let t=document.getElementById(e),n=parseInt(t.value)||1;n<10&&(t.value=n+1)},window.decrementGuest=function(e){let t=document.getElementById(e),n=parseInt(t.value)||1;n>1&&(t.value=n-1)},window.selectStaycation=function(e){let t=document.getElementById(`guests-${e}`).value,n=document.querySelector(`input[name="pets-${e}"]:checked`).value,i=r[e],a=i.staycation?.name||i.name,o=i.id||null,s=parseFloat(i.price||0),c=window.activeAccommodation.id,l=window.activeAccommodation.name;selectedAccommodations[c]||(selectedAccommodations[c]={accommodationName:l,variants:[]}),selectedAccommodations[c].variants.some(e=>e.variantId===o)||selectedAccommodations[c].variants.push({variantId:o,name:a,price:s,guests:t,pets:n}),console.log(`--- Staycation Selected ---`),console.log(`Accommodation ID:`,c),console.log(`Variant ID:`,o),console.log(`Variant Name:`,a),console.log(`Guests:`,t),console.log(`Pets:`,n),console.log(`---------------------------`),typeof updateBookingSummary==`function`&&updateBookingSummary(),Swal.fire({icon:`success`,title:`Staycation Selected`,text:`You selected "${a}" with ${t} guest(s), Pets: ${n}`}),closeModal(`variants-modal`)},window.openVRMode=function(){closeModal(`variants-modal`),Swal.fire({icon:`info`,title:`Under Development`,text:`This feature is under development!`,confirmButtonText:`OK`}).then(()=>{openModal(`variants-modal`)})},window.openPhotoGallery=function(e){closeModal(`variants-modal`);let t=r[e].staycation?.swiper_images||[];if(t.length===0){Swal.fire(`No images available!`).then(()=>{openModal(`variants-modal`)});return}let n=`gallery-swiper-${e}`,i=`
            <div class="swiper ${n}">
                <div class="swiper-wrapper">
                    ${t.map(e=>`
            <div class="swiper-slide">
                <img src="${e.image_path}" class="w-full h-200 object-cover rounded">
            </div>
        `).join(``)}
                </div>
                <div class="swiper-pagination"></div>
                <div class="swiper-button-prev text-white! w-5! h-5!"></div>
                <div class="swiper-button-next text-white! w-5! h-5!"></div>
            </div>
        `;Swal.fire({title:`Photo Gallery`,html:i,showCloseButton:!0,showConfirmButton:!1,width:`70%`,scrollbarPadding:!1,didOpen:()=>{let e=t.length>1;new Swiper(`.${n}`,{loop:e,autoplay:{delay:3e3,disableOnInteraction:!1},pagination:{el:`.${n} .swiper-pagination`,clickable:!0},navigation:{nextEl:`.${n} .swiper-button-next`,prevEl:`.${n} .swiper-button-prev`}})}}).then(()=>{openModal(`variants-modal`)})},r.forEach((e,t)=>{let n=e.staycation||{},r=n.texts||[],o=n.swiper_images||[],s=n.name||e.name||`No Name`,c=r.filter(e=>e.title===`Features`).map(e=>`<li>${e.content}</li>`).join(``),l=r.find(e=>e.title===`Price`)?.content||`₱${e.price||0}/night`,u=`swiper-${t}`;a.push({id:u,images:o});let d=o.length?o.map(e=>`
                <div class="swiper-slide">
                    <img src="${e.image_path}" class="w-full h-full object-cover rounded-t-xl">
                </div>
            `).join(``):`
                <div class="swiper-slide flex items-center justify-center bg-gray-200">
                    <span class="text-gray-600">No Image Available</span>
                </div>
            `;i.insertAdjacentHTML(`beforeend`,`
            <div class="rounded-xl overflow-hidden transition border border-gray-200 shadow-sm hover:shadow-md mb-4">

                <div class="swiper ${u} h-48 md:h-44">
                    <div class="swiper-wrapper">${d}</div>
                    <div class="swiper-pagination" style="--swiper-pagination-color: white;"></div>
                    <div class="swiper-button-prev text-white! w-5! h-5!"></div>
                    <div class="swiper-button-next text-white! w-5! h-5!"></div>
                </div>

                <div class="p-4 space-y-3">
                    <h3 class="font-semibold text-lg">${s}</h3>

                    <div>
                        <h4 class="text-sm font-semibold mb-1">Features</h4>
                        <ul class="list-disc pl-5 text-sm text-gray-600">
                            ${c}
                        </ul>
                    </div>

                    <div>
                        <label class="text-sm font-medium block mb-1">Number of Guests</label>
                        <div class="flex border rounded-lg overflow-hidden w-full">
                            <button class="px-3 bg-gray-100" onclick="decrementGuest('guests-${t}')">−</button>
                            <input id="guests-${t}" value="1" readonly class="flex-1 text-center border-none focus:ring-0">
                            <button class="px-3 bg-gray-100" onclick="incrementGuest('guests-${t}')">+</button>
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium block mb-1">Will you be bringing pets?</label>
                        <div class="flex gap-4">
                            <label class="flex gap-2 items-center">
                                <input type="radio" name="pets-${t}" value="no" checked>
                                <span class="text-sm">No pets</span>
                            </label>
                            <label class="flex gap-2 items-center">
                                <input type="radio" name="pets-${t}" value="yes">
                                <span class="text-sm">Bringing pets (+₱2,000/pet)</span>
                            </label>
                        </div>
                    </div>

                    <div class="font-semibold text-[#7a7d52]">${l}</div>

                    <div class="flex flex-col gap-2">
                        <button class="py-2 bg-[#8f5d54] text-white rounded-lg font-semibold hover:bg-[#7a4e48] transition"
                                onclick="selectStaycation(${t})">
                            Select This Staycation
                        </button>
                        <button class="py-2 bg-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-400 transition"
                                onclick="openPhotoGallery(${t})">
                            Photo Gallery
                        </button>
                        <button class="py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition"
                                onclick="openVRMode()">
                            VR Mode
                        </button>
                    </div>
                </div>
            </div>
        `)}),openModal(`variants-modal`),setTimeout(()=>{a.forEach(e=>{e.images.length<=1||new Swiper(`.${e.id}`,{loop:e.images.length>1,autoplay:{delay:3e3,disableOnInteraction:!1},pagination:{el:`.${e.id} .swiper-pagination`,clickable:!0},navigation:{nextEl:`.${e.id} .swiper-button-next`,prevEl:`.${e.id} .swiper-button-prev`},observer:!0,observeParents:!0})})},150)},window.selectedAccommodations=window.selectedAccommodations||{},window.updateBookingSummary=function(){let e=document.getElementById(`selected-accommodations-summary`),t=document.getElementById(`selected-accommodations-count`),n=document.getElementById(`total-guests`),r=document.getElementById(`total-price`),i=document.getElementById(`proceed-btn`),a=0,o=0,s=``,c=Object.values(window.selectedAccommodations||{});c.forEach(e=>{e.variants.forEach(e=>{let t=parseInt(e.guests)||0,n=parseFloat(e.price)||0;a+=t,o+=n,s+=`
                <div class="flex justify-between text-sm text-gray-700">
                    <span>${e.name} (${t} guest${t>1?`s`:``})</span>
                    <span>₱ ${n.toLocaleString()}</span>
                </div>
            `})}),e.innerHTML=s,t.textContent=`${c.reduce((e,t)=>e+t.variants.length,0)} selected`,n.textContent=a,r.textContent=`₱ ${o.toLocaleString()}`,console.log(`=== Booking Summary Data ===`),c.forEach(e=>{console.log(`Accommodation: ${e.accommodationName}`),e.variants.forEach(e=>{console.log(`  Variant: ${e.name}`),console.log(`    Guests: ${e.guests||0}`),console.log(`    Pets: ${e.pets||`no`}`),console.log(`    Price: ₱${e.price||0}`)})}),console.log(`Total Guests:`,a),console.log(`Total Price: ₱`,o),console.log(`============================`),e.style.maxHeight=s?`500px`:`0`,i.disabled=!s},window.proceedToActivities=function(e){console.log(`Proceed to activities`)};