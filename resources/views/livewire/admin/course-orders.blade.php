<?php

use Livewire\Volt\Component;
use App\Models\CourseOrder;

new class extends Component
{
    public string $filter = 'pending';

    public function orders()
    {
        return CourseOrder::with('course')
            ->when($this->filter !== 'all', fn ($q) => $q->where('status', $this->filter))
            ->latest()
            ->get();
    }

    public function approve($id)
    {
        CourseOrder::findOrFail($id)->update(['status' => 'approved']);
    }

    public function reject($id)
    {
        CourseOrder::findOrFail($id)->update(['status' => 'rejected']);
    }

    /** استایل هر وضعیت — یک‌جا و قابل نگهداری */
    public function statusInfo(string $status): array
    {
        return match ($status) {
            'pending' => [
                'label' => 'در انتظار تأیید',
                'short' => 'در انتظار',
                'class' => 'bg-[#faf0d2] text-[#b98a10]',
                'dot'   => 'bg-[#b98a10]',
                'icon'  => 'clock',
            ],
            'approved' => [
                'label' => 'تأیید شده',
                'short' => 'تأییدشده',
                'class' => 'bg-[#e2f6e7] text-[#16a34a]',
                'dot'   => 'bg-[#16a34a]',
                'icon'  => 'check-circle',
            ],
            'rejected' => [
                'label' => 'رد شده',
                'short' => 'ردشده',
                'class' => 'bg-[#fdeaea] text-[#dc2626]',
                'dot'   => 'bg-[#dc2626]',
                'icon'  => 'x-mark',
            ],
            default => [
                'label' => 'نامشخص',
                'short' => 'نامشخص',
                'class' => 'bg-slate-100 text-slate-500',
                'dot'   => 'bg-slate-400',
                'icon'  => 'sparkles',
            ],
        };
    }

    /** تعداد سفارش‌ها برای هر pill فیلتر */
    public function statusCounts(): array
    {
        $all = CourseOrder::all();

        return [
            'pending'  => $all->where('status', 'pending')->count(),
            'approved' => $all->where('status', 'approved')->count(),
            'rejected' => $all->where('status', 'rejected')->count(),
            'total'    => $all->count(),
        ];
    }

    /** تبدیل اعداد به ارقام فارسی */
    public function fa(int|string $number): string
    {
        return str_replace(
            range('0', '9'),
            ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'],
            (string) $number
        );
    }
}; ?>

@php $counts = $this->statusCounts(); @endphp

<div
    class="space-y-6"
    x-data="{
        previewOpen: false,
        previewSrc: null,
        openPreview(src) {
            this.previewSrc = src;
            this.previewOpen = true;
        },
        closePreview() {
            this.previewOpen = false;
            this.previewSrc = null;
        }
    }"
>

    {{-- ═══════════ هدر با آمار ═══════════ --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <span class="grid h-12 w-12 place-items-center rounded-2xl bg-[#faf0d2] text-[#b98a10]">
                <x-icon name="inbox" class="h-6 w-6" />
            </span>
            <div>
                <h1 class="text-xl font-extrabold">سفارش‌های دوره</h1>
                <p class="text-xs text-slate-500">
                    {{ $this->fa($counts['total']) }} سفارش ثبت شده
                    @if ($counts['pending'] > 0)
                        · <span class="font-bold text-[#b98a10]">{{ $this->fa($counts['pending']) }} در انتظار بررسی</span>
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- ═══════════ فیلتر وضعیت (Pills) ═══════════ --}}
    <div class="overflow-x-auto rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] p-2 shadow-sm">
        <div class="flex gap-2">
            @foreach ([
                'pending'  => ['در انتظار', 'clock', $counts['pending']],
                'approved' => ['تأییدشده', 'check-circle', $counts['approved']],
                'rejected' => ['ردشده', 'x-mark', $counts['rejected']],
                'all'      => ['همه', 'sparkles', $counts['total']],
            ] as $key => [$label, $icon, $count])
                <button
                    wire:click="$set('filter', '{{ $key }}')"
                    class="relative flex shrink-0 items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold transition {{ $filter === $key ? 'bg-gradient-to-br from-[#f0d060] to-[#d7a92b] text-[#0b1826] shadow-md' : 'text-slate-600 hover:bg-[#faf5ec]' }}"
                >
                    <x-icon name="{{ $icon }}" class="h-4 w-4" />
                    <span>{{ $label }}</span>
                    <span class="rounded-full px-2 py-0.5 text-xs font-extrabold {{ $filter === $key ? 'bg-white/30 text-[#0b1826]' : 'bg-[#eee4d9] text-slate-500' }}">
                        {{ $this->fa($count) }}
                    </span>
                </button>
            @endforeach
        </div>
    </div>

    {{-- ═══════════ لیست سفارش‌ها ═══════════ --}}
    <div class="overflow-hidden rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] shadow-sm">

        @forelse ($this->orders() as $order)
            @php
                $status = $this->statusInfo($order->status);
                $receiptUrl = route('admin.orders.receipt', $order->id);
            @endphp

            <div
                wire:key="order-{{ $order->id }}"
                class="border-b border-[#f0e7dc] px-5 py-4 transition last:border-b-0 hover:bg-[#faf5ec] sm:px-6"
            >
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

                    {{-- اطلاعات سفارش --}}
                    <div class="flex min-w-0 flex-1 items-start gap-3">

                        {{-- Thumbnail رسید (قابل کلیک برای پیش‌نمایش) --}}
                        @if ($order->receipt_image)
                            <button
                                type="button"
                                @click="openPreview('{{ $receiptUrl }}')"
                                class="group relative h-20 w-20 shrink-0 overflow-hidden rounded-xl border border-[#eee4d9] bg-white shadow-sm transition hover:border-[#d7a92b]/50 hover:shadow-md"
                                title="مشاهده رسید"
                            >
                                <img
                                    src="{{ $receiptUrl }}"
                                    alt="رسید پرداخت"
                                    class="h-full w-full object-cover transition group-hover:scale-105"
                                >
                                <span class="absolute inset-0 flex items-center justify-center bg-black/0 transition group-hover:bg-black/40">
                                    <span class="grid h-8 w-8 place-items-center rounded-full bg-white/90 text-[#b98a10] opacity-0 transition group-hover:opacity-100">
                                        <x-icon name="eye" class="h-4 w-4" />
                                    </span>
                                </span>
                            </button>
                        @else
                            <span class="grid h-20 w-20 shrink-0 place-items-center rounded-xl bg-[#faf0d2] text-[#b98a10]">
                                <x-icon name="document" class="h-7 w-7" />
                            </span>
                        @endif

                        {{-- جزئیات --}}
                        <div class="min-w-0 flex-1">
                            {{-- نام دوره + badge وضعیت --}}
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-base font-extrabold">{{ $order->course->title ?? 'دوره حذف شده' }}</h3>
                                <span class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-xs font-bold {{ $status['class'] }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $status['dot'] }}"></span>
                                    {{ $status['short'] }}
                                </span>
                            </div>

                            {{-- خریدار --}}
                            <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs">
                                <span class="inline-flex items-center gap-1 text-slate-600">
                                    <x-icon name="user" class="h-3.5 w-3.5 text-[#b98a10]" />
                                    <b class="font-bold text-slate-700">{{ $order->buyer_name }}</b>
                                </span>
                                <span class="inline-flex items-center gap-1 text-slate-600" dir="ltr">
                                    <x-icon name="phone" class="h-3.5 w-3.5 text-[#b98a10]" />
                                    {{ $order->buyer_phone }}
                                </span>
                                <span class="inline-flex items-center gap-1 text-slate-500">
                                    <x-icon name="calendar" class="h-3.5 w-3.5 text-slate-400" />
                                    {{ $order->created_at->format('Y/m/d') }}
                                </span>
                            </div>

                            {{-- مبلغ + کد --}}
                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                @if ($order->course && $order->course->price > 0)
                                    <span class="inline-flex items-center gap-1 rounded-md bg-[#faf0d2] px-2 py-0.5 text-xs font-bold text-[#b98a10]">
                                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                                        </svg>
                                        {{ $this->fa(number_format($order->course->price)) }} تومان
                                    </span>
                                @endif
                                <span class="inline-flex items-center gap-1 rounded-md bg-white border border-[#eee4d9] px-2 py-0.5 font-mono text-[10px] font-bold text-slate-600" dir="ltr">
                                    <x-icon name="check-circle" class="h-3 w-3 text-slate-400" />
                                    کد: {{ $order->code }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- دکمه‌های عملیات --}}
                    <div class="flex shrink-0 flex-wrap items-center gap-2 sm:flex-col sm:items-end">
                        @if ($order->status === 'pending')
                            <button
                                wire:click="approve({{ $order->id }})"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-[#0b1826] px-4 py-2 text-sm font-bold text-[#e8c465] transition hover:bg-[#132840]"
                            >
                                <x-icon name="check-circle" class="h-4 w-4" />
                                تأیید سفارش
                            </button>
                            <button
                                wire:click="reject({{ $order->id }})"
                                wire:confirm="مطمئنی می‌خوای این سفارش رو رد کنی؟"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-red-200 bg-[#fdeaea] px-4 py-2 text-sm font-bold text-[#dc2626] transition hover:bg-red-50"
                            >
                                <x-icon name="x-mark" class="h-4 w-4" />
                                رد سفارش
                            </button>
                        @elseif ($order->status === 'approved')
                            <span class="inline-flex items-center gap-1.5 rounded-xl bg-[#e2f6e7] px-4 py-2 text-sm font-bold text-[#16a34a]">
                                <x-icon name="check-circle" class="h-4 w-4" />
                                دسترسی فعال
                            </span>
                        @elseif ($order->status === 'rejected')
                            <span class="inline-flex items-center gap-1.5 rounded-xl bg-[#fdeaea] px-4 py-2 text-sm font-bold text-[#dc2626]">
                                <x-icon name="x-mark" class="h-4 w-4" />
                                سفارش رد شده
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            {{-- حالت خالی --}}
            <div class="flex flex-col items-center gap-4 px-5 py-16 text-center">
                <span class="grid h-16 w-16 place-items-center rounded-2xl bg-[#faf0d2] text-[#b98a10]">
                    <x-icon name="inbox" class="h-8 w-8" />
                </span>
                <div>
                    <p class="font-bold">سفارشی یافت نشد</p>
                    <p class="mt-1 text-sm text-slate-500">
                        @if ($filter === 'pending')
                            هیچ سفارش در انتظار تأییدی وجود ندارد.
                        @elseif ($filter === 'approved')
                            هنوز سفارشی تأیید نکرده‌اید.
                        @elseif ($filter === 'rejected')
                            سفارش رد شده‌ای وجود ندارد.
                        @else
                            هنوز سفارشی در سیستم ثبت نشده است.
                        @endif
                    </p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- ═══════════ Modal پیش‌نمایش رسید ═══════════ --}}
    <div
        x-show="previewOpen"
        x-cloak
        class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 p-4 backdrop-blur-sm"
        @click="closePreview()"
        @keydown.escape.window="closePreview()"
        x-transition.opacity
    >
        <div class="relative max-h-[90vh] max-w-3xl" @click.stop>
            <button
                type="button"
                @click="closePreview()"
                class="absolute -top-12 left-1/2 -translate-x-1/2 rounded-full bg-white/10 p-2 text-white transition hover:bg-white/20"
            >
                <x-icon name="x-mark" class="h-6 w-6" />
            </button>
            <img
                :src="previewSrc"
                alt="رسید پرداخت"
                class="max-h-[85vh] w-auto rounded-2xl shadow-2xl"
            >
        </div>
    </div>

</div>