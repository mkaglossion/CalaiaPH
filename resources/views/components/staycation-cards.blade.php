{{-- Accommodation Cards --}}
<section class="row g-4 g-md-5 justify-content-center" aria-labelledby="accommodation-heading">
  <div class="col-12">
    <h2 id="accommodation-heading" class="visually-hidden">Available accommodation options</h2>
  </div>

  @forelse($accommodations as $accommodation)
    <div class="col-12 col-md-5">
      <article class="card h-100 border-0 overflow-hidden"
                style="box-shadow:0 8px 18px rgba(0,0,0,.08);">
        {{-- Inset image --}}
        <div class="px-4 pt-4">
          <div class="position-relative overflow-hidden shadow-sm">
            <img src="{{ $accommodation['image'] }}"
                 alt="{{ $accommodation['alt'] }}"
                 class="w-100" style="height:260px; object-fit:cover;">
          </div>
        </div>

        <div class="card-body px-5 pb-5 pt-4">
          <h3 class="text-center fw-bold mb-3 text-uppercase"
              style="letter-spacing:.02em; color:#8f5d54;">
            {{ $accommodation['title'] }}
          </h3>

          {{-- thin divider like mock --}}
          <div class="mx-auto mb-3" style="height:1px; width:100%; background:#e7eddc;"></div>

          <ul class="list-unstyled text-center mb-4">
            @foreach($accommodation['features'] as $feature)
              <li class="mb-2" style="font-size:.82rem; color:#8f5d54; text-transform:uppercase; letter-spacing:.02em;">
                {{ $feature }}
              </li>
            @endforeach
          </ul>

          <div class="d-flex justify-content-center">
            <button type="button"
                    class="btn px-4 py-2 rounded-pill fw-semibold"
                    style="background:#8f5d54; color:#fff; font-size:.85rem;"
                    data-bs-toggle="modal" data-bs-target="#{{ $accommodation['modal'] }}"
                    onmouseover="this.style.backgroundColor='#a0675a'"
                    onmouseout="this.style.backgroundColor='#8f5d54'">
              LEARN MORE
            </button>
          </div>
        </div>
      </article>
    </div>
  @empty
    <div class="col-12">
      <p class="text-center">No accommodations available.</p>
    </div>
  @endforelse
</section>