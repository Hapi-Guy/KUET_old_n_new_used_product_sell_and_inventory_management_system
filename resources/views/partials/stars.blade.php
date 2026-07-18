@php($value = (float) ($value ?? 0))
<span class="text-warning" title="{{ number_format($value, 1) }} / 5">
    @for ($i = 1; $i <= 5; $i++)
        <i class="bi {{ $i <= round($value) ? 'bi-star-fill' : 'bi-star' }}"></i>
    @endfor
</span>
