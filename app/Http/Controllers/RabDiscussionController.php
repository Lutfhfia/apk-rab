<?php

namespace App\Http\Controllers;

use App\Models\Rab;
use Illuminate\Http\Request;

class RabDiscussionController extends Controller
{
    public function store(Request $request, Rab $rab)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ], [
            'message.required' => 'Catatan diskusi wajib diisi.',
        ]);

        $rab->addDiscussionNote(auth()->id(), $request->message);

        return back()->with('success', 'Catatan diskusi berhasil ditambahkan.');
    }
}
