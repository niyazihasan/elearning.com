<?php

namespace App\Http\Controllers;

use App\Models\AbstractUser;
use App\Models\Forms\{UserPasswordChangePage, UpdateUser};

use Atk4\Ui\{Header, View, Menu, JsModal};

use Illuminate\Http\Request;

class UserController extends AtkController
{
    public function profile(Request $request)
    {
        $user = auth()->user();
        
        $lm = $this->atk->add([Menu::class]);
        $lm->add([
            Header::class, "$user->title $user->name $user->sname",
            'icon' => 'user outline',
            'subHeader' => "Login type: $user->login_type"
        ]);

        $rm = $lm->addMenuRight();
        
        $model = new AbstractUser($this->atkDb);
        $model->load($user->id);
                
        $pwdPage = new UserPasswordChangePage($request, $model);
        $this->atk->add($pwdPage);

        $pwdModal = new JsModal('Промяна на парола', $pwdPage->getURL('cut'));
        $pwdModal->setOption('modalCss', 'mini');

        $rm->addItem(['Промяна на парола', 'icon' => 'red unlock'], $pwdModal);
        
        View::addTo($this->atk, ['ui' => 'hidden divider']);
        
        $form = new UpdateUser($request);

        $this->atk->add($form);

        $form->setModel($model);

        return response($this->atk->run());
    }
}