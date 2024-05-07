<x-mail::message>
    # Đơn Hàng Đã Được Gửi

    Đơn hàng của bạn đã được gửi đi!


    <x-mail::button :url="$url">
        Xem Đơn Hàng
    </x-mail::button>


    Cảm ơn,<br>
    {{ config('app.name') }}
</x-mail::message>