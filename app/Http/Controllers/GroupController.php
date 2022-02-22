<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Forms\{CreateGroupPage, UpdateGroup};

use Atk4\Ui\{Grid, Menu, Header, JsModal};

use Illuminate\Http\Request;

class GroupController extends AtkController
{
    public function index()
    {
        $this->atk->add([Header::class, 'Групи', 'icon' => Group::ICON, 'subHeader' => 'Създай и управлявай групи']);

        $model = new Group($this->atkDb);

        $grid = $this->atk->add([Grid::class, 'ipp' => 10]);
        $grid->setModel($model, ['name']);
        $grid->addColumn('specialty_name', ['caption' => 'Специалност']);
        $grid->addColumn('course');
        $grid->addColumn('count_students');
        $grid->addColumn('start_date');
        $grid->addColumn('end_date');
        $grid->addQuickSearch(['name', 'specialty_name', 'course']);

        $vp = new CreateGroupPage($model);

        $this->atk->add($vp);

        $modal = new JsModal("Добави $model->caption", $vp->getURL('cut'));
        $modal->setOption('modalCss', 'mini');

        $grid->menu->addItem(["Добави $model->caption", 'icon' => 'plus'], $modal);
        
        $grid->addActionButton(['icon' => 'violet edit'], function ($js, $id) {
            
            return $this->atk->jsRedirect(route('group.edit', ['group' => $id]));
        });
        
        return response($this->atk->run());
    }
    
    public function edit(Request $request)
    {
        $this->initLocalMenu($request);
        
        $model = new Group($this->atkDb);
        
        $form = new UpdateGroup($request);

        $this->atk->add($form);

        $form->setModel($model, []);

        return response($this->atk->run());
    }
    
    private function initLocalMenu(Request $request)
    {
        $model = new Group($this->atkDb);
        $model->tryLoad($request->group);
        
        if (!$model->loaded()) {
            abort(404);
        }

        $lm = $this->atk->add([Menu::class]);
        $lm->add([
            Header::class, $model->get('name'), 'icon' => Group::ICON,
            'subHeader' => 'Учебна година: ' . $model->get('start_date')->format("M d, Y") . '/' .$model->get('end_date')->format("M d, Y")
        ]);

        $rm = $lm->addMenuRight();
        $rm->addItem(['Обратно към списъка', 'icon' => 'arrow left'], [route('group.index')]);
    }
}