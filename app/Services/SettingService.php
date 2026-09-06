<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SettingService
{
    public function getSettings(): Setting
    {
        return Setting::getSettings();
    }

    public function updateSettings(array $data, ?UploadedFile $logo = null, ?UploadedFile $favicon = null): Setting
    {
        $settings = Setting::getSettings();

        if ($logo) {
            if ($settings->logo && Storage::disk('public')->exists($settings->logo)) {
                Storage::disk('public')->delete($settings->logo);
            }
            $data['logo'] = $logo->store('settings', 'public');
        }

        if ($favicon) {
            if ($settings->favicon && Storage::disk('public')->exists($settings->favicon)) {
                Storage::disk('public')->delete($settings->favicon);
            }
            $data['favicon'] = $favicon->store('settings', 'public');
        }

        $settings->update($data);

        return $settings;
    }

    public function getPublicSettings(): array
    {
        $s = Setting::getSettings();

        return [
            'site_name' => $s->site_name, 'site_description' => $s->site_description,
            'social_media' => ['facebook' => $s->facebook, 'linkedin' => $s->linkedin, 'instagram' => $s->instagram, 'twitter' => $s->twitter],
            'contact' => ['email' => $s->email, 'phone' => $s->phone, 'address' => $s->address],
            'logo' => $s->logo, 'copyright' => $s->copyright_text,
        ];
    }
}
