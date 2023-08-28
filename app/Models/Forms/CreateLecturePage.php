<?php

namespace App\Models\Forms;

use App\Models\Lecture;

use Atk4\Ui\{Form, View, VirtualPage};

class CreateLecturePage extends VirtualPage
{
    public $model = null;

    public function __construct(Lecture $model)
    {
        parent::__construct();

        $this->model = $model;
    }

    protected function init(): void
    {
        parent::init();

        $form = Form::addTo($this);
        $form->setModel($this->model, ['name']);
        $form->buttonSave->set('Next');

        View::addTo($form->layout, ['ui' => 'hidden divider']);

        /**
         * onSubmit handler
         */
        $form->onSubmit(function (Form $form) {

            $form->model->save();

            return $form->getApp()->jsRedirect(route('lecture.edit', ['lecture' => $form->model->getId()]));
        });
    }
}