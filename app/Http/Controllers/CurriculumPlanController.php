<?php

namespace App\Http\Controllers;

use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CurriculumPlanController extends Controller
{
    public function addCurriculumPlanToLevel(Request $request, $levelId)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'sessionDate' => ['required', 'string'],
            'sessionContent' => ['required', 'string'],
        ]);

        try {
            $carbonDate = Carbon::parse($validated['sessionDate']);
            $dayName = $carbonDate->locale('ar')->translatedFormat('l');
            $formattedDate = $dayName . ' ' . $carbonDate->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid date format'], 400);
        }

        $level = Level::findOrFail($levelId);

        if ($level->curriculumPlans()->count() >= 35) {
            return response()->json(['message' => 'Maximum of 35 sessions allowed per level'], 400);
        }

        $session = $level->curriculumPlans()->create([
            'sessionDate' => $formattedDate,
            'sessionContent' => $validated['sessionContent'],
        ]);

        return response()->json([
            'message' => 'Session added successfully',
            'session date' => $session->sessionDate,
            'session content' => $session->sessionContent,
        ]);
    }


    public function getCurriculumPlanByLevel($levelId)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $level = Level::with('curriculumPlans')->find($levelId);

        if (!$level) {
            return response()->json(['message' => 'Level not found'], 404);
        }
        $curriculum = $level->curriculumPlans->map(function ($plan) {
            return [
                'sessionDate' => $plan->sessionDate,
                'sessionContent' => $plan->sessionContent,
            ];
        });

        return response()->json([
            'courseID' => $level->courseID,
            'levelName' => $level->levelName,
            'levelId' => $level->id,
            'curriculumPlan' => $curriculum
        ]);
    }

    public function updateCurriculumPlanForLevel(Request $request, $levelId, $sessionId)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'sessionDate' => ['sometimes', 'string'],
            'sessionContent' => ['sometimes', 'string'],
        ]);

        if (empty($validated)) {
            return response()->json(['message' => 'No data provided to update'], 400);
        }
        $level = Level::find($levelId);

        if (!$level) {
            return response()->json(['message' => 'Level not found'], 404);
        }

        $session = $level->curriculumPlans()->where('id', $sessionId)->first();

        if (!$session) {
            return response()->json(['message' => 'Session not found in this level'], 404);
        }
        $updateData = [];

        if (isset($validated['sessionDate'])) {

            try {
                $carbonDate = Carbon::parse($validated['sessionDate']);
                $dayName = $carbonDate->locale('ar')->translatedFormat('l');
                $formattedDate = $dayName . ' ' . $carbonDate->format('Y-m-d');
                $updateData['sessionDate'] = $formattedDate;
            } catch (\Exception $e) {
                return response()->json(['message' => 'Invalid date format'], 400);
            }
        }

        if (isset($validated['sessionContent'])) {
            $updateData['sessionContent'] = $validated['sessionContent'];
        }

        $session->update($updateData);

        return response()->json([
            'message' => 'Curriculum session updated successfully',
            'updated_session' => [
                'id' => $session->id,
                'sessionDate' => $session->sessionDate,
                'sessionContent' => $session->sessionContent,
            ]
        ]);
    }
}
