<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\Forms\{CreateTeacherPage, UpdateTeacher, PasswordChangePage};

use Atk4\Ui\{Grid, Header, Menu, JsModal};

use Illuminate\Http\Request;

class TeacherController extends AtkController
{
    public function index(Request $request)
    { 
        $this->atk->add([Header::class, 'Преподаватели', 'icon' => Teacher::ICON, 'subHeader' => 'Създай и управлявай преподаватели']);
        
        $model = new Teacher($this->atkDb);
        
        $grid = $this->atk->add([Grid::class, 'ipp' => 10]);
        $grid->setModel($model, ['title', 'name', 'fname', 'sname', 'active']);
        $grid->addQuickSearch(['name', 'fname', 'sname', 'active', 'title']);
        
        $teacherPage = new CreateTeacherPage($model, $request);

        $this->atk->add($teacherPage);

        $modal = new JsModal("Добави $model->caption", $teacherPage->getURL('cut'));
        $modal->setOption('modalCss', 'tiny');

        $grid->menu->addItem(["Добави $model->caption", 'icon' => 'plus'], $modal);

        $grid->addActionButton(['icon' => 'violet edit'], function ($js, $id) {
            
            return $this->atk->jsRedirect(route('teacher.edit', ['teacher' => $id]));
        });
        
        $passwordPage = new PasswordChangePage($request, clone $model);

        $this->atk->add($passwordPage);

        $passwordModal = new JsModal('Промяна на потребителска парола', $passwordPage->getURL('cut'), ['id' => $grid->JsRow()->data('id')]);
        $passwordModal->setOption('modalCss', 'mini');
        
        $grid->addActionButton(['icon' => 'green unlock'], $passwordModal);

        return response($this->atk->run());
    }
    
    public function edit(Request $request)
    {
        $this->initLocalMenu($request);
        
        $model = new Teacher($this->atkDb);
        
        $form = new UpdateTeacher($request);

        $this->atk->add($form);

        $form->setModel($model, []);

        return response($this->atk->run());
    }
    
    private function initLocalMenu(Request $request)
    {
        $model = new Teacher($this->atkDb);
        $model->tryLoad($request->teacher);
        
        if (!$model->loaded()) {
            abort(404);
        }

        $lm = $this->atk->add([Menu::class]);
        $lm->add([Header::class, $model->get('title') . ' ' . $model->get('name') . ' ' . $model->get('sname'), 'icon' => Teacher::ICON]);

        $rm = $lm->addMenuRight();
        $rm->addItem(['Обратно към списъка', 'icon' => 'arrow left'], [route('teacher.index')]);
    }
}