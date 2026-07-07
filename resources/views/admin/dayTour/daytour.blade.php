@extends('layouts.admin')

@section('title', 'Day Tour Page CMS')

@section('content')
<x-admin.cms-manager
    title="Page Information"
    description="Manage the image slider and content displayed on your Day Tour page."
    :images="$images"
    :texts="$texts"
    imageStoreRoute="{{ route('day-tour-swiper-images.store') }}"
    imageDestroyRoute="/admin/day-tour-swiper-images"
    textStoreRoute="{{ route('day-tour-texts.store') }}"
    textDestroyRoute="/admin/day-tour-texts"
/>
@endsection
