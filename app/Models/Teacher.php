<?php

namespace App\Models;

class Teacher extends AbstractUser 
{
    public $caption = 'Преподавател';
    
    const ICON = 'user tie';

    protected function init(): void 
    {
        parent::init();

        /*
         * Default conditions
         */
        $this->addCondition('login_type', User::LOGIN_TYPE_TEACHER);
        
        /**
         * Relations
         */
        $this->hasMany('Disciplines', ['model' => new Discipline(), 'their_field' => 'teacher_id']);
    }
    
}
