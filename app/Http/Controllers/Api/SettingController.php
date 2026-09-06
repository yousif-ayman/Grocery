<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SettingRequest;
use App\Http\Resources\SettingResource;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => new SettingResource(Setting::getSettings())]);
    }

    public function update(SettingRequest $request): JsonResponse
    {
        $settings = Setting::getSettings();
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            if ($settings->logo && Storage::disk('public')->exists($settings->logo)) {
                Storage::disk('public')->delete($settings->logo);
            }
            $data['logo'] = $request->file('logo')->store('settings', 'public');
        }

        if ($request->hasFile('favicon')) {
            if ($settings->favicon && Storage::disk('public')->exists($settings->favicon)) {
                Storage::disk('public')->delete($settings->favicon);
            }
            $data['favicon'] = $request->file('favicon')->store('settings', 'public');
        }

        $settings->update($data);

        return response()->json([
            'success' => true, 'message' => 'Settings updated successfully',
            'data' => new SettingResource($settings)
        ]);
    }

    public function publicSettings(): JsonResponse
    {
        $s = Setting::getSettings();
        return response()->json([
            'success' => true,
            'data' => [
                'site_name' => $s->site_name, 'site_description' => $s->site_description,
                'social_media' => ['facebook' => $s->facebook, 'linkedin' => $s->linkedin, 'instagram' => $s->instagram, 'twitter' => $s->twitter],
                'contact' => ['email' => $s->email, 'phone' => $s->phone, 'address' => $s->address],
                'logo' => $s->logo, 'copyright' => $s->copyright_text,
            ]
        ]);
    }
}
