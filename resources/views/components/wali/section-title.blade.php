<div class="flex items-center justify-between mb-3">

    <div>
        <h3 class="font-bold text-[15px] text-slate-900">
            {{ $title }}
        </h3>

        @isset($subtitle)
            <p class="text-[11px] text-slate-500">
                {{ $subtitle }}
            </p>
        @endisset
    </div>

    @isset($action)
        {{ $action }}
    @endisset

</div>