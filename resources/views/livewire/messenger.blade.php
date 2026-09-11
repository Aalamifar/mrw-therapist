<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use Livewire\WithFileUploads;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

new class extends Component
{
    use WithFileUploads;

    public ?int $selectedConversationId = null;
    public string $messageBody = '';
    public string $search = '';
    public bool $showChatOnMobile = false;

    public $attachment = null;

    public ?int $editingMessageId = null;
    public string $editingBody = '';

    public function mount()
    {
        $otherUserId = request()->query('user_id');

        if ($otherUserId) {
            $otherUser = User::find($otherUserId);

            if ($otherUser && $this->canStartConversationWith($otherUser)) {
                $conversation = auth()->user()->getPrivateConversationWith($otherUser);

                if ($conversation) {
                    $this->selectedConversationId = $conversation->id;
                    $this->showChatOnMobile = true;

                    Message::where('conversation_id', $conversation->id)
                        ->where('sender_id', '!=', auth()->id())
                        ->whereNull('read_at')
                        ->update([
                            'read_at' => now(),
                        ]);
                }
            }
        }
    }

    /** Only classmates and their assigned therapist may start a direct chat. */
    protected function canStartConversationWith(User $otherUser): bool
    {
        $user = auth()->user();

        if ($user->role === 'therapist' && $otherUser->role === 'student') {
            return $user->classesAsTherapist()
                ->whereHas('students', fn ($query) => $query->whereKey($otherUser->id))
                ->exists();
        }

        if ($user->role === 'student' && $otherUser->role === 'therapist') {
            return $user->classesAsStudent()
                ->where('therapist_id', $otherUser->id)
                ->exists();
        }

        return false;
    }

    #[Computed]
    public function conversations()
    {
        $userId = auth()->id();

        return Conversation::whereHas('users', function ($query) use ($userId) {
            $query->where('users.id', $userId);
        })
            ->with([
                'users',
                'lastMessage',
            ])
            ->when($this->search, function ($query) {
                $search = '%' . $this->search . '%';

                $query->whereHas('users', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', $search);
                });
            })
            ->latest()
            ->get();
    }

    public function selectConversation($conversationId)
    {
        $conversation = Conversation::whereHas('users', function ($query) {
            $query->where('users.id', auth()->id());
        })->find($conversationId);

        if (! $conversation) {
            abort(403);
        }

        $this->selectedConversationId = (int) $conversation->id;
        $this->showChatOnMobile = true;

        Message::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', auth()->id())
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
            ]);

        unset($this->messages);
        unset($this->conversations);
        unset($this->selectedConversation);
        unset($this->otherUser);
    }

    public function backToList()
    {
        $this->showChatOnMobile = false;
        $this->selectedConversationId = null;

        $this->cancelEdit();
        $this->removeAttachment();

        unset($this->messages);
        unset($this->selectedConversation);
        unset($this->otherUser);
    }

    #[Computed]
    public function selectedConversation()
    {
        if (! $this->selectedConversationId) {
            return null;
        }

        return Conversation::whereHas('users', function ($query) {
            $query->where('users.id', auth()->id());
        })
            ->with('users')
            ->find($this->selectedConversationId);
    }

    #[Computed]
    public function messages()
    {
        if (! $this->selectedConversationId) {
            return collect();
        }

        return Message::where('conversation_id', $this->selectedConversationId)
            ->with('sender')
            ->oldest()
            ->get();
    }

    public function sendMessage()
    {
        $this->validate([
            'messageBody' => [
                'nullable',
                'string',
                'max:2000',
                'required_without:attachment',
            ],
            'attachment' => [
                'nullable',
                'file',
                'max:10240',
                'required_without:messageBody',
            ],
        ], [
            'messageBody.required_without' => 'لطفاً پیام یا فایل انتخاب کنید.',
            'attachment.required_without' => 'لطفاً پیام یا فایل انتخاب کنید.',
            'attachment.max' => 'حجم فایل نباید بیشتر از ۱۰ مگابایت باشد.',
        ]);

        $conversation = $this->selectedConversation;

        if (! $conversation) {
            return;
        }

        $attachmentPath = null;
        $attachmentName = null;
        $attachmentType = null;
        $attachmentSize = null;

        if ($this->attachment) {
            $attachmentName = $this->attachment->getClientOriginalName();
            $attachmentType = $this->attachment->getMimeType();
            $attachmentSize = $this->attachment->getSize();

            $attachmentPath = $this->attachment->store(
                'messages',
                'local'
            );
        }

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => auth()->id(),
            'body' => trim($this->messageBody),
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'attachment_type' => $attachmentType,
            'attachment_size' => $attachmentSize,
        ]);

        $this->messageBody = '';
        $this->attachment = null;

        unset($this->messages);
        unset($this->conversations);
    }

    public function startEditing(int $messageId)
    {
        $message = $this->getOwnMessage($messageId);

        if (! $message) {
            return;
        }

        if (! $message->body) {
            return;
        }

        $this->editingMessageId = $message->id;
        $this->editingBody = $message->body;
    }

    public function cancelEdit()
    {
        $this->editingMessageId = null;
        $this->editingBody = '';
    }

    public function updateMessage()
    {
        if (! $this->editingMessageId) {
            return;
        }

        $this->validate([
            'editingBody' => 'required|string|max:2000',
        ], [
            'editingBody.required' => 'متن پیام نمی‌تواند خالی باشد.',
            'editingBody.max' => 'متن پیام نمی‌تواند بیشتر از ۲۰۰۰ کاراکتر باشد.',
        ]);

        $message = $this->getOwnMessage($this->editingMessageId);

        if (! $message) {
            $this->cancelEdit();
            return;
        }

        $message->update([
            'body' => trim($this->editingBody),
            'edited_at' => now(),
        ]);

        $this->cancelEdit();

        unset($this->messages);
        unset($this->conversations);
    }

    public function deleteMessage(int $messageId)
    {
        $message = $this->getOwnMessage($messageId);

        if (! $message) {
            return;
        }

        if ($message->attachment_path) {
            Storage::disk('local')->delete($message->attachment_path);
        }

        $message->delete();

        if ($this->editingMessageId === $messageId) {
            $this->cancelEdit();
        }

        unset($this->messages);
        unset($this->conversations);
    }

    protected function getOwnMessage(int $messageId): ?Message
    {
        if (! $this->selectedConversationId) {
            return null;
        }

        return Message::where('id', $messageId)
            ->where('conversation_id', $this->selectedConversationId)
            ->where('sender_id', auth()->id())
            ->first();
    }

    public function removeAttachment()
    {
        $this->attachment = null;
    }

    public function formatFileSize(?int $bytes): string
    {
        if (! $bytes) {
            return '';
        }

        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        if ($bytes < 1024 * 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }

        return round($bytes / (1024 * 1024), 1) . ' MB';
    }

    public function isImage(?string $mimeType): bool
    {
        return $mimeType && str_starts_with($mimeType, 'image/');
    }

    #[Computed]
    public function otherUser()
    {
        $conversation = $this->selectedConversation;

        if (! $conversation) {
            return null;
        }

        return $conversation->otherUser(auth()->user());
    }

    /** رنگ آواتار هر کاربر — ثابت و هماهنگ با پالت طرح */
    public function avatarTone($id): string
    {
        $tones = [
            'bg-[#f3e8fa] text-[#9333ea]',
            'bg-[#dff4f0] text-[#0d9488]',
            'bg-[#fdeaea] text-[#dc2626]',
            'bg-[#e2f6e7] text-[#16a34a]',
            'bg-[#e8eef7] text-[#33507a]',
            'bg-[#fae4fb] text-[#c026d3]',
        ];

        return $tones[abs(crc32((string) $id)) % count($tones)];
    }

    /** تبدیل اعداد به ارقام فارسی */
    public function fa(int $number): string
    {
        return str_replace(range('0', '9'), ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'], (string) $number);
    }
}; ?>

<div class="h-[calc(100vh-9rem)] overflow-hidden rounded-2xl border border-[#eee4d9] bg-[#fdfbf8] shadow-sm flex">

    {{-- ═══════════ SIDEBAR ═══════════ --}}
    <aside
        class="flex w-full shrink-0 flex-col border-l border-[#eee4d9] bg-[#faf5ec]/40 sm:w-[340px] {{ $showChatOnMobile ? 'hidden sm:flex' : 'flex' }}"
    >
        {{-- Header --}}
        <div class="border-b border-[#eee4d9] bg-[#fdfbf8] p-4">
            <div class="mb-3 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-[#f3e8fa] text-[#9333ea]">
                        <x-icon name="chat" class="h-5 w-5" />
                    </span>
                    <div>
                        <h2 class="text-base font-extrabold">پیام‌ها</h2>
                        <p class="text-[10px] text-slate-500">
                            {{ $this->fa($this->conversations->count()) }} گفتگو
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    class="rounded-xl p-2 text-slate-500 transition hover:bg-[#f0e7dc] hover:text-[#b98a10]"
                    title="گفتگوی جدید"
                >
                    <x-icon name="pencil" class="h-4 w-4" />
                </button>
            </div>

            <div class="relative">
                <x-icon name="search" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#b98a10]" />
                <input
                    type="text"
                    wire:model.live.debounce.400ms="search"
                    placeholder="جستجوی نام کاربر..."
                    class="w-full rounded-xl border border-[#eee4d9] bg-white py-2.5 pl-3 pr-10 text-sm shadow-sm outline-none transition placeholder:text-slate-400 focus:border-[#d7a92b]/50 focus:ring-2 focus:ring-[#d7a92b]/20"
                >
            </div>
        </div>

        {{-- List --}}
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

                <button
                    type="button"
                    wire:click="selectConversation({{ $conversation->id }})"
                    class="flex w-full items-center gap-3 px-4 py-3 text-right transition border-b border-[#f0e7dc] last:border-b-0
                        {{ $isActive
                            ? 'bg-gradient-to-l from-[#faf0d2]/60 to-transparent border-r-2 border-r-[#d7a92b]'
                            : 'hover:bg-[#faf5ec]' }}"
                >
                    <div class="relative shrink-0">
                        <div class="grid h-11 w-11 place-items-center rounded-full text-sm font-extrabold {{ $this->avatarTone($other?->id ?? 0) }}">
                            {{ mb_substr($other?->name ?? '?', 0, 1) }}
                        </div>
                        @if ($unreadCount > 0)
                            <span class="absolute -top-0.5 -right-0.5 h-3 w-3 rounded-full bg-[#d7a92b] ring-2 ring-[#fdfbf8]"></span>
                        @endif
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-2">
                            <span class="truncate text-sm font-bold {{ $isActive ? 'text-[#b98a10]' : '' }}">
                                {{ $other?->name ?? 'کاربر' }}
                            </span>

                            @if ($lastMsg)
                                <span class="shrink-0 text-[10px] text-slate-400">
                                    {{ $lastMsg->created_at->diffForHumans() }}
                                </span>
                            @endif
                        </div>

                        <div class="mt-0.5 flex items-center justify-between gap-2">
                            <span class="truncate text-xs text-slate-500">
                                @if ($lastMsg)
                                    @if ($lastMsg->sender_id === $currentUserId)
                                        <span class="text-slate-400">شما: </span>
                                    @endif

                                    @if ($lastMsg->body)
                                        {{ Str::limit($lastMsg->body, 35) }}
                                    @elseif ($lastMsg->attachment_path)
                                        <span class="inline-flex items-center gap-1 text-slate-400">
                                            <x-icon name="paper-clip" class="h-3 w-3" />
                                            فایل پیوست
                                        </span>
                                    @endif
                                @else
                                    <span class="italic text-slate-400">شروع گفتگو</span>
                                @endif
                            </span>

                            @if ($unreadCount > 0)
                                <span class="flex h-5 min-w-5 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-[#f0d060] to-[#d7a92b] px-1.5 text-[10px] font-extrabold text-[#0b1826]">
                                    {{ $unreadCount > 9 ? '+۹' : $this->fa($unreadCount) }}
                                </span>
                            @endif
                        </div>
                    </div>
                </button>
            @empty
                <div class="flex flex-col items-center gap-3 px-5 py-14 text-center">
                    <span class="grid h-14 w-14 place-items-center rounded-2xl bg-[#f3e8fa] text-[#9333ea]">
                        <x-icon name="chat" class="h-7 w-7" />
                    </span>
                    <div>
                        <p class="text-sm font-bold">هنوز گفتگویی ندارید</p>
                        <p class="mt-1 text-xs text-slate-500">
                            @if ($search)
                                کاربری با این نام یافت نشد.
                            @else
                                از لیست دانش‌آموزان، گفتگو را شروع کنید.
                            @endif
                        </p>
                    </div>
                </div>
            @endforelse
        </div>
    </aside>

    {{-- ═══════════ MAIN ═══════════ --}}
    <main class="flex min-w-0 flex-1 flex-col {{ $showChatOnMobile ? 'flex' : 'hidden sm:flex' }}">
        @if ($this->selectedConversationId && $this->selectedConversation)

            {{-- Header --}}
            <div class="flex items-center gap-3 border-b border-[#eee4d9] bg-[#fdfbf8] px-4 py-3 shadow-sm">
                <button
                    type="button"
                    wire:click="backToList"
                    class="rounded-xl p-2 text-slate-500 transition hover:bg-[#f0e7dc] hover:text-slate-700 sm:hidden"
                >
                    <x-icon name="arrow-right" class="h-5 w-5" />
                </button>

                <div class="grid h-11 w-11 shrink-0 place-items-center rounded-full text-sm font-extrabold {{ $this->avatarTone($this->otherUser?->id ?? 0) }}">
                    {{ mb_substr($this->otherUser?->name ?? '?', 0, 1) }}
                </div>

                <div class="min-w-0 flex-1">
                    <div class="truncate font-extrabold">{{ $this->otherUser?->name ?? 'کاربر' }}</div>
                    <div class="flex items-center gap-1.5 text-xs text-[#16a34a]">
                        <span class="h-2 w-2 rounded-full bg-[#16a34a]"></span>
                        آنلاین
                    </div>
                </div>
            </div>

            {{-- Messages --}}
            <div
                id="messages-container"
                class="flex-1 space-y-3 overflow-y-auto px-4 py-6 bg-[#f4eee7]/40"
                x-data="{
                    scrollToBottom() {
                        this.$nextTick(() => {
                            const el = document.getElementById('messages-container');
                            if (el) el.scrollTop = el.scrollHeight;
                        });
                    }
                }"
                x-init="scrollToBottom()"
                x-on:livewire:navigated.window="scrollToBottom()"
            >
                @forelse ($this->messages as $message)
                    @php
                        $isMine = $message->sender_id === auth()->id();
                        $isEditing = $editingMessageId === $message->id;
                        $isImage = $message->hasAttachment() && $this->isImage($message->attachment_type);
                    @endphp

                    <div wire:key="message-{{ $message->id }}" class="flex {{ $isMine ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[85%] sm:max-w-md">

                            {{-- حباب پیام --}}
                            <div
                                class="rounded-2xl px-4 py-2.5 text-sm shadow-sm
                                {{ $isMine
                                    ? 'bg-[#0b1826] text-[#f8ecc2] rounded-bl-sm'
                                    : 'border border-[#eee4d9] bg-[#fdfbf8] text-slate-700 rounded-br-sm' }}"
                            >
                                @if ($message->body)
                                    @if ($isEditing)
                                        <textarea
                                            wire:model="editingBody"
                                            rows="3"
                                            class="w-full resize-none rounded-xl border border-[#eee4d9] bg-white px-3 py-2 text-sm text-slate-800 outline-none focus:border-[#d7a92b]/50 focus:ring-2 focus:ring-[#d7a92b]/20"
                                        ></textarea>

                                        <div class="mt-2 flex items-center gap-2">
                                            <button
                                                type="button"
                                                wire:click="updateMessage"
                                                class="rounded-lg bg-gradient-to-br from-[#f0d060] to-[#d7a92b] px-3 py-1.5 text-xs font-extrabold text-[#0b1826]"
                                            >
                                                ذخیره
                                            </button>
                                            <button
                                                type="button"
                                                wire:click="cancelEdit"
                                                class="rounded-lg bg-white border border-[#eee4d9] px-3 py-1.5 text-xs font-bold text-slate-600 hover:bg-[#f0e7dc]"
                                            >
                                                لغو
                                            </button>
                                        </div>
                                    @else
                                        <div class="whitespace-pre-wrap break-words">
                                            {{ $message->body }}
                                        </div>
                                    @endif
                                @endif

                                {{-- Attachment --}}
                                @if ($message->hasAttachment())
                                    <div class="{{ $message->body ? 'mt-3 pt-3 border-t border-white/10' : '' }}">

                                        @if ($isImage)
                                            <a
                                                href="{{ route('message.attachment', $message) }}"
                                                target="_blank"
                                                class="block overflow-hidden rounded-xl ring-1 {{ $isMine ? 'ring-white/10' : 'ring-[#eee4d9]' }}"
                                            >
                                                <img
                                                    src="{{ route('message.attachment', $message) }}"
                                                    alt="{{ $message->attachment_name }}"
                                                    class="max-h-72 w-auto max-w-full object-cover"
                                                >
                                            </a>
                                        @else
                                            <a
                                                href="{{ route('message.attachment', $message) }}"
                                                class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition
                                                    {{ $isMine
                                                        ? 'bg-white/5 hover:bg-white/10 border border-white/10'
                                                        : 'bg-[#faf5ec] hover:bg-[#f0e7dc] border border-[#eee4d9]' }}"
                                            >
                                                <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl {{ $isMine ? 'bg-[#faf0d2] text-[#b98a10]' : 'bg-[#f3e8fa] text-[#9333ea]' }}">
                                                    <x-icon name="paper-clip" class="h-5 w-5" />
                                                </div>

                                                <div class="min-w-0 flex-1">
                                                    <div class="truncate text-xs font-bold">{{ $message->attachment_name }}</div>
                                                    <div class="mt-0.5 text-[10px] opacity-70">
                                                        {{ $this->formatFileSize($message->attachment_size) }}
                                                    </div>
                                                </div>

                                                <x-icon name="arrow-down-tray" class="h-4 w-4 shrink-0" />
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            {{-- Footer --}}
                            <div
                                class="mt-1 flex items-center gap-2 px-1 text-[10px] text-slate-400
                                {{ $isMine ? 'justify-end' : 'justify-start' }}"
                            >
                                <span>{{ $message->created_at->format('H:i') }}</span>

                                @if ($message->isEdited())
                                    <span class="italic">(ویرایش شد)</span>
                                @endif

                                @if ($isMine && !$isEditing)
                                    <div class="flex items-center gap-0.5">
                                        @if ($message->body)
                                            <button
                                                type="button"
                                                wire:click="startEditing({{ $message->id }})"
                                                class="rounded p-1 transition hover:text-[#b98a10] hover:bg-[#faf0d2]"
                                                title="ویرایش"
                                            >
                                                <x-icon name="pencil" class="h-3 w-3" />
                                            </button>
                                        @endif
                                        <button
                                            type="button"
                                            wire:click="deleteMessage({{ $message->id }})"
                                            wire:confirm="آیا از حذف این پیام مطمئن هستید؟"
                                            class="rounded p-1 transition hover:text-red-600 hover:bg-red-50"
                                            title="حذف"
                                        >
                                            <x-icon name="trash" class="h-3 w-3" />
                                        </button>
                                    </div>
                                @endif

                                @if ($isMine)
                                    <x-icon
                                        name="check-circle"
                                        class="h-3 w-3 {{ $message->read_at ? 'text-[#d7a92b]' : 'text-slate-300' }}"
                                    />
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="flex h-full flex-col items-center justify-center py-16 text-center">
                        <span class="mb-3 grid h-16 w-16 place-items-center rounded-2xl bg-[#faf0d2] text-[#b98a10]">
                            <x-icon name="chat" class="h-8 w-8" />
                        </span>
                        <p class="text-sm font-bold">شروع گفتگو با {{ $this->otherUser?->name }}</p>
                        <p class="mt-1 text-xs text-slate-500">اولین پیام خود را ارسال کنید</p>
                    </div>
                @endforelse
            </div>

            {{-- Compose --}}
            <form wire:submit="sendMessage" class="border-t border-[#eee4d9] bg-[#fdfbf8] p-3 sm:p-4">

                {{-- Selected file --}}
                @if ($attachment)
                    <div class="mb-3 flex items-center gap-3 rounded-xl border border-[#eee4d9] bg-[#faf5ec] px-3 py-2.5">
                        <div class="grid h-10 w-10 place-items-center rounded-xl bg-[#e2f6e7] text-[#16a34a]">
                            <x-icon name="check-circle" class="h-5 w-5" />
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="truncate text-xs font-bold">{{ $attachment->getClientOriginalName() }}</div>
                            <div class="text-[10px] text-slate-500">
                                آماده ارسال · {{ number_format($attachment->getSize() / 1024 / 1024, 2) }} MB
                            </div>
                        </div>

                        <button
                            type="button"
                            wire:click="removeAttachment"
                            class="rounded-lg p-2 text-slate-400 transition hover:bg-red-50 hover:text-red-600"
                            title="حذف فایل"
                        >
                            <x-icon name="x-mark" class="h-4 w-4" />
                        </button>
                    </div>
                @endif

                {{-- Upload progress --}}
                <div wire:loading wire:target="attachment" class="mb-2 flex items-center gap-1.5 text-xs text-slate-500">
                    <svg class="h-3.5 w-3.5 animate-spin text-[#b98a10]" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="3"/>
                        <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                    </svg>
                    در حال آپلود فایل...
                </div>

                <div class="flex items-end gap-2">

                    {{-- File picker --}}
                    <label
                        class="grid h-11 w-11 shrink-0 cursor-pointer place-items-center rounded-xl border border-[#eee4d9] bg-white text-[#b98a10] transition hover:border-[#d7a92b]/50 hover:bg-[#faf0d2]"
                        title="ارسال فایل"
                    >
                        <x-icon name="paper-clip" class="h-5 w-5" />
                        <input
                            type="file"
                            wire:model="attachment"
                            class="hidden"
                            accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip,.rar"
                        >
                    </label>

                    {{-- Textarea --}}
                    <div class="relative flex-1">
                        <textarea
                            wire:model="messageBody"
                            rows="1"
                            placeholder="پیام خود را بنویسید..."
                            class="w-full resize-none rounded-xl border border-[#eee4d9] bg-white px-4 py-3 text-sm outline-none transition focus:border-[#d7a92b]/50 focus:ring-2 focus:ring-[#d7a92b]/20"
                            onkeydown="if(event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); this.closest('form').requestSubmit(); }"
                        ></textarea>
                    </div>

                    {{-- Send button --}}
                    <button
                        type="submit"
                        class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-[#0b1826] text-[#e8c465] transition hover:bg-[#132840] disabled:cursor-not-allowed disabled:opacity-50"
                        x-bind:disabled="!$wire.messageBody.trim() && !$wire.attachment"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                            <path d="m22 2-7 20-4-9-9-4 20-7Z"/>
                            <path d="M22 2 11 13"/>
                        </svg>
                    </button>
                </div>
            </form>

        @else

            {{-- Welcome --}}
            <div class="flex flex-1 flex-col items-center justify-center p-8 text-center">
                <span class="mb-4 grid h-20 w-20 place-items-center rounded-2xl bg-gradient-to-br from-[#f0d060] to-[#d7a92b] text-[#0b1826] shadow-md">
                    <x-icon name="chat" class="h-10 w-10" />
                </span>
                <h3 class="text-lg font-extrabold">به پیام‌رسان خوش آمدید</h3>
                <p class="mt-1 max-w-xs text-sm text-slate-500">
                    یک گفتگو را از لیست سمت راست انتخاب کنید تا شروع به چت کنید.
                </p>
            </div>

        @endif
    </main>
</div>
