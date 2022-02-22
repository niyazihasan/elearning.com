<?php

namespace App\Traits;

trait AuditTrait 
{
    public function initAudit()
    {
        parent::addField('created_at', ['type' => 'datetime', 'default' => date('Y-m-d H:i:s'), 'caption' => 'Дата на добавяне']);
        parent::addField('updated_at', ['type'=>'datetime', 'caption' => 'Дата на редактиране']);

        parent::onHook(\Atk4\Data\Model::HOOK_BEFORE_UPDATE, function ($m, &$data) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        });
    }
}
