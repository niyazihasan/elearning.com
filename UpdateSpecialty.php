<?php

namespace App\Models\Forms;

use App\Models\DisciplineSpecialty;

use Atk4\Ui\Form\Layout\Section\Columns;
use Atk4\Ui\{Grid, Button, View, Header, Form, JsNotify, JsReload, JsModal};

use Illuminate\Http\Request;

class UpdateSpecialty extends Form
{
    private $request = null;

    public function __construct(Request $request)
    {
        parent::__construct();

        $this->request = $request;
    }

    public function setModel(\Atk4\Data\Model $specialty, $fields = [])
    {
        parent::setModel($specialty, []);

        $specialty->load($this->request->specialty);
        
        View::addTo($this->layout, ['ui' => 'hidden divider']);
        
        $cols = $this->layout->addSubLayout([Columns::class]);
        
        /**
         * Column one
         */
        $c1 = $cols->addColumn(6);
        $c1->add([Header::class, "Редактирай $specialty->caption"]);
        
        $c1->addControl('name');
        
        /**
         * Column two
         */
        $c2 = $cols->addColumn(10);
        $c2->add([Header::class, 'Предмети']);
        
        $disciplines = $specialty->ref('Disciplines')->ref('discipline_id');
       
        $grid = $c2->add([Grid::class, 'ipp' => 10]);
        $grid->setModel($disciplines, ['name', 'course', 'semester']);
        
        $disciplinePage = new AssignDisciplineSpecialtyPage($specialty, $grid);

        $this->add($disciplinePage);
        
        $modal = new JsModal("Добави Предмет", $disciplinePage->getURL('cut'));
        
        $grid->menu->addItem(["Добави Предмет", 'icon' => 'plus'], $modal);
        
        $grid->addActionButton(['icon' => 'unlink'], function ($js, $id) use ($disciplines, $grid) {
            
            $disciplineSpecialty = new DisciplineSpecialty($disciplines->persistence);
            $disciplineSpecialty->addCondition('discipline_id', $id)
                                ->addCondition('specialty_id', $this->request->specialty)
                                ->tryLoadAny();

            if ($disciplineSpecialty->loaded()) {
                $disciplineSpecialty->delete();
            }

            return new JsReload($grid);
            
        }, "Сигурен ли си за премахването на Предмет?");

        /**
         * onSubmit handler
         */
        $this->onSubmit(function (Form $form) {

            $form->model->save();

            return new JsNotify(['content' => 'Specialty is saved!', 'color' => 'green']);
        });
        
        /**
         * Delete btn
         */
        $this->add([Button::class, 'Delete', 'ui' => 'negative button', 'style' => ['margin-left' => '1em']])->on('click', function ($btn) use ($specialty) {

            $specialty->delete();

            return $this->getApp()->jsRedirect(route('specialty.index'));
            
        }, ['confirm' => "Сигурен ли си за изтриването на $specialty->caption?"]);

        return $this->model;
    }
}