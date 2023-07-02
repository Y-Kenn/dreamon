@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => '']) }}>
        @if($status === 'passwords.sent')
            送信しました
        @else
            {{ $status }}
        @endif
    </div>
@endif
