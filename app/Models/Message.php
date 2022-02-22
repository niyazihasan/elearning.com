<?php

namespace App\Models;

use Atk4\Data\Model;

class Message extends Model 
{
    public $table = 'messages';
    public $caption = 'Съобщение';

    protected function init(): void 
    {
        parent::init();

        /*
         * Fields
         */
        $this->addField('message', ['type' => 'text', 'caption' => $this->caption, 'required' => true]);
        $this->addField('subject', ['type' => 'string', 'caption' => 'Тема']);
        $this->addField('read_at', ['type' => 'datetime']);
        $this->addField('created_at', ['type' => 'datetime', 'default' => date('Y-m-d H:i:s'), 'caption' => 'Дата']);
        $this->addField('deleted_by_recipient', ['type' => 'boolean']);
        $this->addField('deleted_by_sender', ['type' => 'boolean']);
        
        /**
         * Relations
         */
        $this->hasOne('sender_id', ['model' => new AbstractUser(), 'default' => auth()->user()->id]);
        $this->hasOne('recipient_id', ['model' => new AbstractUser(), 'required' => true, 'caption' => 'До']);
        
         /*
         * Default conditions
         */
        $this->setOrder('created_at', 'desc');
    }
    
    public function markAsRead()
    {
        if (is_null($this->get('read_at')) && $this->get('recipient_id') == auth()->user()->id) {
            $this->save(['read_at' => date('Y-m-d H:i:s')]);
        }
    }
    
    public function markAsDelete($deleted_by) 
    {
        $this->save([$deleted_by => 1]);
    }
}
