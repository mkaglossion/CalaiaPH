@extends('layouts.admin')

@section('title', 'Our Farm Page CMS')

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
    :pageCopyEnabled="$cmsConfig['pageCopyEnabled']"
    :pageCopyFields="$cmsConfig['pageCopyFields']"
    :pageCopyMeta="$cmsConfig['pageCopyMeta']"
    :pageCopyStoreRoute="$cmsConfig['pageCopyStoreRoute']"
    :imageReorderRoute="$cmsConfig['imageReorderRoute']"
/>

@include('admin.ourFarm.partials.sections-manager', ['sections' => $farmSections])
@endsection
