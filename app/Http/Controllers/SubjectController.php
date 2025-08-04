<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Level;
use App\Models\Subject;
use App\Models\Curriculum;
use App\Models\Extension;
use App\Models\Student;
use App\Models\BookRequest;


class SubjectController extends Controller
{
    //  ________ADMIN________
    // 1) show subject names                         DONE
    // 2) add subject in level (teacher + name )     DONE
    // 3) show teacher to add subject                DONE
    // 4) upload Curriculum                          DONE
    // 5) show subject detail                        DONE
    // 6) edit Curriculum                            DONE

    //  _______TEACHER________
    // 1) show subject (with his)             DONE (NOT IN NEED)
    // 2) show subject details (all)          DONE (SAME ADMIN)
    // 3) add extension                       DONE
    // 4) detete extension                    DONE

    //  _______STUDENT________
    // 1) show subject                      DONE (NOT IN NEED)
    // 2) show details                      DONE
    // 3) request book                      DONE

    //  _______SUBADMIN________
    // 1) show book requests                DONE






    // __________ Admin api ___________

    public function getTeachers() {

        $teachers = User::where('role', 'teacher')
                        ->join('teachers', 'users.id', '=', 'teachers.userID')
                        ->select(
                            'teachers.id as id', // Get ID from teachers table
                            'users.firstAndLastName'     // Get name from users table
                        )
                        ->get();

        return response()->json([
            'teachers' => $teachers
        ]);
    }


    public function addSubject(Request $request){

        $validated = $request->validate([
                'subjectName' => 'required|string',
                'teacherID' => 'required|exists:teachers,id',
                'levelName' => 'required|in:introductory,level1,level2,level3,level4,level5,level6',
                'courseID' => 'required|exists:courses,id',
        ]);

        $level = Level::where('courseID', $validated['courseID'])
                    ->where('levelName', $validated['levelName'])
                    ->first();

        if (!$level) {
            return response()->json([
                'message' => 'Level not found for this course'
            ], 404);
        }

         if (Subject::where('subjectName', $validated['subjectName'])
                ->where('teacherID', $validated['teacherID'])
                ->exists()) {
            return response()->json([
                'message' => 'Subject Already Created'
            ], 409);
        }

        $subject = Subject::create([
            'subjectName' => $validated['subjectName'],
            'levelID' => $level->id,
            'teacherID' => $validated['teacherID'],
        ]);

        return response()->json([
            'message' => 'Subject created successfully',
            'subject' => $subject
        ], 201);

    }


    public function getSubjects($courseID ,$levelName){
        $level = Level::where('courseID', $courseID)
                    ->where('levelName', $levelName)
                    ->first();

        $subjects = Subject::where('levelID', $level->id)->pluck('subjectName');

        return response()->json([
            'subjects' => $subjects
        ]);
    }

    public function addCurriculum(Request $request){

        $validated = $request->validate([
            'subjectID' => 'required|exists:subjects,id',
            'curriculumFile' => 'required|file|max:51200' // 50MB max
        ]);

        if(Curriculum::where('subjectID',$validated['subjectID'])->first())
            return response()->json([
                    'message' => 'the subject already have curriculum , please update it'
            ]);

        $path = $request->file('curriculumFile')->store('curricula', 'public');

        $fullPath = asset('storage/' . $path);

        $curriculumName = $request->file('curriculumFile')->getClientOriginalName();

        // Create curriculum record
        $curriculum = Curriculum::create([
            'subjectID' => $validated['subjectID'],
            'curriculumFile' => $fullPath,
            'curriculumName' => $curriculumName
        ]);

        return response()->json([
            'message' => 'Curriculum uploaded successfully',
            'curriculum' => $curriculum
        ], 201);

    }


    public function updateCurriculum(Request $request){
        $validated = $request->validate([
            'subjectID' => 'required|exists:subjects,id',
            'curriculumFile' => 'required|file|max:51200'
        ]);

        $subject = Subject::findOrFail($validated['subjectID']);

        $curriculum = $subject->curriculum;
        if (!$subject->curriculum) {
            return response()->json([
                'message' => 'No curriculum found for this subject'
            ], 404);
        }

        if ($subject->curriculum->curriculumFile) {
            $oldPath = str_replace(asset('storage/'), '', $subject->curriculum->curriculumFile);
            Storage::disk('public')->delete($oldPath);
        }

        $path = $request->file('curriculumFile')->store('curricula', 'public');

        $fullPath = asset('storage/' . $path);

        $curriculumName = $request->file('curriculumFile')->getClientOriginalName();

        $curriculum->update([
            'curriculumFile' => $fullPath,
            'curriculumName' => $curriculumName
        ]);

        return response()->json([
            'message' => 'Curriculum updated successfully',
            'curriculum' => $curriculum,
            'file_size' => Storage::disk('public')->size($path)
        ]);

    }

    public function getSubjectDetails($courseID, $levelName){
        $subjects = DB::table('subjects')
            ->join('levels', 'subjects.levelID', '=', 'levels.id')
            ->join('teachers', 'subjects.teacherID', '=', 'teachers.id')
            ->join('users', 'teachers.userID', '=', 'users.id')
            ->leftJoin('curricula', 'subjects.id', '=', 'curricula.subjectID')
            ->select([
                'subjects.id',
                'subjects.subjectName',
                'teachers.id as teacherID',
                'users.firstAndLastName as teacherName',
                'curricula.id as curriculumID',
                'curricula.curriculumFile',
                'curricula.curriculumName'
            ])
            ->where('levels.courseID', $courseID)
            ->where('levels.levelName', $levelName)
            ->get();

        $subjectIds = $subjects->pluck('id');
        $extensions = DB::table('extensions')
            ->whereIn('subjectID', $subjectIds)
            ->get()
            ->groupBy('subjectID');

        $subjects->each(function ($subject) use ($extensions) {
            $subject->extensions = $extensions->get($subject->id, []);
        });


        return response()->json([
            'subjects' => $subjects
        ]);
    }





    // __________ Teacher api _____________

    public function getSubject(){

    }

    public function addExtension(Request $request){
        $validated = $request->validate([
            'subjectID' => 'required|exists:subjects,id',
            'extensionFile' => 'required|file|max:51200' // 50MB max
        ]);

        $path = $request->file('extensionFile')->store('extensions', 'public');

        $fullPath = asset('storage/' . $path);

        $extensionName = $request->file('extensionFile')->getClientOriginalName();


        $extension = Extension::create([
            'subjectID' => $validated['subjectID'],
            'extensionFile' => $fullPath,
            'extensionName' => $extensionName
        ]);

        return response()->json([
            'message' => 'extension uploaded successfully',
            'extension' => $extension
        ], 201);
    }

    public function deleteExtension($extensionID){
        $extension = Extension::find($extensionID);

        if(!$extension){
            return response()->json([
                'message' => 'extension not found'
            ], 404);
        }

        try {

            $oldPath = str_replace(asset('storage/'), '', $extension->extensionFile);
            Storage::disk('public')->delete($oldPath);

            $extension->delete();

            return response()->json([
                'message' => 'Extension deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete extension',
                'error' => $e->getMessage()
            ], 500);
        }

    }





    //  __________ Student APIs ____________

    public function requestBook($curriculumID){
        $user = Auth::user();
        $student = Student::where('userID', $user->id)->get()->first();

        $curriculum = Curriculum::where('id',$curriculumID)->get()->first();

        if($curriculum == null){
            return response()->json([
                'message' => 'Curriculum Not Found'
            ], 404);
        }

        if(BookRequest::where('studentID',$student->id)
                       ->where('curriculumID',$curriculum->id)->get()->first()){

            return response()->json([
                'message' => 'Book Request Already Added!'
            ], 409);
        }

        $bookRequest = BookRequest::create([
            'studentID' => $student->id,
            'curriculumID' => $curriculum->id
        ]);


        return response()->json([
            'message' => 'Book Request Added Successfully',
            'bookRequest' => $bookRequest
        ], 201);
    }


    public function getSubjectDetailsStudent($courseID){

        $student = Student::where('userID', Auth::id())->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        // Get the student's level from the pivot table
        $levelStudent = DB::table('level_student_pivot')
            ->join('levels', 'level_student_pivot.levelID', '=', 'levels.id')
            ->where('level_student_pivot.studentID', $student->id)
            ->where('levels.courseID', $courseID)
            ->select('levels.*')
            ->first();

        if (!$levelStudent) {
            return response()->json(['error' => 'Student is not enrolled in any level for this course'], 404);
        }

        $subjects = DB::table('subjects')
            ->join('levels', 'subjects.levelID', '=', 'levels.id')
            ->join('teachers', 'subjects.teacherID', '=', 'teachers.id')
            ->join('users', 'teachers.userID', '=', 'users.id')
            ->leftJoin('curricula', 'subjects.id', '=', 'curricula.subjectID')
            ->select([
                'subjects.id',
                'subjects.subjectName',
                'teachers.id as teacherID',
                'users.firstAndLastName as teacherName',
                'curricula.id as curriculumID',
                'curricula.curriculumFile',
                'curricula.curriculumName'
            ])
            ->where('levels.courseID', $courseID)
            ->where('levels.id', $levelStudent->id)
            ->get();

        $subjectIds = $subjects->pluck('id');
        $curriculumIds = $subjects->pluck('curriculumID')->filter()->unique();

        $extensions = DB::table('extensions')
                ->whereIn('subjectID', $subjectIds)
                ->get()
                ->groupBy('subjectID');

        // Get requested books for this student
        $requestedBooks = DB::table('book_requests')
                ->join('curricula', 'book_requests.curriculumID', '=', 'curricula.id')
                ->where('book_requests.studentID', $student->id)
                ->whereIn('book_requests.curriculumID', $curriculumIds)
                ->select('curricula.curriculumName')
                ->pluck('curricula.curriculumName')
                ->toArray();

        // Add extensions to subjects
        $subjects->each(function ($subject) use ($extensions, $requestedBooks) {
            $subject->extensions = $extensions->get($subject->id, []);
        });

        return response()->json([
            'levelName' => $levelStudent->levelName,
            'subjects' => $subjects,
            'requested_books' => $requestedBooks
        ]);
    }






    // _________ Subadmin APIs ____________
    public function getBookRequestStudents($curriculumID){

        if (!$curriculum = Curriculum::find($curriculumID)) {
            return response()->json(['message' => 'Curriculum not found'], 404);
        }

        $students = BookRequest::with('student.user')
            ->where('curriculumID', $curriculumID)
            ->get()
            ->map(fn($r) => [
                'studentID' => $r->student->id,
                'studentName' => $r->student->user->firstAndLastName
            ]);

        return response()->json([
            'total' => $students->count(),
            'students' => $students
        ]);
    }



}
