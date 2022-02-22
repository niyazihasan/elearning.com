<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    const LOGIN_TYPE_ADMIN = 'admin';
    const LOGIN_TYPE_TEACHER = 'teacher';
    const LOGIN_TYPE_STUDENT = 'student';
    
    const LOGIN_TYPES = [
        self::LOGIN_TYPE_ADMIN,
        self::LOGIN_TYPE_TEACHER,
        self::LOGIN_TYPE_STUDENT
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name', 'fname', 'sname',
        'email', 'password', 'active',
        'group_id', 'login_type'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token'
    ];
    
    public function isAdmin()
    {
        return auth()->user()->login_type === self::LOGIN_TYPE_ADMIN;
    }
    
    public function isTeacher()
    {
        return auth()->user()->login_type === self::LOGIN_TYPE_TEACHER;
    }
    
    public function isStudent()
    {
        return auth()->user()->login_type === self::LOGIN_TYPE_STUDENT;
    }

}