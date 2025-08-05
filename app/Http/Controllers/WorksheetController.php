<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WorksheetController extends Controller
{
    /*  Teacher

    1) add worksheet
    2) add question
    3) edit question
    4) delete question

        Student

    1) add solution
    2)

    */


    // _______________ TEACHER _______________

    public function addWorksheet(Request $request) {
        $validated = $request->validate([
            'subjectID' => 'required|exists:subjects,id',
            'worksheetName' => 'required|string',
            'questions' => 'required|array',
            'questions.*.type' => 'required|in:automation,editorial',
            'questions.*.question' => 'required|string',
            'questions.*.options' => 'required_if:questions.*.type,automation|array|min:2',
            'questions.*.options.*' => 'required_if:questions.*.type,automation|string',
        ]);

        if (Worksheet::where('worksheetName', $validated['worksheetName'])
                ->where('subjectID', $validated['subjectID'])
                ->exists()) {
            return response()->json(['message' => 'Worksheet Already Exists'], 409);
        }

        $worksheet = Worksheet::create([
            'worksheetName' => $validated['worksheetName'],
            'subjectID' => $validated['subjectID'],
        ]);

        foreach ($validated['questions'] as $questionData) {
            $question = $worksheet->questions()->create([
                'type' => $questionData['type'],
                'question' => $questionData['question'],
            ]);

            if ($questionData['type'] === 'automation' && isset($questionData['options'])) {
                foreach ($questionData['options'] as $option) {
                    $question->options()->create(['option' => $option]);
                }
            }
        }

        return response()->json([
            'message' => 'Worksheet created with questions',
            'data' => $worksheet->load('questions.options')
        ], 201);
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
            'correct_answer' => 'required_if:type,automation',
            'options' => 'required_if:type,automation|array|min:2',
            'options.*' => 'required_if:type,automation|string',
        ]);

        try {
            $question = Question::with('options')->findOrFail($validated['questionID']);

            $question->fill($request->only(['type', 'question', 'correct_answer']));
            $question->save();


            if ($question->type === 'automation' && isset($validated['options'])) {
                $question->options()->delete();

                foreach ($validated['options'] as $option) {
                    $question->options()->create(['option' => $option]);
                }
            }

            return response()->json([
                'message' => 'Question updated successfully',
                'question' => $question->load('options')
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update question',
                'error' => $e->getMessage()
            ], 500);
        }
    }


}
