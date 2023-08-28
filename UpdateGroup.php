<?php

namespace App\Models\Forms;

use App\Models\Student;

use Atk4\Ui\Form\Layout\Section\Columns;
use Atk4\Ui\{Grid, View, Button, Header, Form, JsNotify, JsReload, JsModal};
use Atk4\Ui\Form\Control\Dropdown;

use Illuminate\Http\Request;

class UpdateGroup extends Form
{
    private $request = null;

    public function __construct(Request $request)
    {
        parent::__construct();

        $this->request = $request;
    }

    public function setModel(\Atk4\Data\Model $group, $fields = [])
    {
        parent::setModel($group, []);

        $group->load($this->request->group);
        
        View::addTo($this->layout, ['ui' => 'hidden divider']);
        
        $cols = $this->layout->addSubLayout([Columns::class]);

        /**
         * Column one
         */
        $c1 = $cols->addColumn(6);
        $c1->add([Header::class, "Редактирай $group->caption"]);

        $c1->addControl('specialty_id');
        $c1->addControl('name');
        $c1->addControl('course');
        $c1->addControl('start_date');
        $c1->addControl('end_date');

        /**
         * Column two
         */
        $c2 = $cols->addColumn(10);
        $c2->add([Header::class, 'Студенти']);

        $students = $group->ref('Students');

        $grid = $c2->add([Grid::class, 'ipp' => 10]);
        $grid->setModel(clone $students, ['name', 'fname', 'sname', 'active']);

        $studentPage = new StudentPage($students, $this->request, $grid);

        $this->add($studentPage);

        $modal = new JsModal("Добави $students->caption", $studentPage->getURL('cut'));
        $modal->setOption('modalCss', 'tiny');

        $grid->menu->addItem(["Добави $students->caption", 'icon' => 'plus'], $modal);

        $grid->addActionButton(['icon' => 'violet edit'], function ($js, $id) use ($students, $studentPage) {
            
            $modal = new JsModal("Редактирай $students->caption", $studentPage->getURL('cut'), ['student_id' => $id]);
            $modal->setOption('modalCss', 'tiny');

            return $modal;
        });

        $passwordPage = new PasswordChangePage($this->request, $students);

        $this->add($passwordPage);

        $passwordModal = new JsModal('Промяна на потребителска парола', $passwordPage->getURL('cut'), ['id' => $grid->JsRow()->data('id')]);
        $passwordModal->setOption('modalCss', 'mini');

        $grid->addActionButton(['icon' => 'green unlock'], $passwordModal);

        $grid->addActionButton(['icon' => 'red trash'], function ($js, $id) use ($students, $grid) {
            
            $students->load($id)->delete();

            return new JsReload($grid);

        }, "Сигурен ли си за изтриването на $students->caption?");

        /**
         * onSubmit handler
         */
        $this->onSubmit(function (Form $form) {

            $form->model->save();

            return new JsNotify(['content' => 'Group is saved!', 'color' => 'green']);
        });

        /**
         * Delete btn
         */
        $this->add([Button::class, 'Delete', 'ui' => 'negative button', 'style' => ['margin-left' => '1em']])->on('click', function ($btn) use ($group) {

            $group->delete();

            return $this->getApp()->jsRedirect(route('group.index'));
            
        }, ['confirm' => "Сигурен ли си за изтриването на $group->caption?"]);

        return $this->model;
    }
}