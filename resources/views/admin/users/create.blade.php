@extends('layouts.dashboard')

@section('title', 'Add User')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Add User</h1>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left mr-1"></i>Back to Users
    </a>
</div>

@include('admin.users.form', ['action' => route('admin.users.store'), 'method' => 'POST'])
@endsection
