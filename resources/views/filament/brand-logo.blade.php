@php
    $isLoginPage = request()->routeIs('filament.admin.auth.login');
@endphp

<div class="flex flex-col items-center justify-center text-center space-y-2">
    <div class="font-extrabold text-2xl tracking-wide text-primary-600 dark:text-primary-400 leading-none">
        SIMAK-PM
    </div>
    @if ($isLoginPage)
        <div class="text-[10px] font-medium text-gray-500 dark:text-gray-400 leading-tight max-w-48 tracking-tight mb-8">
            Sistem Informasi Model Asesmen Kinerja <br> Pendidikan Menengah
        </div>
        <br>
    @endif
</div>
