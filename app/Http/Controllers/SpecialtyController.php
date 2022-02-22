<?php

namespace App\Http\Controllers;

use App\Models\Specialty;
use App\Models\Forms\{CreateSpecialtyPage, UpdateSpecialty};

use Atk4\Ui\{Grid, Menu, Header, JsModal};

use Illuminate\Http\Request;

class SpecialtyController extends AtkController
{
    public function index()
    { 
        $this->atk->add([Header::class, 'Специалности', 'icon' => Specialty::ICON, 'subHeader' => 'Създай и управлявай специалности']);
        
        $model = new Specialty($this->atkDb);
        $model->getField('created_at')->type = 'date';
        $model->getField('updated_at')->type = 'date';
        
        $grid = $this->atk->add([Grid::class, 'ipp' => 10]);
        $grid->setModel($model, ['name', 'created_at', 'updated_at']);
        $grid->addQuickSearch(['name']);
        
        $vp = new CreateSpecialtyPage($model);

        $this->atk->add($vp);

        $modal = new JsModal("Добави $model->caption", $vp->getURL('cut'));
        $modal->setOption('modalCss', 'mini');

        $grid->menu->addItem(["Добави $model->caption", 'icon' => 'plus'], $modal);

        $grid->addActionButton(['icon' => 'violet edit'], function ($js, $id) {
            
            return $this->atk->jsRedirect(route('specialty.edit', ['specialty' => $id]));
        });
        
        return response($this->atk->run());
    }
    
    public function edit(Request $request)
    {
        $this->initLocalMenu($request);
        
        $model = new Specialty($this->atkDb);
        
        $form = new UpdateSpecialty($request);

        $this->atk->add($form);

        $form->setModel($model, []);

        return response($this->atk->run());
    }
    
    private function initLocalMenu(Request $request)
    {
        $model = new Specialty($this->atkDb);
        $model->tryLoad($request->specialty);
        
        if (!$model->loaded()) {
            abort(404);
        }

        $lm = $this->atk->add([Menu::class]);
        $lm->add([Header::class, $model->get('name'), 'icon' => Specialty::ICON]);

        $rm = $lm->addMenuRight();
        $rm->addItem(['Обратно към списъка', 'icon' => 'arrow left'], [route('specialty.index')]);
    }
}