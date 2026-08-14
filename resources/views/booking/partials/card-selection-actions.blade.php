@php
    $selectionExpression = $selectionExpression ?? '{}';
@endphp

@include('booking.partials.wishlist-button', [
    'itemType' => $itemType,
    'itemId' => $itemId,
    'selectionExpression' => $selectionExpression,
])

@include('booking.partials._checkmark-badge', [
    'condition' => $selectedCondition,
    'position' => 'right',
    'positionClass' => $checkPositionClass ?? null,
])
