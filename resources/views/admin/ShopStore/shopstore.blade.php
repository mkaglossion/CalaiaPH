@extends('layouts.admin')

@section('title', 'Shop Store Page CMS')

@section('content')
@php
    $resolveShopText = function (string $label, string $fallback = '') use ($texts) {
        $match = collect($texts ?? [])->first(function ($item) use ($label) {
            $itemLabel = strtolower((string) ($item->label ?? $item->title ?? ''));
            return $itemLabel === strtolower($label);
        });
        $value = trim((string) ($match->text ?? $match->content ?? ''));
        return $value !== '' ? $value : $fallback;
    };

    $contentTexts = collect($texts ?? [])->reject(function ($item) {
        $label = strtolower((string) ($item->label ?? $item->title ?? ''));
        return str_starts_with($label, 'shop_page_');
    })->values();
@endphp

<x-admin.cms-manager
    title="Page Information"
    description="Manage the image slider and content displayed on your Shop Store page."
    :images="$images"
    :texts="$contentTexts"
    imageStoreRoute="{{ route('admin.shop-swiper-images.store') }}"
    imageDestroyRoute="/admin/shop/shop-swiper-images"
    textStoreRoute="{{ route('shop-texts.store') }}"
    textDestroyRoute="/admin/shop-texts"
    pageCopyEnabled="true"
    :pageCopyFields="[
        'title' => $resolveShopText('shop_page_title', 'Shop Our Products'),
        'intro' => $resolveShopText('shop_page_intro', 'Farm-fresh produce and quality frozen goods delivered straight from our fields to your table'),
    ]"
    pageCopyStoreRoute="{{ route('admin.shop.copy.store') }}"
/>
@endsection
