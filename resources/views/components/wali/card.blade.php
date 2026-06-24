<div {{ $attributes->merge([
    'class' => 'bg-white rounded-3xl border border-slate-100 shadow-sm'
]) }}>
    {{ $slot }}
</div>