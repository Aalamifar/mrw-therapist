<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;

new class extends Component
{
    public ?int $selectedConversationId = null;
    public string $messageBody = '';
    public string $search = '';
    public bool $showChatOnMobile = false;

    public function mount()
    {
        $studentId = request('student_id');

        if ($studentId) {
            $student = User::find($studentId);
            if ($student) {
                $conversation = auth()->user()->getPrivateConversationWith($student);
                $this->selectedConversationId = $conversation->id;
                $this->showChatOnMobile = true;

                Message::where('conversation_id', $conversation->id)
                    ->where('sender_id', '!=', auth()->id())
                    ->whereNull('read_at')
                    ->update(['read_at' => now()]);
            }
        }
    }

    #[Computed]
    public function conversations()
    {
        $userId = auth()->id();
        return Conversation::whereHas('users', fn($q) => $q->where('users.id', $userId))
            ->with(['users', 'lastMessage'])
            ->when($this->search, fn($q) => $q->whereHas('users', fn($q2) => $q2->where('name', 'like', '%' . $this->search . '%')))
            ->latest()
            ->get();
    }

    // حذف type hinting برای جلوگیری از تداخل پارسر Livewire
    public function selectConversation($conversationId)
    {
        $this->selectedConversationId = (int) $conversationId;
        $this->showChatOnMobile = true;

        Message::where('conversation_id', $this->selectedConversationId)
            ->where('sender_id', '!=', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function backToList()
    {
        $this->showChatOnMobile = false;
        $this->selectedConversationId = null;
    }

    #[Computed]
    public function selectedConversation()
    {
        if (! $this->selectedConversationId) return null;
        return Conversation::with('users')->find($this->selectedConversationId);
    }

    #[Computed]
    public function messages()
    {
        if (! $this->selectedConversationId) return collect();
        return Message::where('conversation_id', $this->selectedConversationId)
            ->with('sender')
            ->oldest()
            ->get();
    }

    public function sendMessage()
    {
        $this->validate(['messageBody' => 'required|string|max:2000']);

        $conversation = $this->selectedConversation;
        if (! $conversation) return;

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => auth()->id(),
            'body' => $this->messageBody,
        ]);

        $this->messageBody = '';
    }

    #[Computed]
    public function otherUser()
    {
        $conversation = $this->selectedConversation;
        if (! $conversation) return null;
        return $conversation->otherUser(auth()->user());
    }
}; ?>

<div class="h-[calc(100vh-9rem)] rounded-2xl border border-slate-200 bg-white overflow-hidden flex">

    {{-- SIDEBAR: لیست گفتگوها --}}
    <aside class="flex flex-col border-l border-slate-200 bg-[#F7F6F3]/50 w-full sm:w-[320px] shrink-0 {{ $showChatOnMobile ? 'hidden sm:flex' : 'flex' }}">
        <div class="p-4 border-b border-slate-200 bg-white">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-bold text-[#061C32]">پیام‌ها</h2>
                <button class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 transition">
                    <x-icon name="pencil" class="h-4 w-4" />
                </button>
            </div>
            
            <div class="relative">
                <x-icon name="search" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input 
                    type="text" 
                    wire:model.live.debounce.400ms="search" 
                    placeholder="جستجو..."
                    class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-3 pr-9 text-sm outline-none focus:border-[#061C32]/40 focus:ring-2 focus:ring-[#061C32]/10"
                >
            </div>
        </div>

        <div class="flex-1 overflow-y-auto">
            @forelse ($this->conversations as $conversation)
                @php
                    $currentUserId = auth()->id();
                    $currentUser = auth()->user();
                    $other = $conversation->otherUser($currentUser);
                    $lastMsg = $conversation->lastMessage;
                    $isActive = $conversation->id === $this->selectedConversationId;
                    $unreadCount = $lastMsg 
                        ? Message::where('conversation_id', $conversation->id)
                            ->where('sender_id', '!=', $currentUserId)
                            ->whereNull('read_at')
                            ->count()
                        : 0;
                @endphp

                {{-- ارسال به صورت رشته برای پایداری کامل --}}
                <button 
                    wire:click="selectConversation('{{ $conversation->id }}')"
                    class="w-full flex items-center gap-3 px-4 py-3 text-right transition border-b border-slate-100
                           {{ $isActive ? 'bg-[#061C32]/5 border-r-2 border-r-[#E9C72A]' : 'hover:bg-slate-100/70' }}"
                >
                    <div class="relative shrink-0">
                        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-[#E9C72A]/20 font-bold text-[#061C32]">
                            {{ mb_substr($other?->name ?? '?', 0, 1) }}
                        </div>
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-bold text-sm text-[#061C32] truncate">
                                {{ $other?->name ?? 'کاربر' }}
                            </span>
                            @if ($lastMsg)
                                <span class="text-[10px] text-slate-400 shrink-0">
                                    {{ $lastMsg->created_at->diffForHumans(short: true) }}
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center justify-between gap-2 mt-0.5">
                            <span class="text-xs text-slate-500 truncate">
                                @if($lastMsg)
                                    @if($lastMsg->sender_id === $currentUserId)
                                        <span class="text-slate-400">شما:</span>
                                    @endif
                                    {{ \Illuminate\Support\Str::limit($lastMsg->body, 30) }}
                                @else
                                    <span class="italic text-slate-400">شروع گفتگو</span>
                                @endif
                            </span>
                            @if($unreadCount > 0)
                                <span class="flex h-5 min-w-5 items-center justify-center rounded-full bg-[#E9C72A] px-1.5 text-[10px] font-bold text-[#061C32]">
                                    {{ $unreadCount > 9 ? '+9' : $unreadCount }}
                                </span>
                            @endif
                        </div>
                    </div>
                </button>
            @empty
                <div class="flex flex-col items-center gap-2 px-4 py-12 text-center">
                    <x-icon name="chat" class="h-8 w-8 text-slate-300" />
                    <p class="text-sm text-slate-500">هنوز گفتگویی ندارید</p>
                </div>
            @endforelse
        </div>
    </aside>

    {{-- MAIN: ناحیه چت --}}
    <main class="flex flex-1 flex-col min-w-0 {{ $showChatOnMobile ? 'flex' : 'hidden sm:flex' }}">
        
        {{-- فراخوانی بدون پرانتز به دلیل استفاده از #[Computed] --}}
        @if ($this->selectedConversationId && $this->selectedConversation)
            @php
                $otherUser = $this->otherUser;
                $messages = $this->messages;
            @endphp

            <div class="flex items-center gap-3 border-b border-slate-200 bg-white px-4 py-3">
                <button wire:click="backToList" class="sm:hidden rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 transition">
                    <x-icon name="arrow-right" class="h-5 w-5" />
                </button>

                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#E9C72A]/20 font-bold text-[#061C32]">
                    {{ mb_substr($otherUser?->name ?? '?', 0, 1) }}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="font-bold text-[#061C32] truncate">{{ $otherUser?->name ?? 'کاربر' }}</div>
                    <div class="text-xs text-emerald-600">آنلاین</div>
                </div>
            </div>

            <div id="messages-container" class="flex-1 overflow-y-auto px-4 py-6 space-y-3 bg-[#F7F6F3]/30"
                x-data="{ scrollToBottom() { this.$nextTick(() => { const el = document.getElementById('messages-container'); if (el) el.scrollTop = el.scrollHeight; }); } }"
                x-init="scrollToBottom()"
                @livewire:navigated.window="scrollToBottom()"
            >
                @foreach ($messages as $message)
                    @php $isMine = $message->sender_id === auth()->id(); @endphp
                    <div class="flex {{ $isMine ? 'justify-start' : 'justify-end' }}">
                        <div class="max-w-[75%] sm:max-w-md">
                            <div class="rounded-2xl px-4 py-2.5 text-sm shadow-sm {{ $isMine ? 'bg-[#061C32] text-white rounded-bl-sm' : 'bg-white text-[#061C32] border border-slate-100 rounded-br-sm' }}">
                                {{ $message->body }}
                            </div>
                            <div class="mt-1 flex items-center gap-1 px-1 text-[10px] text-slate-400 {{ $isMine ? 'justify-start' : 'justify-end' }}">
                                <span>{{ $message->created_at->format('H:i') }}</span>
                                @if($isMine)
                                    <x-icon name="check-circle" class="h-3 w-3 {{ $message->read_at ? 'text-[#E9C72A]' : 'text-slate-300' }}" />
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach

                @if($messages->isEmpty())
                    <div class="flex flex-col items-center justify-center h-full text-center py-12">
                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-[#E9C72A]/20 mb-3">
                            <x-icon name="chat" class="h-8 w-8 text-[#061C32]" />
                        </div>
                        <p class="text-sm font-medium text-[#061C32]">شروع گفتگو با {{ $otherUser?->name }}</p>
                        <p class="text-xs text-slate-500 mt-1">اولین پیام خود را ارسال کنید</p>
                    </div>
                @endif
            </div>

            <form wire:submit="sendMessage" class="border-t border-slate-200 bg-white p-3 sm:p-4">
                <div class="flex items-end gap-2">
                    <div class="flex-1 relative">
                        <textarea 
                            wire:model="messageBody"
                            rows="1"
                            placeholder="پیام خود را بنویسید..."
                            class="w-full resize-none rounded-xl border border-slate-200 bg-[#F7F6F3]/50 px-4 py-3 text-sm outline-none transition focus:border-[#061C32]/40 focus:ring-2 focus:ring-[#061C32]/10 focus:bg-white"
                            onkeydown="if(event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); this.closest('form').requestSubmit(); }"
                        ></textarea>
                    </div>
                    <button type="submit" class="shrink-0 flex h-11 w-11 items-center justify-center rounded-xl bg-[#061C32] text-white transition hover:bg-[#061C32]/90 disabled:opacity-50" @disabled(!trim($messageBody))>
                        <x-icon name="arrow-right" class="h-5 w-5 rotate-180" />
                    </button>
                </div>
            </form>

        @else
            <div class="flex flex-1 flex-col items-center justify-center text-center p-8">
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-[#E9C72A]/20 mb-4">
                    <x-icon name="chat" class="h-10 w-10 text-[#061C32]" />
                </div>
                <h3 class="text-lg font-bold text-[#061C32]">به پیام‌رسان خوش آمدید</h3>
                <p class="mt-1 text-sm text-slate-500 max-w-xs">یک گفتگو را از لیست سمت راست انتخاب کنید.</p>
            </div>
        @endif
    </main>
</div>