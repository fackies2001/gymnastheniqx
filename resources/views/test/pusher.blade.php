@extends('layouts.adminlte')

@section('subtitle', 'test-pusher')
@section('content_header_title', 'test-pusher')
{{-- @section('content_header_subtitle', 'Dashboard') --}}
@section('content_body')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('pusher.store') }}" method="POST">
                @csrf
                <input class="form-control" type="text" name="message">
                <button class="btn btn-success" type="submit">push</button>
            </form>
        </div>
    </div>

    @push('js')
        <script type="module">
            console.log('Listening...');

            window.Echo.channel('notify-channel') // MUST match the channel in NotifyEvent
                .listen('.notify-event', (e) => { // dot prefix required for broadcastAs()
                    console.log('✔️ Received from Pusher:', e.message);
                });
        </script>
    @endpush
@stop
