<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'           => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:20',
            'profile_photo'  => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $updateData = [
            'full_name'      => $request->name,
            'contact_number' => $request->contact_number,
        ];

        if ($request->hasFile('profile_photo')) {
            // Setup Cloudinary
            $cloudinary = new Cloudinary(
                Configuration::instance([
                    'cloud' => [
                        'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                        'api_key'    => env('CLOUDINARY_API_KEY'),
                        'api_secret' => env('CLOUDINARY_API_SECRET'),
                    ],
                ])
            );

            // Delete old photo if exists
            if ($user->profile_photo_public_id) {
                $cloudinary->uploadApi()->destroy($user->profile_photo_public_id);
            }

            // Upload new photo
            $result = $cloudinary->uploadApi()->upload(
                $request->file('profile_photo')->getRealPath(),
                ['folder' => 'profile_photos']
            );

            $updateData['profile_photo']           = $result['secure_url'];
            $updateData['profile_photo_public_id'] = $result['public_id'];
        }

        $user->update($updateData);
        $user->refresh();
        session(['auth.user.name' => $user->name]);

        return redirect()->back()->with('status', 'profile-updated');
    }
}
