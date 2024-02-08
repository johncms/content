@extends('system::layout/default')
@section('content')
    @if(!empty($sections))
        <div class="list-group">
            @php /** @var \Johncms\Content\DTO\SectionsListItemDTO[] $sections */ @endphp
            @foreach($sections as $section)
                <a href="{{ $section->url }}" class="list-group-item d-flex justify-content-between align-items-center">
                    {{ $section->name }}
                </a>
            @endforeach
        </div>
    @endif
@endsection
