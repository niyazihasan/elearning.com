<?php

namespace App\Models;

class Student extends AbstractUser 
{
    public $caption = 'Студент';
    
    const ICON = 'user graduate';

    protected function init(): void 
    {
        parent::init();
        
        /*
         * Default conditions
         */
        $this->addCondition('login_type', User::LOGIN_TYPE_STUDENT);
        
        /**
         * Relations
         */
        $this->hasOne('Group', ['model' => new Group(), 'our_field' => 'group_id']);
        $this->hasMany('Tasks', ['model' => new Task(), 'their_field' => 'student_id']);
        $this->hasMany('Presences', ['model' => new Presence(), 'their_field' => 'student_id']);
    }
    
}
