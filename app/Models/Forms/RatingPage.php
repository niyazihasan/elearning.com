<?php

namespace App\Models\Forms;

use App\Models\Task;

use Atk4\Ui\{Form, View, Grid, JsExpression, JsReload, JsToast, VirtualPage};

use Illuminate\Http\Request;

class RatingPage extends VirtualPage 
{
    public $model = null;
    private $grid = null;
    private $request = null;
    
    public function __construct(Request $request, Task $model, Grid $tasks) 
    {
        parent::__construct();

        $this->model = $model;
        $this->grid = $tasks;
        $this->request = $request;
    }
    
    protected function init(): void 
    {
        parent::init();
        
        $request = $this->request;
        
        $this->set(function ($vp) use ($request) {

            if ($request->has('task_id')) {
                $this->model->tryLoad($this->stickyGet('task_id'));
            }

            $form = Form::addTo($vp);
            $form->setModel($this->model, ['rating']);

            View::addTo($form->layout, ['ui' => 'hidden divider']);
            
            /**
             * onSubmit handler
             */
            $form->onSubmit(function (Form $form) {

                $form->model->save();

                return [new JsReload($this->grid), [new JsToast('Rating is saved!'), new JsExpression('$(".atk-dialog-content").trigger("close")')]];
            });
        });
    }
}