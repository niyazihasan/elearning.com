<?php

namespace App\Models;

use Atk4\Data\Model;

class Lecture extends Model 
{
    use \App\Traits\AuditTrait;
    
    public $table = 'lectures';
    public $caption = 'Лекция';
    
    const ICON = 'folder outline';

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
        $this->addField('name', ['type' => 'string', 'required' => true, 'caption' => 'Тема']);
        $this->addField('description', ['type' => 'text', 'caption' => 'Описание']);
        $this->addField('discipline_id', ['type' => 'integer', 'required' => true, 'caption' => 'Предмет']);
        
        /**
         * Relations
         */
        $this->hasOne('Discipline', ['model' => new Discipline(), 'our_field' => 'discipline_id']);
        
        $document = new Document($this->persistence);
        $document->addCondition('downloadable_type', get_class($this));
        
        $this->hasMany('Documents', ['model' => $document, 'their_field' => 'downloadable_id']);
    }
}
