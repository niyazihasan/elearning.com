<?php

namespace App\Models\Forms;

use App\Models\Admin;

use Atk4\Ui\{Form, Grid, JsNotify, JsReload, JsExpression, View, VirtualPage};

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminPage extends VirtualPage
{
    public $model = null;
    private $request = null;
    private $grid = null;

    public function __construct(Admin $model, Request $request, Grid $grid)
    {
        parent::__construct();

        $this->model = $model;
        $this->request = $request;
        $this->grid = $grid;
    }

    protected function init(): void
    {
        parent::init();

        $request = $this->request;

        $this->set(function ($vp) use ($request) {
            
            if ($request->has('admin_id')) {
                $this->model->tryLoad($this->stickyGet('admin_id'));
            }
            
            $form = Form::addTo($vp);
            $form->setModel($this->model, []);

            $group1 = $form->addGroup(['width' => 'two']);
            $group1->addControls([['name'], ['fname']]);

            $group2 = $form->addGroup(['width' => 'two']);
            $group2->addControls([['sname'], ['email']]);

            $form->addControl('active');

            View::addTo($form->layout, ['ui' => 'hidden divider']);
            
            /**
             * onSubmit handler
             */
            $form->onSubmit(function (Form $form) use ($request) {
                
                $rules = "required|email|unique:users,email";

                if ($form->model->loaded()) {
                    $rules .= ",{$form->model->get('id')}";
                }

                $validator = Validator::make($request->all(), ['email' => $rules]);

                if ($validator->fails()) {
                    return $form->error('email', $validator->errors()->first('email'));
                }

                $form->model->save();

                return [new JsReload($this->grid), [new JsNotify(['content' => 'Admin is saved!']), new JsExpression('$(".atk-dialog-content").trigger("close")')]];
            });
        });
    }
}