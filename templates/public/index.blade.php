@extends('system::layout/default')
@section('content')
    @if(!empty($sections))
        <div class="list-group mb-4">
            @php /** @var \Johncms\Content\DTO\SectionsListItemDTO[] $sections */ @endphp
            @foreach($sections as $section)
                <a href="{{ $section->url }}" class="list-group-item d-flex justify-content-between align-items-center">
                    {{ $section->name }}
                </a>
            @endforeach
        </div>
    @endif

    @if(!empty($elements))
        <div class="list-group mb-3">
            @foreach($elements as $element)
                <a href="{{ $element->url }}" class="list-group-item list-group-item-action">{{ $element->name }}</a>
            @endforeach
        </div>
        <div>
            {!! $pagination !!}
        </div>
    @endif

    @if(empty($elements) && empty($sections))
        <div class="alert alert-info">
            {{ __('The List is Empty') }}
        </div>
    @endif
@endsection
