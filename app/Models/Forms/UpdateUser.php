<?php

namespace App\Models\Forms;

use Atk4\Ui\Form\Layout\Section\Columns;
use Atk4\Ui\{Header, Form, JsNotify};

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UpdateUser extends Form
{
    private $request = null;

    public function __construct(Request $request) 
    {
        parent::__construct();
        
        $this->request = $request;
    }
    
    public function setModel(\Atk4\Data\Model $user, $fields = [])
    {
        parent::setModel($user, []);
        
        $cols = $this->layout->addSubLayout([Columns::class]);

        /**
         * Column one
         */
        $c1 = $cols->addColumn(6);
        $c1->add([Header::class, "Редактирай"]);

        $c1->addControl('name');
        $c1->addControl('fname');
        $c1->addControl('sname');
        $c1->addControl('email');

        /**
         * onSubmit handler
         */
        $this->onSubmit(function (Form $form) use ($user) {

            $validator = Validator::make($this->request->all(), [
                'email' => "required|email|unique:users,email,{$user->id}"
            ]);

            if ($validator->fails()) {
                return $form->error('email', $validator->errors()->first('email'));
            }

            $form->model->save();

            return new JsNotify(['content' => 'Data is saved!', 'color' => 'green']);
        });

        return $this->model;
    }
}