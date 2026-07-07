@extends('layouts.admin')

@section('title', 'Food & Beverage Page CMS')

@section('content')
<x-admin.cms-manager
    title="Page Information"
    description="Manage the image slider and content displayed on your Food & Beverage page."
    :images="$images"
    :texts="$texts"
    imageStoreRoute="{{ route('food-swiper-images.store') }}"
    imageDestroyRoute="/admin/food-swiper-images"
    textStoreRoute="{{ route('food-texts.store') }}"
    textDestroyRoute="/admin/food-texts"
/>
@endsection
