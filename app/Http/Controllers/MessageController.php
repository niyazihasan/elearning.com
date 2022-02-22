<?php

namespace App\Http\Controllers;

use App\Models\{User, Message};
use App\Models\Forms\SendMessagePage;

use Illuminate\Http\Request;

use Atk4\Ui\{Menu, View, Header, Grid, JsModal, JsReload, JsExpression};

class MessageController extends AtkController
{
    public function inbox()
    {
        $this->initLocalMenu();

        View::addTo($this->atk, ['ui' => 'hidden divider']);

        Header::addTo($this->lm, ['Получени', 'icon' => 'inbox', 'subHeader' => 'Създай и управлявай съобщения']);

        $model = new Message($this->atkDb);
        $model->addCondition('recipient_id', auth()->user()->id);
        $model->addCondition('deleted_by_recipient', null);

        $senderName = $model->addField('sender_name');
        $senderName->never_persist = true;

        $model->onHook(\Atk4\Data\Model::HOOK_AFTER_LOAD, function ($m) {
            
            $title = '';
            
            if ($m->ref('sender_id')->get('login_type') == User::LOGIN_TYPE_TEACHER) {
                $title = $m->ref('sender_id')->get('title');
            }
            
            $m->set('sender_name', "$title " . $m->ref('sender_id')->get('name') . ' ' . $m->ref('sender_id')->get('sname'));
        });

        $grid = Grid::addTo($this->atk, ['ipp' => 20]);
        $grid->setModel($model, ['subject']);
        $grid->addQuickSearch(['message', 'subject']);

        $grid->addColumn('sender_name', null, ['caption' => 'От']);
        $grid->addColumn('created_at');

        $grid->addColumn(null, new \App\Ui\ToggleIcon('read_at', function ($js) {
            
            return $this->atk->jsRedirect(route('message.show', ['message' => intval($_POST['id'])]));

        }, ['icon' => 'green envelope', 'inactiveIcon' => 'green envelope open']));

        $selection = $grid->addSelection();

        $grid->menu->addItem(['Изтрий', 'icon' => 'red times'])->on('click', function ($js, $arg1) use ($grid) {
            
            foreach (explode(',', $arg1) as $id) {
                $model = new Message($this->atkDb);
                $model->load($id);
                $model->markAsDelete('deleted_by_recipient');
            }

            return new JsReload($grid);

        }, ['args'=> [new JsExpression('[]', [$selection->jsChecked()])]]);

        return response($this->atk->run());
    }

    public function outbox()
    {
        $this->initLocalMenu();

        View::addTo($this->atk, ['ui' => 'hidden divider']);

        Header::addTo($this->lm, ['Изпратени', 'icon' => 'share square', 'subHeader' => 'Създай и управлявай съобщения']);

        $model = new Message($this->atkDb);
        $model->addCondition('sender_id', auth()->user()->id);
        $model->addCondition('deleted_by_sender', null);
        
        $recipientName = $model->addField('recipient_name');
        $recipientName->never_persist = true;

        $model->onHook(\Atk4\Data\Model::HOOK_AFTER_LOAD, function($m) {
            
            $title = '';

            if ($m->ref('recipient_id')->get('login_type') == User::LOGIN_TYPE_TEACHER) {
                $title = $m->ref('recipient_id')->get('title');
            }

            $m->set('recipient_name', "$title " . $m->ref('recipient_id')->get('name') . ' ' . $m->ref('recipient_id')->get('sname'));
        });

        $grid = Grid::addTo($this->atk, ['ipp' => 20]);

        $grid->setModel($model, ['subject']);
        $grid->addQuickSearch(['message', 'subject']);
        $grid->addColumn('recipient_name', null, ['caption' => 'До']);
        $grid->addColumn('created_at');

        $grid->addColumn(null, new \App\Ui\ToggleIcon('read_at', function ($js) {
            
            return $this->atk->jsRedirect(route('message.show', ['message' => intval($_POST['id'])]));

        }, ['icon' => 'green envelope', 'inactiveIcon' => 'green envelope open']));

        $selection = $grid->addSelection();

        $grid->menu->addItem(['Изтрий', 'icon' => 'red times'])->on('click', function ($js, $arg1) use ($grid) {
            
            foreach (explode(',', $arg1) as $id) {
                $model = new Message($this->atkDb);
                $model->load($id);
                $model->markAsDelete('deleted_by_sender');
            }

            return new JsReload($grid);

        }, ['args'=> [new JsExpression('[]', [$selection->jsChecked()])]]);

        return response($this->atk->run());
    }

    public function show(Request $request)
    {
        $this->initLocalMenu();

        Header::addTo($this->lm, ['Съобщение', 'icon' => 'envelope open outline']);

        $model = new Message($this->atkDb);
        $model->tryLoad($request->message);

        if (!$model->loaded()) {
            abort(404);
        }

        $model->markAsRead();

        $senderTitle = '';
        $recipientTitle = '';

        if ($model->ref('sender_id')->get('login_type') == User::LOGIN_TYPE_TEACHER) {
            $senderTitle = $model->ref('sender_id')->get('title');
        }

        if ($model->ref('recipient_id')->get('login_type') == User::LOGIN_TYPE_TEACHER) {
            $recipientTitle = $model->ref('recipient_id')->get('title');
        }

        Header::addTo($this->atk, ['От: ', 'subHeader' => "$senderTitle " . $model->ref('sender_id')->get('name') . ' ' . $model->ref('sender_id')->get('sname')]);
        View::addTo($this->atk, ['ui' => 'dividing divider']);

        Header::addTo($this->atk, ['До: ', 'subHeader' => "$recipientTitle " . $model->ref('recipient_id')->get('name') . ' ' . $model->ref('recipient_id')->get('sname')]);
        View::addTo($this->atk, ['ui' => 'dividing divider']);

        Header::addTo($this->atk, ['Тема: ' . $model->get('subject'), 'subHeader' => 'Дата: ' . $model->get('created_at')->format('Y-m-d H:i:s')]);
        View::addTo($this->atk, ['ui' => 'dividing divider']);

        Header::addTo($this->atk, ['Съобщение: ', 'subHeader' => $model->get('message')]);
        View::addTo($this->atk, ['ui' => 'dividing divider']);

        return response($this->atk->run());
    }

    private function initLocalMenu()
    {
        $this->lm = $this->atk->add([Menu::class]);

        $rm = $this->lm->addMenuRight();
        $rm->addItem(['Получени', 'icon' => 'inbox'], [route('message.inbox')]);
        $rm->addItem(['Изпратени', 'icon' => 'share square'], [route('message.outbox')]);

        $messagePage = new SendMessagePage(new Message($this->atkDb));

        $this->atk->add($messagePage);

        $modal = new JsModal("Напиши Съобщение", $messagePage->getUrl('cut'));
        $modal->setOption('modalCss', 'tiny');

        $rm->addItem(['Изпрати', 'icon' => 'paper plane'], $modal);
    }
}