<?php

namespace App\Models;

use Atk4\Data\Model;

class Group extends Model 
{
    use \App\Traits\AuditTrait;
    
    public $table = 'groups';
    public $caption = 'Група';
    
    const ICON = 'users cog';
    const COURSES = ['1', '2', '3', '4'];

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
        $this->addField('start_date', ['type' => 'date', 'required' => true, 'caption' => 'Начало на уч. година']);
        $this->addField('end_date', ['type' => 'date', 'required' => true, 'caption' => 'Край на уч. година']);
        
        /**
         * Never-persist Fields
         */
        $this->addField('count_students', ['never_persist' => true, 'caption' => 'Брой студенти']);
        
        /**
         * Hooks
         */
        $this->onHook(Model::HOOK_AFTER_LOAD, function ($m) {
            $m->set('count_students', $m->ref('Students')->action('count')->getOne());
        });

        /**
         * Relations
         */
        $this->hasOne('specialty_id', ['model' => new Specialty(), 'required' => true, 'caption' => 'Специалност'])->addFields(['specialty_name' => 'name']);
        $this->hasMany('Students', ['model' => new Student(), 'their_field' => 'group_id']);
    }
}
