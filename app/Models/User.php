<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Admin;
use App\Models\Teacher;
use App\Models\Subadmin;
use App\Models\Student;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'role',
        'name',
        'email',
        'password',
        'firstAndLastName',
        'fatherName',
        'phoneNumber',
        'address',
        'birthDate',
    ];

    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function subadmin()
    {
        return $this->hasOne(Subadmin::class);
    }

    public function admin()
    {
        return $this->hasOne(related: Admin::class);
    }

public function userProfile(){
        return $this->hasOne(related: UserProfile::class);

}
public function courses()
{
    return $this->belongsToMany(Course::class,'courseStudentPivots');
}

public function marks()
{
    return $this->hasMany(Mark::class);
}


// public function supervisedCourses()
// {
//     return $this->hasMany(Course::class, 'subadmin_id');
// }

    // Helper methods
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isTeacher()
    {
        return $this->role === 'teacher';
    }

    public function isStudent()
    {
        return $this->role === 'student';
    }

    public function isSubadmin()
    {
        return $this->role === 'subadmin';
    }


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
