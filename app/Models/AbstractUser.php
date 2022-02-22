<?php

namespace App\Models;

use Atk4\Data\Model;

class AbstractUser extends Model
{
    use \App\Traits\AuditTrait;

    public $table = 'users';

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
        $this->addField('fname', ['type' => 'string', 'required' => true, 'caption' => 'Презиме']);
        $this->addField('sname', ['type' => 'string', 'required' => true, 'caption' => 'Фамилия']);
        $this->addField('login_type', ['enum' => User::LOGIN_TYPES, 'required' => true]);
        $this->addField('email', ['type' => 'email', 'required' => true]);
        $this->addField('password', ['type' => 'password', 'required' => true, 'caption' => 'Парола']);
        $this->addField('active', ['type' => 'boolean', 'caption' => 'Активност']);
        $this->addField('title', ['type' => 'string', 'caption' => 'Титла']);
        $this->addField('group_id', ['type' => 'integer']);
        
        /*
         * Default conditions
         */
        $this->setOrder('name');

        /**
         * Relations
         */
        $message = new Message($this->persistence);
        $message->addCondition('read_at', null);

        $this->hasMany('UnReadMessages', ['model' => $message, 'their_field' => 'recipient_id']);
    }

    public function checkPasswordStrength($password)
    {
        return preg_match('/(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,8}$/', $password);
    }

}
