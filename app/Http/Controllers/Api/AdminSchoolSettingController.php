<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SchoolSetting;
use App\Models\SchoolSettingHistory;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSchoolSettingController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => SchoolSetting::with('histories.user')->orderBy('setting_key')->get()]);
    }

    public function update(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable|string|max:1000',
        ]);

        $changed = [];
        foreach ($payload['settings'] as $key => $value) {
            $setting = SchoolSetting::firstOrCreate(
                ['setting_key' => $key],
                ['label' => ucwords(str_replace('_', ' ', $key))]
            );
            if ($setting->setting_value !== $value) {
                SchoolSettingHistory::create([
                    'school_setting_id' => $setting->id,
                    'changed_by' => $request->user()->id,
                    'old_value' => $setting->setting_value,
                    'new_value' => $value,
                ]);
                $setting->update(['setting_value' => $value]);
                ActivityLogger::log($request, 'setting.updated', $setting, ['key' => $key]);
                $changed[] = $setting->fresh();
            }
        }

        return response()->json(['data' => $changed]);
    }
}
