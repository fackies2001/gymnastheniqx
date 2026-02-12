@extends('layouts.adminlte')

@section('subtitle', 'Supplier')
@section('content_header_title', 'Supplier')
{{-- @section('content_header_subtitle', 'Dashboard') --}}
@section('content_body')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <div class="card-title mb-0" style="letter-spacing: 1ch; text-transform: uppercase;" id="title_emp">
                            <h3 class="my-4">Create Supplier</h3>
                        </div>
                        <!-- Button to trigger the Create Supplier modal -->
                        {{-- <a href="#" class="btn btn-sm btn-primary ml-auto" data-toggle="modal"
                        data-target="#createSupplierModal">Create Supplier</a> --}}
                    </div>
                    <form method="POST" action="{{ route('suppliers.store') }}">
                        <div class="card-body row">
                            @csrf
                            <div class="col-sm-12 @can('can-create-supplier-api') col-md-6 @endcan">


                                <div class="mb-3">
                                    <x-bootstrap.label for="name" value="Supplier Name" />
                                    <x-bootstrap.input id="name" name="name" required
                                        placeholder="Enter supplier name" />
                                    <x-bootstrap.input-error :messages="$errors->get('name')" />
                                </div>
                                <div class="mb-3">
                                    <x-bootstrap.label for="email" value="Supplier Email" />
                                    <x-bootstrap.input id="email" name="email" type="email" required
                                        placeholder="Enter supplier email" />
                                    <x-bootstrap.input-error :messages="$errors->get('email')" />
                                </div>
                                <div class="mb-3">
                                    <x-bootstrap.label for="phone" value="Supplier Phone" />
                                    <x-bootstrap.input id="phone" name="phone" required
                                        placeholder="Enter supplier phone" />
                                    <x-bootstrap.input-error :messages="$errors->get('phone')" />
                                </div>
                                <div class="mb-3">
                                    <x-bootstrap.label for="address" value="Supplier Address" />
                                    <x-bootstrap.input id="address" name="address" required
                                        placeholder="Enter supplier address" />
                                    <x-bootstrap.input-error :messages="$errors->get('address')" />
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex">
                            <button type="submit" class="btn btn-success btn-sm ml-auto" id="createSupplierSubmit"> Save
                                Supplier</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@stop
