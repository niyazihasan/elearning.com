<?php

namespace App\Models\Forms;

use Atk4\Data\Model;
use Atk4\Ui\{Form, View, JsExpression, VirtualPage};

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PasswordChangePage extends VirtualPage
{
    public $model = null;
    private $request = null;
    
    public function __construct(Request $request, Model $user)
    {
        parent::__construct();
        
        $this->model = $user;
        $this->request = $request;
    }
    
    protected function init(): void
    {
        parent::init();
        
        $request = $this->request;
        
        $this->set(function ($vp) use ($request) {
            
            if ($request->has('id')) {
                $this->model->tryLoad($this->stickyGet('id'));
            }
            
            $form = $vp->add([Form::class]);
            $form->setModel($this->model, []);

            $newPassword = $form->addControl('new_password', 'Нова парола', 'password');
            $newPassword->field->required = true;
            $newPassword->field->never_persist = true;
            
            View::addTo($form->layout, ['ui' => 'hidden divider']);
            
            /**
             * onSubmit handler
             */
            $form->onSubmit(function (Form $form) use ($request) {

                if ($request->filled('new_password') && $this->model->checkPasswordStrength($request->input('new_password'))) {

                    if ($this->model->loaded()) {
                        $this->model->set('password', Hash::make($request->input('new_password')));
                        $this->model->save();
                    }
                } else {
                    return $form->error('new_password', 'Min. 6, max. 8 characters with at least 1 uppercase letter, 1 lowercase letter and 1 numeric character');
                }

                return [new JsExpression('$(".atk-dialog-content").trigger("close")')];
            });
        });
    }
}