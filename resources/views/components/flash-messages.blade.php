{{-- Drop this at the top of any page's content to render the standard
     session flashes with the shared alert styling — replaces the
     @if(session('status'))...@endif / @if(session('error'))...@endif
     pairs that used to be repeated on every page. --}}
@if (session('status'))
    <x-alert type="success" class="mb-4">{{ session('status') }}</x-alert>
@endif
@if (session('error'))
    <x-alert type="error" class="mb-4">{{ session('error') }}</x-alert>
@endif
@if (session('warning'))
    <x-alert type="warning" class="mb-4">{{ session('warning') }}</x-alert>
@endif
