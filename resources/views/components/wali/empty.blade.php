<div class="bg-white rounded-3xl p-8 text-center border border-slate-100">

    <svg xmlns="http://www.w3.org/2000/svg"
         fill="none"
         viewBox="0 0 24 24"
         stroke-width="1.5"
         stroke="currentColor"
         class="w-10 h-10 mx-auto text-slate-300">

        <path stroke-linecap="round"
              stroke-linejoin="round"
              d="M19.5 14.25v-2.625a3.375 3.375 0 10-6.75 0v2.625m-3.75 0h11.25A1.125 1.125 0 0121.375 15.375V19.5A1.125 1.125 0 0120.25 20.625H3.75A1.125 1.125 0 012.625 19.5V15.375A1.125 1.125 0 013.75 14.25H7.5Z"/>
    </svg>

    <div class="mt-3 font-semibold text-slate-700">
        {{ $title ?? 'Belum Ada Data' }}
    </div>

    <div class="text-[12px] text-slate-500 mt-1">
        {{ $description ?? 'Data belum tersedia' }}
    </div>

</div>