@extends('layouts.admin')

@section('title', 'Where to Stay Page CMS')

@section('content')
@php
    $staycationPageCopyLabel = trim((string) (($pageCopyTexts['staycation_page_label'] ?? '') ?? '')) !== ''
        ? $pageCopyTexts['staycation_page_label']
        : 'Staycation Options';
    $staycationPageCopyTitle = trim((string) (($pageCopyTexts['staycation_page_title'] ?? '') ?? '')) !== ''
        ? $pageCopyTexts['staycation_page_title']
        : 'All Glamping & Camping';
    $staycationPageCopyIntro = trim((string) (($pageCopyTexts['staycation_page_intro'] ?? '') ?? '')) !== ''
        ? $pageCopyTexts['staycation_page_intro']
        : 'Browse every room directly on the page. The category modals are still available below if you want the full feature list and gallery.';
@endphp

<x-admin.cms-manager
    title="Page Information"
    description="Manage the image slider and content displayed on your Where to Stay page."
    :images="$images"
    :texts="$texts"
    imageStoreRoute="{{ route('stay-swiper-images.store') }}"
    imageDestroyRoute="/admin/stay-swiper-images"
    textStoreRoute="{{ route('stay-texts.store') }}"
    textDestroyRoute="/admin/stay-texts"
    pageCopyEnabled="true"
    :pageCopyFields="[
        'label' => $staycationPageCopyLabel,
        'title' => $staycationPageCopyTitle,
        'intro' => $staycationPageCopyIntro,
    ]"
    pageCopyStoreRoute="{{ route('admin.stay.copy.store') }}"
/>
@endsection
