<?php

namespace App\Models;

class Admin extends AbstractUser 
{
    public $caption = 'Администратор';
    
    const ICON = 'user secret';

    protected function init(): void 
    {
        parent::init();
      
        /*
         * Default conditions
         */
        $this->addCondition('login_type', User::LOGIN_TYPE_ADMIN);
    }

}
