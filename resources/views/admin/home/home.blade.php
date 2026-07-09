@extends('layouts.admin')

@section('title', 'Home Page CMS')

@section('content')
<x-admin.cms-manager
    :title="$cmsConfig['title']"
    :description="$cmsConfig['description']"
    :images="$images"
    :texts="$filteredTexts"
    :imageStoreRoute="$cmsConfig['imageStoreRoute']"
    :imageDestroyRoute="$cmsConfig['imageDestroyRoute']"
    :textStoreRoute="$cmsConfig['textStoreRoute']"
    :textDestroyRoute="$cmsConfig['textDestroyRoute']"
/>
@endsection
