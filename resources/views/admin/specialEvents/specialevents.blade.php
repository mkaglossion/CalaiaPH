@extends('layouts.admin')

@section('title', 'Special Events Page CMS')

@section('content')
@php
    $resolveSpecialText = function (string $label, string $fallback = '') use ($texts) {
        $match = collect($texts ?? [])->first(function ($item) use ($label) {
            $itemLabel = strtolower((string) ($item->label ?? $item->title ?? ''));
            return $itemLabel === strtolower($label);
        });

        $value = trim((string) ($match->text ?? $match->content ?? ''));
        return $value !== '' ? $value : $fallback;
    };
@endphp

<x-admin.cms-manager
    title="Page Information"
    description="Manage the image slider and content displayed on your Special Events page."
    :images="$images"
    :texts="$texts"
    imageStoreRoute="{{ route('special-swiper-images.store') }}"
    imageDestroyRoute="/admin/special-swiper-images"
    textStoreRoute="{{ route('special-texts.store') }}"
    textDestroyRoute="/admin/special-texts"
    pageCopyEnabled="true"
    :pageCopyFields="[
        'label' => '',
        'title' => $resolveSpecialText('special_events_title', 'Our Special Events'),
        'intro' => $resolveSpecialText('special_events_intro', 'From intimate weddings to grand corporate celebrations, our beautiful farm setting provides the perfect backdrop for your special occasion.'),
    ]"
    pageCopyStoreRoute="{{ route('admin.specialEvents.copy.store') }}"
/>
@endsection
