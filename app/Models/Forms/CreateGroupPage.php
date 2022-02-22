<?php

namespace App\Models\Forms;

use App\Models\Group;

use Atk4\Ui\{Form, View, VirtualPage};

class CreateGroupPage extends VirtualPage 
{
    public $model = null;
    
    public function __construct(Group $model) 
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
            $form->buttonSave->set('Next');
            
            $form->addControl('specialty_id');
            
            $group1 = $form->addGroup(['width' => 'two']);
            $group1->addControls([['name'], ['course']]);
            
            $group2 = $form->addGroup(['width' => 'two']);
            $group2->addControls([
                ['start_date', ['iconLeft' => 'calendar alternate outline icon']],
                ['end_date', ['iconLeft' => 'calendar alternate outline']]
            ]);

            View::addTo($form->layout, ['ui' => 'hidden divider']);
            
            /**
             * onSubmit handler
             */
            $form->onSubmit(function (Form $form) {

                $form->model->save();

                return $form->getApp()->jsRedirect(route('group.edit', ['group' => $form->model->get('id')]));
            });
        });
    }
}
