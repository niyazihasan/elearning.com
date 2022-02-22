<?php

namespace App\Models\Forms;

use Atk4\Ui\Form\Layout\Section\Columns;
use Atk4\Ui\{Grid, Header, View, Button, Form, JsNotify, JsReload, JsModal};

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UpdateTeacher extends Form
{
    private $request = null;

    public function __construct(Request $request)
    {
        parent::__construct();

        $this->request = $request;
    }

    public function setModel(\Atk4\Data\Model $teacher, $fields = [])
    {
        parent::setModel($teacher, []);

        $teacher->load($this->request->teacher);
        
        View::addTo($this->layout, ['ui' => 'hidden divider']);
        
        $cols = $this->layout->addSubLayout([Columns::class]);

        /**
         * Column one
         */
        $c1 = $cols->addColumn(6);
        $c1->add([Header::class, "Редактирай $teacher->caption"]);

        $c1->addControl('name');
        $c1->addControl('fname');
        $c1->addControl('sname');
        $c1->addControl('title');
        $c1->addControl('email');
        $c1->addControl('active');

        /**
         * Column two
         */
        $c2 = $cols->addColumn(10);
        $c2->add([Header::class, 'Предмети']);

        $disciplines = $teacher->ref('Disciplines');

        $grid = $c2->add([Grid::class, 'ipp' => 10]);
        $grid->setModel($disciplines, ['name', 'course', 'semester']);

        $disciplinePage = new AssignDisciplineTeacherPage($teacher, $grid);

        $this->add($disciplinePage);

        $modal = new JsModal("Добави $disciplines->caption", $disciplinePage->getURL('cut'));

        $grid->menu->addItem(["Добави $disciplines->caption", 'icon' => 'plus'], $modal);

        $grid->addActionButton(['icon' => 'unlink'], function ($js, $id) use ($disciplines, $grid) {
            
            $disciplines->load($id)->set('teacher_id', null)->save();

            return new JsReload($grid);

        }, "Сигурен ли си за премахването на $disciplines->caption?");

        /**
         * onSubmit handler
         */
        $this->onSubmit(function (Form $form) {

            $validator = Validator::make($this->request->all(), [
                'email' => "required|email|unique:users,email, {$form->model->get('id')}"
            ]);

            if ($validator->fails()) {
                return $form->error('email', $validator->errors()->first('email'));
            }

            $form->model->save();

            return new JsNotify(['content' => 'Teacher is saved!', 'color' => 'green']);
        });
        
        /**
         * Delete btn
         */
        $this->add([Button::class, 'Delete', 'ui' => 'negative button', 'style' => ['margin-left' => '1em']])->on('click', function ($btn) use ($teacher) {

            $teacher->delete();

            return $this->getApp()->jsRedirect(route('teacher.index'));
            
        }, ['confirm' => "Сигурен ли си за изтриването на $teacher->caption?"]);

        return $this->model;
    }
}