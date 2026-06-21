<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Update profile and avatar.
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone_number' => 'nullable|string|max:20',
            'avatar_data' => 'nullable|string', // Base64 image data from cropper
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone_number = $request->phone_number;

        // Process avatar data if provided
        if ($request->filled('avatar_data')) {
            // avatar_data format: data:image/png;base64,iVBORw0KGgo...
            $imgData = $request->avatar_data;
            if (preg_match('/^data:image\/(\w+);base64,/', $imgData, $type)) {
                $imgData = substr($imgData, strpos($imgData, ',') + 1);
                $type = strtolower($type[1]); // jpg, png, gif

                if (in_array($type, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $imgData = base64_decode($imgData);
                    
                    if ($imgData !== false) {
                        $fileName = 'avatars/' . $user->id . '_' . time() . '.' . $type;
                        
                        // Delete old avatar if exists
                        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                            Storage::disk('public')->delete($user->avatar);
                        }

                        // Save new avatar
                        Storage::disk('public')->put($fileName, $imgData);
                        $user->avatar = $fileName;
                    }
                }
            }
        }

        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui!');
    }
}
