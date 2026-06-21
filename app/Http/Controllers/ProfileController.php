<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Memperbarui profil pengguna beserta foto profil (avatar).
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone_number' => 'nullable|string|max:20',
            'avatar_data' => 'nullable|string', // Data gambar Base64 dari hasil cropping/pemotongan gambar
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone_number = $request->phone_number;

        // Proses data foto profil jika disediakan
        if ($request->filled('avatar_data')) {
            // format avatar_data: data:image/png;base64,iVBORw0KGgo...
            $imgData = $request->avatar_data;
            if (preg_match('/^data:image\/(\w+);base64,/', $imgData, $type)) {
                $imgData = substr($imgData, strpos($imgData, ',') + 1);
                $type = strtolower($type[1]); // jpg, png, gif

                if (in_array($type, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $imgData = base64_decode($imgData);
                    
                    if ($imgData !== false) {
                        $fileName = 'avatars/' . $user->id . '_' . time() . '.' . $type;
                        
                        // Hapus avatar lama jika ada
                        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                            Storage::disk('public')->delete($user->avatar);
                        }

                        // Simpan avatar baru
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
