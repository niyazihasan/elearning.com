<?php

namespace App\Models\Forms;

use App\Models\{Discipline, DisciplineSpecialty};

use Atk4\Data\Model;
use Atk4\Ui\{Grid, JsReload, JsNotify, VirtualPage};

class AssignDisciplineSpecialtyPage extends VirtualPage
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

            $grid->addActionButton(['icon' => 'plus'], function ($js, $id) {
                
                $disciplineSpecialty = new DisciplineSpecialty($this->model->persistence);

                $disciplineSpecialty->addCondition('specialty_id', $this->model->getId())
                                    ->addCondition('discipline_id', $id)
                                    ->tryLoadAny();

                if (!$disciplineSpecialty->loaded()) {

                    $disciplineSpecialty->save();

                    return new JsReload($this->grid);
                }

                return new JsNotify(['content' => 'Предмета е добавен за тази специалност!', 'color' => 'red']);
            });
        });
    }
}