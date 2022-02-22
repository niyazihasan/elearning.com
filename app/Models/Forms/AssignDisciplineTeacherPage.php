<?php

namespace App\Models\Forms;

use App\Models\Discipline;

use Atk4\Data\Model;
use Atk4\Ui\{Grid, JsReload, JsNotify, VirtualPage};

class AssignDisciplineTeacherPage extends VirtualPage
{
    public $model = null;
    private $grid = null;

    public function __construct(Model $model, Grid $grid)
    {
        parent::__construct();

        $this->model = $model;
        $this->grid = $grid;
    }

    protected function init(): void
    {
        parent::init();

        $this->set(function ($vp) {
            
            $disciplines = new Discipline($this->model->persistence);

            $grid = $vp->add([Grid::class, 'ipp' => 10]);
            $grid->setModel($disciplines, ['name', 'course', 'semester']);
            $grid->addQuickSearch(['name', 'course', 'semester']);

            $grid->addActionButton(['icon' => 'plus'], function ($js, $id) use ($disciplines) {
                
                $disciplines->tryLoad($id);

                if ($disciplines->loaded()) {

                    $notify = new JsNotify();
                    $notify->setColor('red');

                    if ($disciplines->get('teacher_id') && $disciplines->get('teacher_id') != $this->model->getId()) {
                        return $notify->setContent('Предмета се води от друг преподавател!');
                    }

                    if ($disciplines->get('teacher_id') == $this->model->getId()) {
                        return $notify->setContent('Предмета е добавен за този преподавател!');
                    }

                    $disciplines->set('teacher_id', $this->model->getId());
                    $disciplines->save();

                    return new JsReload($this->grid);
                }
            });
        });
    }
}