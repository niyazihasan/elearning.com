<?php

namespace App\Http\Controllers;

use App\Models\Discipline;
use App\Models\Forms\DisciplinePage;

use Atk4\Ui\{Grid, Header, JsReload, JsModal};

use Illuminate\Http\Request;

class DisciplineController extends AtkController
{
    public function index(Request $request)
    {
        $this->atk->add([Header::class, 'Предмети', 'icon' => Discipline::ICON, 'subHeader' => 'Създай и управлявай предмети']);

        $model = new Discipline($this->atkDb);
        
        $grid = $this->atk->add([Grid::class, 'ipp' => 10]);
        $grid->setModel(clone $model, ['name', 'course', 'semester']);
        $grid->addQuickSearch(['name', 'course', 'semester']);

        $vp = new DisciplinePage($model, $request, $grid);

        $this->atk->add($vp);

        $modal = new JsModal("Добави $model->caption", $vp->getURL('cut'));
        $modal->setOption('modalCss', 'mini');

        $grid->menu->addItem(["Добави $model->caption", 'icon' => 'plus'], $modal);

        $grid->addActionButton(['icon' => 'violet edit'], function ($js, $id) use ($model, $vp) {
            
            $modal = new JsModal("Редактирай $model->caption", $vp->getURL('cut'), ['discipline_id' => $id]);
            $modal->setOption('modalCss', 'mini');

            return $modal;
        });

        $grid->addActionButton(['icon' => 'red trash'], function ($js, $id) use ($model, $grid) {
            
            $model->load($id)->delete();

            return new JsReload($grid);

        }, "Сигурен ли си за изтриването на $model->caption?");

        return response($this->atk->run());
    }
}