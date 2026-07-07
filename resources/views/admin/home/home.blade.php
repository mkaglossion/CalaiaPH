@extends('layouts.admin')

@section('title', 'Home Page CMS')

@section('content')
<x-admin.cms-manager
    title="Page Information"
    description="Manage the image slider and content displayed on your Home page."
    :images="$images"
    :texts="$texts"
    imageStoreRoute="{{ route('home-swiper-images.store') }}"
    imageDestroyRoute="/admin/home-swiper-images"
    textStoreRoute="{{ route('home-texts.store') }}"
    textDestroyRoute="/admin/home-texts"
/>
@endsection
