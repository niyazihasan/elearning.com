<?php

namespace App\Models;

use Atk4\Data\Model;

class Discipline extends Model 
{
    use \App\Traits\AuditTrait;
    
    public $table = 'disciplines';
    public $caption = 'Предмет';
    
    const ICON = 'book';
    const COURSES = ['1', '2', '3', '4'];
    const SEMESTERS = ['1', '2', '3', '4', '5', '6', '7', '8'];

    protected function init(): void 
    {
        parent::init();
        
        /*
         * Traits
         */
        $this->initAudit();

        /*
         * Fields
         */
        $this->addField('name', ['type' => 'string', 'required' => true, 'caption' => 'Име']);
        $this->addField('course', ['enum' => self::COURSES, 'required' => true, 'caption' => 'Курс']);
        $this->addField('semester', ['enum' => self::SEMESTERS, 'required' => true, 'caption' => 'Семестър']);
        $this->addField('teacher_id', ['type' => 'integer', 'caption' => 'Преподавател']);
        
        /*
         * Default conditions
         */
        $this->setOrder('course', 'asc');
        
        /**
         * Relations
         */
        $this->hasMany('Lectures', ['model' => new Lecture(), 'their_field' => 'discipline_id']);
        $this->hasMany('Specialties', ['model' => new DisciplineSpecialty(), 'their_field' => 'discipline_id']);
    }
}
