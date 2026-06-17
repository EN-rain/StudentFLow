<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SchoolSetting;
use App\Models\SchoolSettingHistory;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;

class AdminSchoolSettingController extends Controller
{
    public function index()
    {
        $settings = SchoolSetting::with(['histories.user'])->orderBy('setting_key')->get();
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $payload = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable|string|max:1000',
        ]);

        foreach ($payload['settings'] as $key => $value) {
            $setting = SchoolSetting::firstOrCreate(
                ['setting_key' => $key],
                ['label' => ucwords(str_replace('_', ' ', $key))]
            );
            $old = $setting->setting_value;
            if ($old !== $value) {
                $setting->update(['setting_value' => $value]);
                SchoolSettingHistory::create([
                    'school_setting_id' => $setting->id,
                    'changed_by' => $request->user()->id,
                    'old_value' => $old,
                    'new_value' => $value,
                ]);
                ActivityLogger::log($request, 'setting.updated', $setting, ['key' => $key]);
            }
        }

        return back()->with('status', 'School settings updated.');
    }
}
