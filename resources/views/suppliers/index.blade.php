@extends('layouts.adminlte')

@section('subtitle', 'Suppliers')
@section('content_header_title', 'Suppliers')
@section('content_header_subtitle', 'All Suppliers')

@section('content_body')
    <div class="row">
        @foreach ($suppliers as $supplier)
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-centered">
                        <h5><a href="{{ route('suppliers.show', $supplier->id) }}" title="click to view supplier's products">
                                {{ $supplier->name }}
                            </a></h5>
                        <hr>
                        <p>{{ $supplier->email }}</p>
                        <p>{{ $supplier->phone }}</p>
                        <p>{{ $supplier->address }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
