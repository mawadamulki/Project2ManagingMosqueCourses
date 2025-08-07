<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Worksheet;
use App\Models\QuestionOption;
use App\Models\Question;
use App\Models\Answer;
use App\Models\Student;
use App\Models\Teacher;

class WorksheetController extends Controller
{
    /*  ____ Teacher ____

    1) add worksheet      DONE
    2) add question       DONE
    3) edit question      DONE
    4) delete question    DONE
    5) delete worksheet   DONE
    6) add answers

        ____ Student ____

    1) add solution
    2)

    */




    // _______________ TEACHER _______________


    public function addWorksheet(Request $request) {

        $validated = $request->validate([
            'subjectID' => 'required|exists:subjects,id',
            'worksheetName' => 'required|string|unique:worksheets,worksheetName,NULL,id,subjectID,'.$request->subjectID,
            'questions' => 'required|array|min:1',
            'questions.*.type' => 'required|in:automation,editorial',
            'questions.*.question' => 'required|string',
            'questions.*.options' => 'required_if:questions.*.type,automation|array|min:2',
            'questions.*.options.*' => 'required_if:questions.*.type,automation|string'
        ]);

        DB::beginTransaction();

        try {
            $worksheet = Worksheet::create([
                'worksheetName' => $validated['worksheetName'],
                'subjectID' => $validated['subjectID'],
            ]);

            foreach ($validated['questions'] as $questionData) {
                $question = $worksheet->questions()->create([
                    'type' => $questionData['type'],
                    'question' => $questionData['question'],
                    'correct_answer' => $questionData['correct_answer'] ?? null,
                ]);

                if ($questionData['type'] === 'automation') {
                    foreach ($questionData['options'] as $option) {
                        $question->options()->create(['option' => $option]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Worksheet Added Successfully.'
            ], 201);
            // return response()->json([
            //     'worksheet' => [
            //         'id' => $worksheet->id,
            //         'name' => $worksheet->worksheetName,
            //         'subject_id' => $worksheet->subjectID,
            //         'questions' => $worksheet->questions->map(fn($q) => [
            //             'questionID' => $q->id,
            //             'type' => $q->type,
            //             'question' => $q->question,
            //             'options' => $q->type === 'automation'
            //                 ? $q->options->pluck('option')->toArray()
            //                 : null
            //         ])
            //     ]
            // ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create worksheet',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    public function deleteWorksheet($worksheetID) {
        try {
            $worksheet = Worksheet::with('questions.options')->findOrFail($worksheetID);

            $worksheet->questions->each(function($question) {
                $question->options()->delete();
                $question->answers()->delete();
                $question->delete();
            });

            $worksheet->delete();

            return response()->json([
                'message' => 'Worksheet and all related questions deleted successfully'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Worksheet not found'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete worksheet',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function deleteQuestion($questionID){
        try {
            $question = Question::findOrFail($questionID);
            $question->delete();

            return response()->json([
                'message' => 'Question deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete question',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function editQuestion(Request $request) {
        $validated = $request->validate([
            'questionID' => 'required|exists:questions,id',
            'type' => 'sometimes|in:automation,editorial',
            'question' => 'sometimes|string',
            'options' => 'required_if:type,automation|array|min:2',
            'options.*' => 'required_if:type,automation|string',
        ]);

        $question = Question::with('options')->findOrFail($validated['questionID']);

        $question->fill($request->only(['type', 'question']));
        $question->save();


        if ($question->type === 'automation' && isset($validated['options'])) {
            $question->options()->delete();

            foreach ($validated['options'] as $option) {
                $question->options()->create(['option' => $option]);
            }
        }

        return response()->json([
            'message' => 'Question updated successfully',
            'question' => [
                    'id' => $question->id,
                    'type' => $question->type,
                    'question' => $question->question,
                    'options' => $question->type === 'automation'
                        ? $question->options->pluck('option')->toArray()
                        : []
                ]
        ], 200);

    }



    public function addQuestionToWorksheet(Request $request) {

        $validated = $request->validate([
            'worksheetID' => 'required|exists:worksheets,id',
            'type' => 'required|in:automation,editorial',
            'question' => 'required|string',
            'options' => 'required_if:type,automation|array|min:2',
            'options.*' => 'required_if:type,automation|string'
        ]);

        DB::beginTransaction();

        try {
            $worksheet = Worksheet::findOrFail($validated['worksheetID']);

            $question = $worksheet->questions()->create([
                'type' => $validated['type'],
                'question' => $validated['question'],
            ]);

            if ($validated['type'] === 'automation') {
                foreach ($validated['options'] as $option) {
                    $question->options()->create(['option' => $option]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'question added successfully',
                'question' => [
                    'id' => $question->id,
                    'type' => $question->type,
                    'question' => $question->question,
                    'options' => $question->type === 'automation'
                        ? $question->options->pluck('option')->toArray()
                        : []
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to add question',
                'error' => $e->getMessage()
            ], 500);
        }

    }


    public function teacherSubmitAnswers(Request $request)
    {
        $request->validate([
            'answers' => 'required|array',
            'answers.*.questionID' => 'required|exists:questions,id',
            'answers.*.answer' => 'required|string',
        ]);

        $user = Auth::user();
        $teacher = Teacher::where('userID', $user->id)->firstOrFail();

        $questionIds = collect($request->answers)->pluck('questionID')->unique();

        $existingAnswers = Answer::where('teacherID', $teacher->id)
            ->whereIn('questionID', $questionIds)
            ->get()
            ->groupBy('questionID');

        foreach ($request->answers as $answerData) {
            if ($existingAnswers->has($answerData['questionID'])) {
                return response()->json([
                    'message' => 'You already submitted answers for same worksheet, please edit it instead.',
                    'conflicts' => $existingAnswers->map(function($answers, $questionId) {
                        return [
                            'questionID' => $questionId,
                            'existingAnswerID' => $answers->first()->id,
                            'message' => 'Please edit your existing answer instead'
                        ];
                    })->values(),
                    'error_code' => 'answers_exist'
                ], 422);
            }
        }

        $answers = [];
        $now = now();

        foreach ($request->answers as $answerData) {
            $answers[] = [
                'teacherID' => $teacher->id,
                'questionID' => $answerData['questionID'],
                'answer' => $answerData['answer'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::transaction(function () use ($answers) {
            Answer::insert($answers);
        });


        return response()->json([
            'message' => 'answers submitted successfully'
        ], 201);


    }


    public function teacherEditAnswer(Request $request) {

        $request->validate([
            'answerID' => 'required|exists:answers,id',
            'answer' => 'required|string',
        ]);

        $user = Auth::user();
        $teacher = Teacher::where('userID', $user->id)->firstOrFail();

        $answer = Answer::where('id', $request->answerID)
                    ->where('teacherID', $teacher->id)
                    ->firstOrFail();

        $answer->update([
            'answer' => $request->answer,
        ]);

        $an[] = [
            'id' => $answer->id,
            'teacherID' => $answer->teacherID,
            'questionID' => $answer->questionID,
            'answer' => $answer->answer,
        ];

        return response()->json([
            'message' => 'Answer updated successfully',
            'answer' => $an
        ]);

    }



    // ______________ Student ________________


    public function studentSubmitAnswers(Request $request)
    {
        $request->validate([
            'answers' => 'required|array',
            'answers.*.questionID' => 'required|exists:questions,id',
            'answers.*.answer' => 'required|string',
        ]);

        $user = Auth::user();
        $student = Student::where('userID', $user->id)->firstOrFail();

        $questionIds = collect($request->answers)->pluck('questionID')->unique();

        $existingAnswers = Answer::where('studentID', $student->id)
            ->whereIn('questionID', $questionIds)
            ->get()
            ->groupBy('questionID');

        foreach ($request->answers as $answerData) {
            if ($existingAnswers->has($answerData['questionID'])) {
                return response()->json([
                    'message' => 'You already submitted answers for same worksheet, please edit it instead.',
                    'conflicts' => $existingAnswers->map(function($answers, $questionId) {
                        return [
                            'questionID' => $questionId,
                            'existingAnswerID' => $answers->first()->id,
                            'message' => 'Please edit your existing answer instead'
                        ];
                    })->values(),
                ], 422);
            }
        }


        $answers = [];
        $now = now();

        foreach ($request->answers as $answerData) {
            $answers[] = [
                'studentID' => $student->id,
                'questionID' => $answerData['questionID'],
                'answer' => $answerData['answer'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::transaction(function () use ($answers) {
            Answer::insert($answers);
        });

        return response()->json([
            'message' => 'answers submitted successfully'
        ], 201);
    }


    public function studentEditAnswer(Request $request) {

        $request->validate([
            'answerID' => 'required|exists:answers,id',
            'answer' => 'required|string',
        ]);

        $user = Auth::user();
        $student = Student::where('userID', $user->id)->firstOrFail();

        $answer = Answer::where('id', $request->answerID)
                    ->where('studentID', $student->id)
                    ->firstOrFail();

        $answer->update([
            'answer' => $request->answer,
        ]);

        $an[] = [
            'id' => $answer->id,
            'studentID' => $answer->studentID,
            'questionID' => $answer->questionID,
            'answer' => $answer->answer,
        ];

        return response()->json([
            'message' => 'Answer updated successfully',
            'answer' => $an
        ]);

    }


    public function getWorksheets($subjectID) {

        $worksheets = Worksheet::where('subjectID', $subjectID)
        ->select('id', 'worksheetName')->get();

        return response()->json([
            'worksheets' => $worksheets
        ]);

    }



    public function getWorksheetWithAnswers($worksheetId) {
        $user = Auth::user();

        $worksheet = Worksheet::with(['questions' => function($query) {
            $query->with('options:id,questionID,option');
        }])->findOrFail($worksheetId);

        $answers = collect();

        if ($user->teacher) {
            $answers = Answer::where('teacherID', $user->teacher->id)
                ->whereIn('questionID', $worksheet->questions->pluck('id'))
                ->with(['student' => function($query) {
                    $query->with('user:id,name');
                }])
                ->get()
                ->groupBy('questionID');

        } elseif ($user->student) {
            $answers = Answer::where('studentID', $user->student->id)
                ->whereIn('questionID', $worksheet->questions->pluck('id'))
                ->with(['teacher' => function($query) {
                    $query->with('user:id,name');
                }])
                ->get()
                ->groupBy('questionID');
        }

        return response()->json([
            'worksheet' => [
                'id' => $worksheet->id,
                'name' => $worksheet->worksheetName,
                'subject_id' => $worksheet->subjectID,
                'questions' => $worksheet->questions->map(function($question) use ($answers, $user) {
                    $questionAnswers = $answers->get($question->id, collect());

                    return [
                        'id' => $question->id,
                        'question' => $question->question,
                        'type' => $question->type,
                        'options' => $question->options->pluck('option')->toArray(),
                        'answer' => $questionAnswers->map(function($answer) use ($user) {
                            $response = [
                                'id' => $answer->id,
                                'answer' => $answer->answer
                            ];

                            return $response;
                        })
                    ];
                })
            ]
        ]);
    }


    public function getTeacherAnswers($worksheetID) {

        $user = Auth::user();
        $student = $user->student()->firstOrFail();


        $worksheet = Worksheet::with(['questions' => function($query) {
            $query->with(['answers' => function($q) {
                $q->whereNotNull('teacherID') // Only teacher-provided answers
                ->with('teacher.user:id,firstAndLastName'); // Include teacher info
            }]);
        }])->findOrFail($worksheetID);


        $formattedQuestions = $worksheet->questions->map(function($question) {
            return [
                'questionID' => $question->id,
                'question' => $question->question,
                'type' => $question->type,
                'teacherAnswers' => $question->answers->map(function($answer) {
                    return [
                        'answerID' => $answer->id,
                        'answer' => $answer->answer
                    ];
                })
            ];
        });

        return response()->json([
            'worksheet_id' => $worksheet->id,
            'worksheet_name' => $worksheet->worksheetName,
            'questions' => $formattedQuestions
        ]);
    }


}
