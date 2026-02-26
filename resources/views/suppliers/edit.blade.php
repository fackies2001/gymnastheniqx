@extends('layouts.adminlte')

@section('subtitle', 'Supplier')
@section('content_header_title', 'Supplier')
@section('content_body')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <div class="card-title mb-0" style="letter-spacing: 1ch; text-transform: uppercase;" id="title_emp">
                            <h3 class="my-4">Edit Supplier</h3>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('suppliers.update', $supplier->id) }}">
                        <div class="card-body row">
                            @csrf
                            @method('PUT')
                            <div class="col-sm-12 col-md-6">
                                <div class="mb-3">
                                    <x-bootstrap.label for="name" value="Supplier Name" />
                                    <x-bootstrap.input id="name" name="name" required
                                        placeholder="Enter supplier name" :value="old('name', $supplier->name)" />
                                    <x-bootstrap.input-error :messages="$errors->get('name')" />
                                </div>
                                <div class="mb-3">
                                    <x-bootstrap.label for="email" value="Supplier Email" />
                                    <x-bootstrap.input id="email" name="email" type="email" required
                                        placeholder="Enter supplier email" :value="old('email', $supplier->email)" />
                                    <x-bootstrap.input-error :messages="$errors->get('email')" />
                                </div>
                                <div class="mb-3">
                                    <x-bootstrap.label for="contact_number" value="Supplier Phone" />
                                    <x-bootstrap.input id="contact_number" name="contact_number" required
                                        placeholder="Enter supplier phone" :value="old('contact_number', $supplier->contact_number)" />
                                    <x-bootstrap.input-error :messages="$errors->get('contact_number')" />
                                </div>
                                <div class="mb-3">
                                    <x-bootstrap.label for="address" value="Supplier Address" />
                                    <x-bootstrap.input id="address" name="address" required
                                        placeholder="Enter supplier address" :value="old('address', $supplier->address)" />
                                    <x-bootstrap.input-error :messages="$errors->get('address')" />
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex">
                            <a href="{{ route('suppliers.index') }}" class="btn btn-secondary btn-sm">Cancel</a>
                            <button type="submit" class="btn btn-success btn-sm ml-auto">Update Supplier</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
