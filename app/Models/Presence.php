<?php

namespace App\Models;

use Atk4\Data\Model;

class Presence extends Model
{
    public $table = 'presences';
    public $caption = 'Присъствие';

    const ICON = 'list alternate outline';

    protected function init(): void
    {
        parent::init();

        /*
         * Fields
         */
        $this->addField('name', ['type' => 'boolean']);
        $this->addField('date', ['type' => 'date']);
        $this->addField('discipline_id', ['type' => 'integer']);
        $this->addField('teacher_id', ['type' => 'integer']);
        $this->addField('student_id', ['type' => 'integer']);
        $this->addField('created_at', ['type' => 'datetime', 'default' => date('Y-m-d H:i:s')]);
        
        /*
         * Default conditions
         */
        $this->setOrder('date');
    }
}
