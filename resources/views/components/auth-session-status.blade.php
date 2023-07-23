@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => '']) }}>
        @if($status === 'passwords.sent')
            送信しました
        @elseif($status === 'passwords.reset')
            パスワードをリセットしました。
        @else
            {{ $status }}
        @endif
    </div>
@endif
