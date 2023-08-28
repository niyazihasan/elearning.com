<?php

namespace App\Models\Forms;

use App\Models\Student;

use Atk4\Ui\{Form, Grid, JsNotify, JsReload, JsExpression, View, VirtualPage};

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StudentPage extends VirtualPage
{
    public $model = null;
    private $grid = null;
    private $request = null;

    public function __construct(Student $model, Request $request, Grid $grid)
    {
        parent::__construct();

        $this->model = $model;
        $this->grid = $grid;
        $this->request = $request;
    }

    protected function init(): void
    {
        parent::init();

        if ($this->request->has('student_id')) {
            $this->model->tryLoad($this->stickyGet('student_id'));
        }

        $form = Form::addTo($this);
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
        $form->onSubmit(function (Form $form) {

            $rules = "required|email|unique:users,email";

            if ($form->model->loaded()) {
                $rules .= ",{$form->model->getId()}";
            }

            $validator = Validator::make($this->request->all(), ['email' => $rules]);

            if ($validator->fails()) {
                return $form->error('email', $validator->errors()->first('email'));
            }

            $form->model->save();

            return [
                new JsReload($this->grid),
                new JsNotify(['content' => 'Student is saved!']),
                new JsExpression('$(".atk-dialog-content").trigger("close")')
            ];
        });
    }
}