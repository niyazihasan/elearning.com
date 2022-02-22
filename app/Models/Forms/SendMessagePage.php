<?php

namespace App\Models\Forms;

use App\Models\{AbstractUser, Message};

use Atk4\Ui\{Form, View, VirtualPage};

class SendMessagePage extends VirtualPage 
{
    public $model = null;
    
    public function __construct(Message $model) 
    {
        parent::__construct();
        
        $this->model = $model;
    }
    
    protected function init(): void 
    {
        parent::init();
        
        $this->set(function ($vp) {
            
            $form = Form::addTo($vp);
            $form->setModel($this->model, []);
            $form->buttonSave->set('Изпрати');
            
            $form->addControl('recipient_id', [
                Form\Control\Dropdown::class,
                'renderRowFunction' => function (AbstractUser $row) {
                    return [
                        'value' => $row->getId(),
                        'title' =>  $row->get('title') . ' ' . $row->get('name') . ' ' . $row->get('sname'),
                    ];
                }
            ]);

            $form->addControl('subject');
            $form->addControl('message', [Form\Control\Textarea::class]);

            View::addTo($form->layout, ['ui' => 'hidden divider']);
            
            /**
             * onSubmit handler
             */
            $form->onSubmit(function (Form $form) {
                
                $form->model->save();
                
                return $form->getApp()->jsRedirect(route('message.outbox'));
            });
        });
    }
}
