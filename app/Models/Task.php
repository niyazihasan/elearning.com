<?php

namespace App\Models;

use Atk4\Data\Model;

class Task extends Model
{
    public $table = 'tasks';

    public $caption = 'Задача';

    const ICON = 'tasks';
    
    const TYPE_COURSE_WORK = 'course-work';
    const TYPE_HOME_WORK = 'home-work';
    
    const TYPES = [
        self::TYPE_COURSE_WORK => 'Курсова работа',
        self::TYPE_HOME_WORK => 'Домашна работа'
    ];
     
    protected function init(): void
    {
        parent::init();

        $this->addField('name', ['type' => 'text', 'caption' => 'Тема', 'required' => true]);
        $this->addField('solution_upload_period', ['type' => 'datetime', 'caption' => 'Срок за предаване',]);
        $this->addField('type', ['enum' => array_keys(self::TYPES), 'caption' => 'Тип', 'required' => true]);
        $this->addField('discipline_id', ['type' => 'integer']);
        $this->addField('teacher_id', ['type' => 'integer']);
        $this->addField('student_id', ['type' => 'integer']);
        
        $this->addField('rating', [
            'type' => 'float',
            'caption' => 'Оценка',
            'ui' => [
                'table' => [
                    \Atk4\Ui\Table\Column\ColorRating::class, [
                        'min' => 2,
                        'max' => 6,
                        'steps' => 1,
                        'colors' => ['#FF0000', '#FFFF00', '#00FF00']]
                ]
            ]
        ]);

        $this->addField('created_at', ['type' => 'datetime', 'default' => date('Y-m-d H:i:s'), 'caption' => 'Дата на създаване']);
        
        /**
         * Never-persist Fields
         */
        $this->addField('type_name', ['never_persist' => true, 'caption' => 'Тип']);

        /**
         * Hooks
         */
        $this->onHook(Model::HOOK_AFTER_LOAD, function ($m) {
            $m->set('type_name', self::TYPES[$m->get('type')]);
        });

        /**
         * Relations
         */
        $docAssigment = new Document($this->persistence);
        $docAssigment->addCondition('downloadable_type', get_class($this));
        $docAssigment->addCondition('document_type', $docAssigment::DOCUMENT_TYPE_TASK);
        
        $docSolution = new Document($this->persistence);
        $docSolution->addCondition('downloadable_type', get_class($this));
        $docSolution->addCondition('document_type', $docSolution::DOCUMENT_TYPE_SOLUTION);
        
        $this->hasOne('assignment_id', ['model' => $docAssigment]);
        $this->hasOne('solution_id', ['model' => $docSolution]);
        $this->hasOne('Discipline', ['model' => new Discipline(), 'our_field' => 'discipline_id']);
    }
}
