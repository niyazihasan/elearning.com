<?php

namespace App\Models\Forms;

use App\Models\Discipline;

use Atk4\Ui\{Form, Grid, JsNotify, JsReload, JsExpression, View, VirtualPage};

use Illuminate\Http\Request;

class DisciplinePage extends VirtualPage
{
    public $model = null;
    private $request = null;
    private $grid = null;

    public function __construct(Discipline $model, Request $request, Grid $grid)
    {
        parent::__construct();

        $this->model = $model;
        $this->request = $request;
        $this->grid = $grid;
    }

    protected function init(): void
    {
        parent::init();

        if ($this->request->has('discipline_id')) {
            $this->model->tryLoad($this->stickyGet('discipline_id'));
        }

        $form = Form::addTo($this);
        $form->setModel($this->model, []);

        $form->addControl('name');

        $group = $form->addGroup(['width' => 'two']);
        $group->addControls([['course'], ['semester']]);

        View::addTo($form->layout, ['ui' => 'hidden divider']);

        /**
         * onSubmit handler
         */
        $form->onSubmit(function (Form $form) {

            $form->model->save();

            return [
                new JsReload($this->grid),
                new JsNotify(['content' => 'Discipline is saved!']),
                new JsExpression('$(".atk-dialog-content").trigger("close")')
            ];
        });
    }
}