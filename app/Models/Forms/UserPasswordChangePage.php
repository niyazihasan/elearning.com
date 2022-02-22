<?php

namespace App\Models\Forms;

use App\Models\AbstractUser;

use Atk4\Ui\{VirtualPage, View, Form, JsExpression};
use Atk4\Ui\Form\Control\Password;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserPasswordChangePage extends VirtualPage 
{
    public $model = null;
    private $request = null; 
    
    public function __construct(Request $request, AbstractUser $user)
    {
        parent::__construct();
        
        $this->model = $user;
        $this->request = $request;
    }

    protected function init(): void
    {
        parent::init();
        
        $this->set(function ($vp) {
            
            $form = $vp->add([Form::class]);
            $form->setModel($this->model, []);

            $currentPwd = $form->addControl('current_pwd', [Password::class, 'caption' => 'Текуща парола']);
            $newPwd = $form->addControl('new_pwd', [Password::class, 'caption' => 'Нова парола']);
            $confirmPwd = $form->addControl('pwd_confirmation', [Password::class, 'caption' => 'Потвърди новата парола']);

            $newPwd->field->never_persist = true;
            $confirmPwd->field->never_persist = true;
            $currentPwd->field->never_persist = true;

            $this->model->getField('current_pwd')->required = true;
            $this->model->getField('new_pwd')->required = true;
            $this->model->getField('pwd_confirmation')->required = true;
            
            View::addTo($form->layout, ['ui' => 'hidden divider']);
            
            /**
             * onSubmit handler
             */
            $form->onSubmit(function (Form $form) {
                
                $validator = Validator::make($this->request->all(), [
                    'new_pwd' => 'required|same:pwd_confirmation',
                    'pwd_confirmation' => 'required'
                ], ['new_pwd.same' => 'The password confirmation must match.']);

                if ($validator->fails()) {
                    return $form->error('new_pwd', $validator->errors()->first('new_pwd'));
                }

                if (!Hash::check($this->request->input('current_pwd'), auth()->user()->password)) {
                    return $form->error('current_pwd', 'The current password is incorrect.');
                }

                if ($this->request->filled('new_pwd') && $this->model->checkPasswordStrength($this->request->input('new_pwd'))) {

                    if ($this->model->loaded()) {
                        $this->model->set('password', Hash::make($this->request->input('new_pwd')));
                        $this->model->save();
                    }
                    
                } else {
                    return $form->error('new_pwd', 'Min. 6, max. 8 characters with at least 1 uppercase letter, 1 lowercase letter and 1 numeric character');
                }
                
                return [new JsExpression('$(".atk-dialog-content").trigger("close")')];
            });
        });
    }
}
