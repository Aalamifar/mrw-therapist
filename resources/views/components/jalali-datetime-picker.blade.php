@props(['model'])

<div
    x-data="{
        setFromJalali(value) {
            if (!value) return;
            const [datePart, timePart] = value.trim().split(' ');
            const [jy, jm, jd] = datePart.split('/').map(Number);
            const [hh, mm] = (timePart || '00:00').split(':').map(Number);
            const g = window.jalaali.toGregorian(jy, jm, jd);
            const pad = (n) => String(n).padStart(2, '0');
            const iso = `${g.gy}-${pad(g.gm)}-${pad(g.gd)}T${pad(hh)}:${pad(mm)}`;
            $wire.set('{{ $model }}', iso);
        }
    }"
    x-init="() => window.jalaliDatepicker && window.jalaliDatepicker.updateLoad()"
>
    <input
        type="text"
        data-jdp
        data-jdp-time-picker="true"
        x-on:change="setFromJalali($event.target.value)"
        placeholder="برای انتخاب تاریخ کلیک کنید"
        readonly
        class="w-full cursor-pointer rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#061C32]/40 focus:ring-2 focus:ring-[#061C32]/10"
    >
</div>