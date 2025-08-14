<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Result;
use App\Models\Presence;
use App\Models\Level;

class ResultController extends Controller
{
    //

    // ____________ Subadmin _____________

    public function addEmptyMarks($subjectID) {
        $subject = Subject::with('level')->findOrFail($subjectID);

        $students = $subject->level->student;

        $skippedStudents = [];

        DB::beginTransaction();
        try {
            foreach ($students as $student) {
                if (Result::where('subjectID', $subjectID)
                        ->where('studentID', $student->id)
                        ->exists()) {
                    $skippedStudents[] = $student->id;
                    continue;
                }

                $result = Result::create([
                    'subjectID' => $subjectID,
                    'studentID' => $student->id,
                    'test' => null,
                    'exam' => null,
                    'presenceMark' => null,
                    'total' => null,
                    'status' => null,
                ]);

            }

            DB::commit();

            return response()->json([
                'message' => 'Empty result records initialized',
                'skipped_students' => $skippedStudents,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to initialize student marks',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    public function addTestMarks(Request $request) {
        $validated = $request->validate([
            'subjectID' => 'required|exists:subjects,id',
            'testMarks' => 'required|array',
            'testMarks.*.studentID' => 'required|exists:students,id',
            'testMarks.*.mark' => 'required|integer|min:0|max:30'
        ]);

        DB::beginTransaction();
        try {
            $results = [];
            $subject = Subject::findOrFail($validated['subjectID']);

            foreach ($validated['testMarks'] as $markData) {
                if (!$subject->level->student->contains($markData['studentID'])) {
                    continue;
                }

                $result = Result::updateOrCreate(
                    [
                        'subjectID' => $validated['subjectID'],
                        'studentID' => $markData['studentID']
                    ],
                    [
                        'test' => $markData['mark'],
                    ]
                );

                $results[] = [
                    'studentID' => $markData['studentID'],
                    'testMark' => $markData['mark'],
                    'resultID' => $result->id
                ];
            }

            DB::commit();

            return response()->json([
                'message' => 'Test marks added for all students',
                'results' => $results
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update test marks',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function addExamMarks(Request $request) {
        $validated = $request->validate([
            'subjectID' => 'required|exists:subjects,id',
            'examMarks' => 'required|array',
            'examMarks.*.studentID' => 'required|exists:students,id',
            'examMarks.*.mark' => 'required|integer|min:0|max:50'
        ]);

        DB::beginTransaction();
        try {
            $results = [];
            $subject = Subject::findOrFail($validated['subjectID']);

            foreach ($validated['examMarks'] as $markData) {
                if (!$subject->level->student->contains($markData['studentID'])) {
                    continue;
                }

                $presenceData = $this->calculatePresenceMark($markData['studentID'], $validated['subjectID']);
                $presenceMark = $presenceData['mark'];

                // Get existing test mark or default to 0
                $testMark = Result::where('subjectID', $validated['subjectID'])
                                ->where('studentID', $markData['studentID'])
                                ->value('test') ?? 0;

                // Calculate total
                $total = $testMark + $markData['mark'] + $presenceMark;

                $result = Result::updateOrCreate(
                    [
                        'subjectID' => $validated['subjectID'],
                        'studentID' => $markData['studentID']
                    ],
                    [
                        'exam' => $markData['mark'],
                        'presenceMark' => $presenceMark,
                        'total' => $total,
                        'status' => $total >= 60 ? 'successful' : 'failed'
                    ]
                );

                $results[] = [
                    'studentID' => $markData['studentID'],
                    'testMark' => $testMark,
                    'examMark' => $markData['mark'],
                    'presencePercentage' => $presenceData['percentage'],
                    'presenceMark' => $presenceMark,
                    'total' => $total,
                    'status' => $result->status,
                    'resultID' => $result->id
                ];

            }

            DB::commit();

            return response()->json([
                'message' => 'Exam marks updated',
                'results' => $results
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update exam marks',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function calculatePresenceMark($studentID, $subjectID) {
        $totalClasses = Presence::where('subjectID', $subjectID)
                            ->distinct('date')
                            ->count('date');

        $attendedClasses = Presence::where([
                                'subjectID' => $subjectID,
                                'studentID' => $studentID
                            ])->count();

        $percentage = $totalClasses > 0 ? ($attendedClasses / $totalClasses) * 100 : 0;
        $mark = round(($percentage / 100) * 20);

        return [
            'percentage' => $percentage,
            'mark' => $mark
        ];
    }


    public function getMarks($subjectID){
        try {
            $subject = Subject::findOrFail($subjectID);

            $results = Result::with('student')
                ->where('subjectID', $subjectID)
                ->get();

            $formattedData = [
                    'study' => [],
                    'exam' => [],
                    'attendance' => [],
                    'total' => [],
                    'status' => []
            ];

            foreach ($results as $result) {
                $studentID = $result->studentID;

                $formattedData['study'][$studentID] = $result->test ?? 0;
                $formattedData['exam'][$studentID] = $result->exam ?? 0;
                $formattedData['attendance'][$studentID] = $result->presenceMark ?? 0;
                $formattedData['total'][$studentID] = $result->total ?? 0;
                $formattedData['status'][$studentID] = $result->status ?? 'failed';
            }

            return response()->json([
                'message' => 'get data',
                'data' => $formattedData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve student marks',
                'error' => $e->getMessage()
            ], 500);
        }

    }


    // ____________ Student _____________

    public function getMarksStudent($subjectID) {
        try {

            $user = Auth::user();
            $student = $user->student()->firstOrFail();
            $studentID = $student->id;

            $result = Result::where([
                'subjectID' => $subjectID,
                'studentID' => $studentID
            ])->first();

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'No marks found for this subject'
                ], 404);
            }

            return response()->json([
                'results' => [
                    'study' => $result->test ?? 0,
                    'exam' => $result->exam ?? 0,
                    'presence' => $result->presenceMark ?? 0,
                    'total' => $result->total ?? 0,
                    'status' => $result->status ?? 'failed'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve marks',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // ___________ Teacher _____________

    public function getTeacherSubjects($courseID ,$levelName) {
        try {
            $user = Auth::user();
            $teacher = $user->teacher()->firstOrFail();
            $teacherID = $teacher->id;

            $level = Level::where('courseID', $courseID)
                    ->where('levelName', $levelName)
                    ->first();

            $subjects = Subject::with('level:id,levelName')
                ->where([
                    'teacherID' => $teacherID,
                    'levelID' => $level->id
                ])
                ->get(['id', 'subjectName', 'levelID']);

            if ($subjects->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No subjects found for this level'
                ], 404);
            }

            return response()->json([
                'subjects' => $subjects->map(function ($subject) {
                    return [
                        'subjectID' => $subject->id,
                        'subjectName' => $subject->subjectName
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve subjects',
                'error' => $e->getMessage()
            ], 500);
        }

    }




}









