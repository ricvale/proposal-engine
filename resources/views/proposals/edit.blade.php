@extends('layouts.app')

@section('content')
    <h1>{{ $proposal->title }}</h1>
    <p class="sub">Edit each section, then mark as final and download the PDF.</p>

    <livewire:proposal-editor :proposal="$proposal" />
@endsection
