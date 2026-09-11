<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MessageAttachmentController extends Controller
{
    public function download(Message $message): StreamedResponse
    {
        $isMember = $message->conversation->users()
            ->whereKey(auth()->id())
            ->exists();

        abort_unless($isMember && $message->attachment_path, 403, 'دسترسی غیرمجاز');
        abort_unless(Storage::disk('local')->exists($message->attachment_path), 404);

        return Storage::disk('local')->response($message->attachment_path, $message->attachment_name, [
            'Content-Disposition' => 'inline; filename="' . addcslashes($message->attachment_name ?? basename($message->attachment_path), '"') . '"',
        ]);
    }
}
