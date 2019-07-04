@foreach($logs as $log)
    @lang('log.metadata', $log->getMetadata())

    <ul>
        @foreach($log->getModified() as $attribute => $modified)
            <li>@lang('log.fields.' . $attribute) {!! trans_choice('log.modified', intval(isset($modified['old'])), $modified) !!} </li>
        @endforeach
    </ul>
@endforeach