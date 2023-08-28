<?php

namespace App\Models\Forms;

use App\Models\Teacher;

use Atk4\Ui\{Form, View, VirtualPage};

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CreateTeacherPage extends VirtualPage
{
    public $model = null;
    private $request = null;

    public function __construct(Teacher $model, Request $request)
    {
        parent::__construct();

        $this->model = $model;
        $this->request = $request;
    }

    protected function init(): void
    {
        parent::init();

        $form = Form::addTo($this);
        $form->setModel($this->model, []);
        $form->buttonSave->set('Next');

        $group1 = $form->addGroup(['width' => 'two']);
        $group1->addControls([['name'], ['fname']]);

        $group2 = $form->addGroup(['width' => 'two']);
        $group2->addControls([['sname'], ['title']]);

        $group3 = $form->addGroup(['width' => 'two']);
        $group3->addControls([['email'], ['active', ['style' => ['margin-top' => '1.6em']]]]);

        View::addTo($form->layout, ['ui' => 'hidden divider']);

        /**
         * onSubmit handler
         */
        $form->onSubmit(function (Form $form) {

            $validator = Validator::make($this->request->all(), [
                    'email' => "required|email|unique:users,email"
            ]);

            if ($validator->fails()) {
                return $form->error('email', $validator->errors()->first('email'));
            }

            $form->model->save();

            return $form->getApp()->jsRedirect(route('teacher.edit', ['teacher' => $form->model->getId()]));
        });
    }
}