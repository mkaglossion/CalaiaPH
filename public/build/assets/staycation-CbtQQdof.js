window.selectedAccommodations={};window.openModal=function(a){const l=document.getElementById(a);l&&(l.classList.remove("hidden"),document.body.classList.add("overflow-hidden"))};window.closeModal=function(a){const l=document.getElementById(a);l&&(l.classList.add("hidden"),document.body.classList.remove("overflow-hidden"))};window.openVariantsModal=function(a){const l=a.dataset.accommodationId,p=a.dataset.accommodationName,u=JSON.parse(a.dataset.variants||"[]");document.getElementById("variants-modal-title").innerText=`Select ${p} Options`,window.activeAccommodation={id:l,name:p};const g=document.getElementById("variants-container");g.innerHTML="";const m=[];window.incrementGuest=function(e){const t=document.getElementById(e);let o=parseInt(t.value)||1;o<10&&(t.value=o+1)},window.decrementGuest=function(e){const t=document.getElementById(e);let o=parseInt(t.value)||1;o>1&&(t.value=o-1)},window.selectStaycation=function(e){const t=document.getElementById(`guests-${e}`).value,o=document.querySelector(`input[name="pets-${e}"]:checked`).value,n=u[e],s=n.staycation?.name||n.name,i=n.id||null,d=parseFloat(n.price||0),r=window.activeAccommodation.id,v=window.activeAccommodation.name;selectedAccommodations[r]||(selectedAccommodations[r]={accommodationName:v,variants:[]}),selectedAccommodations[r].variants.some(c=>c.variantId===i)||selectedAccommodations[r].variants.push({variantId:i,name:s,price:d,guests:t,pets:o}),console.log("--- Staycation Selected ---"),console.log("Accommodation ID:",r),console.log("Variant ID:",i),console.log("Variant Name:",s),console.log("Guests:",t),console.log("Pets:",o),console.log("---------------------------"),typeof updateBookingSummary=="function"&&updateBookingSummary(),Swal.fire({icon:"success",title:"Staycation Selected",text:`You selected "${s}" with ${t} guest(s), Pets: ${o}`}),closeModal("variants-modal")},window.openVRMode=function(){closeModal("variants-modal"),Swal.fire({icon:"info",title:"Under Development",text:"This feature is under development!",confirmButtonText:"OK"}).then(()=>{openModal("variants-modal")})},window.openPhotoGallery=function(e){closeModal("variants-modal");const o=u[e].staycation?.swiper_images||[];if(o.length===0){Swal.fire("No images available!").then(()=>{openModal("variants-modal")});return}const n=`gallery-swiper-${e}`,s=o.map(d=>`
            <div class="swiper-slide">
                <img src="${d.image_path}" class="w-full h-200 object-cover rounded">
            </div>
        `).join(""),i=`
            <div class="swiper ${n}">
                <div class="swiper-wrapper">
                    ${s}
                </div>
                <div class="swiper-pagination"></div>
                <div class="swiper-button-prev text-white! w-5! h-5!"></div>
                <div class="swiper-button-next text-white! w-5! h-5!"></div>
            </div>
        `;Swal.fire({title:"Photo Gallery",html:i,showCloseButton:!0,showConfirmButton:!1,width:"70%",scrollbarPadding:!1,didOpen:()=>{new Swiper(`.${n}`,{loop:!0,autoplay:{delay:3e3,disableOnInteraction:!1},pagination:{el:`.${n} .swiper-pagination`,clickable:!0},navigation:{nextEl:`.${n} .swiper-button-next`,prevEl:`.${n} .swiper-button-prev`}})}}).then(()=>{openModal("variants-modal")})},u.forEach((e,t)=>{const o=e.staycation||{},n=o.texts||[],s=o.swiper_images||[],i=o.name||e.name||"No Name",d=n.filter(c=>c.title==="Features").map(c=>`<li>${c.content}</li>`).join(""),r=n.find(c=>c.title==="Price")?.content||`₱${e.price||0}/night`,v=`swiper-${t}`;m.push({id:v,images:s});const w=s.length?s.map(c=>`
                <div class="swiper-slide">
                    <img src="${c.image_path}" class="w-full h-full object-cover rounded-t-xl">
                </div>
            `).join(""):`
                <div class="swiper-slide flex items-center justify-center bg-gray-200">
                    <span class="text-gray-600">No Image Available</span>
                </div>
            `;g.insertAdjacentHTML("beforeend",`
            <div class="rounded-xl overflow-hidden transition border border-gray-200 shadow-sm hover:shadow-md mb-4">

                <div class="swiper ${v} h-48 md:h-44">
                    <div class="swiper-wrapper">${w}</div>
                    <div class="swiper-pagination" style="--swiper-pagination-color: white;"></div>
                    <div class="swiper-button-prev text-white! w-5! h-5!"></div>
                    <div class="swiper-button-next text-white! w-5! h-5!"></div>
                </div>

                <div class="p-4 space-y-3">
                    <h3 class="font-semibold text-lg">${i}</h3>

                    <div>
                        <h4 class="text-sm font-semibold mb-1">Features</h4>
                        <ul class="list-disc pl-5 text-sm text-gray-600">
                            ${d}
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

                    <div class="font-semibold text-[#7a7d52]">${r}</div>

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
        `)}),openModal("variants-modal"),setTimeout(()=>{m.forEach(e=>{e.images.length<=1||new Swiper(`.${e.id}`,{loop:!0,autoplay:{delay:3e3,disableOnInteraction:!1},pagination:{el:`.${e.id} .swiper-pagination`,clickable:!0},navigation:{nextEl:`.${e.id} .swiper-button-next`,prevEl:`.${e.id} .swiper-button-prev`},observer:!0,observeParents:!0})})},150)};window.selectedAccommodations=window.selectedAccommodations||{};window.updateBookingSummary=function(){const a=document.getElementById("selected-accommodations-summary"),l=document.getElementById("selected-accommodations-count"),p=document.getElementById("total-guests"),u=document.getElementById("total-price"),g=document.getElementById("proceed-btn");let m=0,e=0,t="";const o=Object.values(window.selectedAccommodations||{});o.forEach(n=>{n.variants.forEach(s=>{const i=parseInt(s.guests)||0,d=parseFloat(s.price)||0;m+=i,e+=d,t+=`
                <div class="flex justify-between text-sm text-gray-700">
                    <span>${s.name} (${i} guest${i>1?"s":""})</span>
                    <span>₱ ${d.toLocaleString()}</span>
                </div>
            `})}),a.innerHTML=t,l.textContent=`${o.reduce((n,s)=>n+s.variants.length,0)} selected`,p.textContent=m,u.textContent=`₱ ${e.toLocaleString()}`,console.log("=== Booking Summary Data ==="),o.forEach(n=>{console.log(`Accommodation: ${n.accommodationName}`),n.variants.forEach(s=>{console.log(`  Variant: ${s.name}`),console.log(`    Guests: ${s.guests||0}`),console.log(`    Pets: ${s.pets||"no"}`),console.log(`    Price: ₱${s.price||0}`)})}),console.log("Total Guests:",m),console.log("Total Price: ₱",e),console.log("============================"),a.style.maxHeight=t?"500px":"0",g.disabled=!t};window.proceedToActivities=function(a){console.log("Proceed to activities")};
