@extends('layouts.admin')

@section('title', 'Our Farm Page CMS')

@section('content')
@php
    $resolveFarmText = function (string $label, string $fallback = '') use ($texts) {
        $match = collect($texts ?? [])->first(function ($item) use ($label) {
            $itemLabel = strtolower((string) ($item->label ?? $item->title ?? ''));
            return $itemLabel === strtolower($label);
        });
        $value = trim((string) ($match->text ?? $match->content ?? ''));
        return $value !== '' ? $value : $fallback;
    };

    $contentTexts = collect($texts ?? [])->reject(function ($item) {
        $label = strtolower((string) ($item->label ?? $item->title ?? ''));
        return str_starts_with($label, 'farm_page_');
    })->values();
@endphp

<x-admin.cms-manager
    title="Page Information"
    description="Manage the image slider and content displayed on your Our Farm page."
    :images="$images"
    :texts="$contentTexts"
    imageStoreRoute="{{ route('farm-swiper-images.store') }}"
    imageDestroyRoute="/admin/farm-swiper-images"
    textStoreRoute="{{ route('farm-texts.store') }}"
    textDestroyRoute="/admin/farm-texts"
    pageCopyEnabled="true"
    :pageCopyFields="[
        'title' => $resolveFarmText('farm_page_title', 'Our Sustainable Farming'),
        'intro' => $resolveFarmText('farm_page_intro', 'Discover how we raise livestock and grow produce with respect for nature and animal welfare'),
    ]"
    pageCopyStoreRoute="{{ route('admin.farm.copy.store') }}"
/>
@endsection
