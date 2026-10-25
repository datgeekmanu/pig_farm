<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Farm\UpdateFarmRequest;
use App\Http\Requests\Farm\UpdateNotificationPrefsRequest;
use App\Http\Requests\Farm\UploadLogoRequest;
use App\Http\Resources\FarmSettingsResource;
use App\Models\Farm;
use App\Support\CurrentFarm;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class FarmSettingsController extends Controller
{
    public function show(): FarmSettingsResource
    {
        return FarmSettingsResource::make($this->farm());
    }

    public function update(UpdateFarmRequest $request): FarmSettingsResource
    {
        $farm = $this->farm();
        $farm->update($request->validated());

        return FarmSettingsResource::make($farm);
    }

    public function uploadLogo(UploadLogoRequest $request): FarmSettingsResource
    {
        $farm = $this->farm();

        if ($farm->logo_path) {
            Storage::disk('public')->delete($farm->logo_path);
        }
        $path = $request->file('logo')->store("farms/{$farm->id}/logo", 'public');
        $farm->update(['logo_path' => $path]);

        return FarmSettingsResource::make($farm);
    }

    public function notifications(): JsonResponse
    {
        return response()->json(['data' => $this->farm()->notification_prefs ?? (object) []]);
    }

    public function updateNotifications(UpdateNotificationPrefsRequest $request): JsonResponse
    {
        $farm = $this->farm();
        $farm->update(['notification_prefs' => $request->validated('prefs')]);

        return response()->json(['data' => $farm->notification_prefs]);
    }

    private function farm(): Farm
    {
        return Farm::findOrFail(app(CurrentFarm::class)->id());
    }
}
