@props(['features' => [], 'showHeader' => true, 'ulClass' => 'grid grid-cols-1 gap-1 text-xs', 'liClass' => 'px-2 py-1 bg-gray-50 rounded text-xs', 'headerClass' => 'text-sm font-semibold mb-2'])

@if(!empty($features))
  <div class="mb-4">
    @if($showHeader)
      <h6 class="{{ $headerClass }}" style="font-family: 'Gotham Book', sans-serif;">Features</h6>
    @endif
    <ul class="{{ $ulClass }}" style="font-family: 'Gotham Book', sans-serif;">
      @foreach($features as $feature)
        @if(trim($feature))
          <li class="{{ $liClass }}">{{ trim($feature) }}</li>
        @endif
      @endforeach
    </ul>
  </div>
@endif