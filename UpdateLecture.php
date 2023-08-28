<?php

namespace App\Models\Forms;

use Atk4\Ui\Form\Layout\Section\Columns;
use Atk4\Ui\{Button, View, Grid, Header, Form, JsNotify, JsModal, JsReload, JsExpression};

class UpdateLecture extends Form
{
    public function setModel(\Atk4\Data\Model $lecture, $fields = [])
    {
        parent::setModel($lecture, []);

        $lecture->load($this->getApp()->page);
        
        View::addTo($this->layout, ['ui' => 'hidden divider']);
        
        $cols = $this->layout->addSubLayout([Columns::class]);
        
        /**
         * Column one
         */
        $c1 = $cols->addColumn(6);
        $c1->add([Header::class, "Редактирай $lecture->caption"]);

        $c1->addControl('name');
        $c1->addControl('description', ['rows' => 4]);
  
        /**
         * Column two
         */
        $c2 = $cols->addColumn(10);
        $c2->add([Header::class, 'Документи']);

        $docs = $lecture->ref('Documents');
        $docs->getField('created_at')->type = 'date';

        $gridDocs = $c2->add([Grid::class]);
        $gridDocs->setModel($docs, ['name', 'mime_type', 'created_at']);

        $documentUpload = new DocumentUploadPage($docs, $lecture, \App\Models\Document::DOCUMENT_TYPE_LECTURE, $gridDocs);

        $this->add($documentUpload);

        $modalDoc = new JsModal("Качи $docs->caption", $documentUpload->getURL('cut'));
        $modalDoc->setOption('modalCss', 'mini');

        $gridDocs->menu->addItem(["Качи $docs->caption", 'icon' => 'upload'], $modalDoc);

        $gridDocs->addActionButton(['icon' => 'external link square alternate'], function ($js, $id) use ($docs) {
            
            return new JsExpression('window.open([])', [$docs->tryLoad($id)->getNonCdnUrl()]);
        });

        $gridDocs->addActionButton(['icon' => 'red trash'], function ($js, $id) use ($docs, $gridDocs) {
            
            $docs->tryLoad($id)->onDelete();
            
            return new JsReload($gridDocs);
            
        }, "Сигурен ли си за изтриването на $docs->caption?");

        /**
         * onSubmit handler
         */
        $this->onSubmit(function (Form $form) {

            $form->model->save();

            return new JsNotify(['content' => 'Lecture is saved!', 'color' => 'green']);
        });
        
        /**
         * Delete btn
         */
        $this->add([Button::class, 'Delete', 'ui' => 'negative button', 'style' => ['margin-left' => '1em']])->on('click', function ($btn) use ($lecture) {

            $disciplineId = $lecture->get('discipline_id');
            
            $lecture->delete();

            return $this->getApp()->jsRedirect(route('lecture.index', ['discipline' => $disciplineId]));
            
        }, ['confirm' => "Сигурен ли си за изтриването на $lecture->caption?"]);

        return $this->model;
    }
}