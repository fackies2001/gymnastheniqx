@extends('layouts.adminlte')

@section('subtitle', 'User Management')
@section('content_header_title', 'User Management')
{{-- @section('content_header_subtitle', 'Dashboard') --}}
@section('content_body')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Registration</h5>
                </div>
                <form method="POST" action="{{ route('employee.register') }}">
                    <div class="card-body">
                        @csrf

                        <!-- Name -->
                        <div>
                            <x-bootstrap.label for="name" :value="__('Name')" />
                            <x-bootstrap.input id="name" class="block mt-1 w-full" type="text" name="name"
                                :value="old('name')" required autofocus autocomplete="name" />
                            <x-bootstrap.input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Email Address -->
                        <div class="mt-4">
                            <x-bootstrap.label for="email" :value="__('Email')" />
                            <x-bootstrap.input id="email" class="block mt-1 w-full" type="email" name="email"
                                :value="old('email')" required autocomplete="username" />
                            <x-bootstrap.input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <!-- Password -->
                        <div class="mt-4">
                            <x-bootstrap.label for="password" :value="__('Password')" />

                            <x-bootstrap.input id="password" class="block mt-1 w-full" type="password" name="password"
                                required autocomplete="new-password" />

                            <x-bootstrap.input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <!-- Confirm Password -->
                        <div class="mt-4">
                            <x-bootstrap.label for="password_confirmation" :value="__('Confirm Password')" />

                            <x-bootstrap.input id="password_confirmation" class="block mt-1 w-full" type="password"
                                name="password_confirmation" required autocomplete="new-password" />

                            <x-bootstrap.input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            {{-- <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                                href="{{ route('login') }}">
                                {{ __('Already registered?') }}
                            </a> --}}


                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <div class="btn btn-sm btn-primary" type="submit">Submit</div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
