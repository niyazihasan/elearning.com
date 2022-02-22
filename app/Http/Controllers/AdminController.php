<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Forms\{AdminPage, PasswordChangePage};

use Atk4\Ui\{Grid, Header, JsReload, JsModal};

use Illuminate\Http\Request;

class AdminController extends AtkController
{
    public function index(Request $request)
    {
        $this->atk->add([Header::class, 'Администратори', 'icon' => Admin::ICON, 'subHeader' => 'Създай и управлявай администратори']);

        $model = new Admin($this->atkDb);

        $grid = $this->atk->add([Grid::class, 'ipp' => 10]);
        $grid->setModel(clone $model, ['name', 'fname', 'sname', 'active']);
        $grid->addQuickSearch(['name', 'fname', 'sname', 'active']);

        $adminPage = new AdminPage($model, $request, $grid);

        $this->atk->add($adminPage);

        $formModal = new JsModal("Добави $model->caption", $adminPage->getURL('cut'));
        $formModal->setOption('modalCss', 'tiny');

        $grid->menu->addItem(["Добави $model->caption", 'icon' => 'plus'], $formModal);

        $grid->addActionButton(['icon' => 'violet edit'], function ($js, $id) use ($model, $adminPage) {

            $modal = new JsModal("Редактирай $model->caption", $adminPage->getURL('cut'), ['admin_id' => $id]);
            $modal->setOption('modalCss', 'tiny');

            return $modal;
        });

        $passwordPage = new PasswordChangePage($request, $model);

        $this->atk->add($passwordPage);

        $passwordModal = new JsModal('Промяна на потребителска парола', $passwordPage->getURL('cut'), ['id' => $grid->JsRow()->data('id')]);
        $passwordModal->setOption('modalCss', 'mini');

        $grid->addActionButton(['icon' => 'green unlock'], $passwordModal);

        $grid->addActionButton(['icon' => 'red trash'], function ($js, $id) use ($model, $grid) {
            
            $model->load($id)->delete();

            return new JsReload($grid);
            
        }, "Сигурен ли си за изтриването на $model->caption?");

        return response($this->atk->run());
    }
}