<?php

namespace App\Http\Controllers;

use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CurriculumPlan;

class CurriculumPlanController extends Controller
{
    public function addCurriculumPlan(Request $request)
    {
        $validated = $request->validate([
            'courseID' => ['required', 'exists:users,id'],
            'levelName' => ['required', 'string', 'in:introductory,level1,level2,level3,level4,level5,level6'],
            'subjectName' => ['required', 'string'],
            'sessionDate' => ['required', 'string'],
            'sessionContent' => ['required', 'string'],
        ]);

        $level = Level::where('courseID',$validated['courseID'])
                ->where('levelName', $validated['levelName'])
                ->first();

        if (!$level) {
            return response()->json([
                'message' => 'Level not found.'
            ], 404);
        }

        $session = $level->curriculumPlans()->create([
            'levelID' => $level->id,
            'subjectName' => $validated['subjectName'],
            'sessionDate' => $validated['sessionDate'],
            'sessionContent' => $validated['sessionContent'],
        ]);

        return response()->json([
            'message' => 'Session added successfully',
        ]);
    }


    public function getCurriculumPlan($courseID, $levelName)
    {
        $user = Auth::user();

        $level = Level::with('curriculumPlans')
                    ->where('courseID',$courseID)
                    ->where('levelName', $levelName)
                    ->first();

        if (!$level) {
            return response()->json([
                'message' => 'Level not found.'
            ], 404);
        }

        $curriculum = $level->curriculumPlans->map(function ($plan) {
            return [
                'id' => $plan->id,
                'subjectName' => $plan->subjectName,
                'sessionDate' => $plan->sessionDate,
                'sessionContent' => $plan->sessionContent,
            ];
        });

        return response()->json([
            'levelId' => $level->id,
            'curriculumPlan' => $curriculum
        ]);
    }

    public function deleteCurriculumPlan($sessionID)
    {
        $session = CurriculumPlan::where('id', $sessionID)->get()->first();

        if (!$session) {
            return response()->json(['message' => 'Session not found'], 404);
        }

        $session->delete();

        return response()->json([
            'message' => 'Curriculum session deleted successfully',
        ]);
    }
}
