@props(['model'])

<div
    x-data="{
        dateStr: '',
        timeStr: '00:00',
        emit() {
            if (!this.dateStr) return;
            const [jy, jm, jd] = this.dateStr.split('/').map(Number);
            const [hh, mm] = this.timeStr.split(':').map(Number);
            const g = window.jalaali.toGregorian(jy, jm, jd);
            const pad = (n) => String(n).padStart(2, '0');
            const iso = `${g.gy}-${pad(g.gm)}-${pad(g.gd)}T${pad(hh)}:${pad(mm)}`;
            $wire.set('{{ $model }}', iso);
        }
    }"
    x-init="() => window.jalaliDatepicker && window.jalaliDatepicker.updateLoad()"
    class="flex gap-2"
>
    <input
        type="text"
        data-jdp
        x-model="dateStr"
        x-on:change="emit()"
        placeholder="انتخاب تاریخ"
        readonly
        class="flex-1 cursor-pointer rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#061C32]/40 focus:ring-2 focus:ring-[#061C32]/10"
    >

    <input
        type="time"
        x-model="timeStr"
        x-on:change="emit()"
        class="w-28 rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#061C32]/40 focus:ring-2 focus:ring-[#061C32]/10"
    >
</div>