<?php

namespace App\Models;

use Atk4\Data\Model;

class Specialty extends Model 
{
    use \App\Traits\AuditTrait;
    
    public $table = 'specialties';
    public $caption = 'Специалност';
    
    const ICON = 'list';
    
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
        
        /**
         * Relations
         */
        $this->hasMany('Disciplines', ['model' => new DisciplineSpecialty(), 'their_field' => 'specialty_id']);
        $this->hasMany('Groups', ['model' => new Group(), 'their_field' => 'specialty_id']);
    }
}
