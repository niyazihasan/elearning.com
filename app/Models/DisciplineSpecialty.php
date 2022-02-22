<?php

namespace App\Models;

use Atk4\Data\Model;

class DisciplineSpecialty extends Model 
{
    public $table = 'disciplines_specialties';

    protected function init(): void 
    {
        parent::init();
        
        /**
         * Relations
         */
        $this->hasOne('discipline_id', ['model' => new Discipline()]);
        $this->hasOne('specialty_id', ['model' => new Specialty()]);
    }
}
