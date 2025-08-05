<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Worksheet;
use App\Models\QuestionOption;
use App\Models\Question;

class WorksheetController extends Controller
{
    /*  ____ Teacher ____

    1) add worksheet      DONE
    2) add question
    3) edit question
    4) delete question    DONE
    5) delete worksheet   DONE

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
                'worksheet' => [
                    'id' => $worksheet->id,
                    'name' => $worksheet->worksheetName,
                    'subject_id' => $worksheet->subjectID,
                    'questions' => $worksheet->questions->map(fn($q) => [
                        'questionID' => $q->id,
                        'type' => $q->type,
                        'question' => $q->question,
                        'options' => $q->type === 'automation'
                            ? $q->options->pluck('option')->toArray()
                            : null
                    ])
                ]
            ], 201);

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

            // Delete related records through Eloquent relationships
            $worksheet->questions->each(function($question) {
                $question->options()->delete(); // Delete all options first
                $question->delete(); // Then delete the question
            });

            $worksheet->delete(); // Finally delete the worksheet

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



    // ______________ Student ________________


    public function submitAnswers(Request $request)
    {
        $validated = $request->validate([
            'teacherID' => 'required|exists:teachers,id',
            'worksheetID' => 'required|exists:worksheets,id',
            'answers' => 'required|array',
            'answers.*.questionID' => 'required|exists:questions,id,worksheetID,'.$request->worksheetID,
            'answers.*.answer' => 'required|string'
        ]);

        DB::beginTransaction();

        try {
            $teacherID = $validated['teacherID'];
            $worksheet = Worksheet::with('questions')->find($validated['worksheetID']);

            // Delete existing teacher answers for these questions
            Answer::whereIn('questionID', $worksheet->questions->pluck('id'))
                ->whereNotNull('teacherID')
                ->delete();

            // Store new answers
            $createdAnswers = [];
            foreach ($validated['answers'] as $answerData) {
                $question = Question::find($answerData['questionID']);

                $answer = Answer::create([
                    'teacherID' => $teacherID,
                    'questionID' => $answerData['questionID'],
                    'answer' => $answerData['answer'],
                    'is_correct' => true
                ]);

                // Update correct_answer in questions table for automation questions
                if ($question->type === 'automation') {
                    $question->update(['correct_answer' => $answerData['answer']]);
                }

                $createdAnswers[] = [
                    'question_id' => $answerData['questionID'],
                    'answer_id' => $answer->id
                ];
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'All answers submitted successfully',
                'answers' => $createdAnswers,
                'total_questions' => $worksheet->questions->count(),
                'answers_submitted' => count($createdAnswers)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit answers',
                'error' => $e->getMessage()
            ], 500);
        }
    }



}
