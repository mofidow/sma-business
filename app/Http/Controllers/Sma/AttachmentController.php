<?php

namespace App\Http\Controllers\Sma;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Tecdiary\Laravel\Attachments\Attachment;

class AttachmentController extends Controller
{
    public function destroy(Attachment $attachment)
    {
        if (auth()->user()?->can('delete-attachments')) {
            $attachment->delete();

            return back()->with(['message' => __('Attachment deleted!')]);
        }

        return back()->with(['error' => __('Unable to delete attachment!')]);
    }

    public function download(Request $request, Attachment $attachment)
    {
        if ($request->view == 'yes') {
            return Storage::disk(env('ATTACHMENT_DISK', 'local'))->response($attachment->filepath);
        }

        return Storage::disk(env('ATTACHMENT_DISK', 'local'))->download($attachment->filepath);
    }
}
