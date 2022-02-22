<?php

namespace App\Traits;

use Atk4\Data\Model;

trait EncryptableTrait 
{
    private $encryptable = [];
    
    private function initEncrypt(array $fields)
    {
        $this->encryptable = $fields;
        
        parent::onHook(Model::HOOK_BEFORE_SAVE, \Closure::fromCallable([$this, 'encrypt']), [], 100);
        
        parent::onHook(Model::HOOK_AFTER_LOAD, \Closure::fromCallable([$this, 'decrypt']), [], 100);
 
    }
    
    private function encrypt()
    {
        $this->func('encrypt');
    }

    private function decrypt() 
    {
        $this->func('decrypt');
    }

    private function func(string $type) 
    {
        foreach ($this->encryptable as $field) {
            if (array_key_exists($field, $this->data)) $this->data[$field] = $type($this->data[$field]);
        }
    }

}
