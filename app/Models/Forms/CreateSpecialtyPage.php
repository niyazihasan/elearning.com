<?php

namespace App\Models\Forms;

use App\Models\Specialty;
use Atk4\Ui\{Form, View, VirtualPage};

class CreateSpecialtyPage extends VirtualPage 
{
    public $model = null;
    
    public function __construct(Specialty $model) 
    {
        parent::__construct();
        
        $this->model = $model;
    }

    protected function init(): void 
    {
        parent::init();

        $this->set(function ($vp) {
            
            $form = Form::addTo($vp);
            $form->setModel($this->model, ['name']);
            $form->buttonSave->set('Next');

            View::addTo($form->layout, ['ui' => 'hidden divider']);
            
            /**
             * onSubmit handler
             */
            $form->onSubmit(function (Form $form) {
                
                $form->model->save();

                return $form->getApp()->jsRedirect(route('specialty.edit', ['specialty' => $form->model->get('id')]));
            });
        });
    }
}