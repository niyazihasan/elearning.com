<?php

namespace App\Models\Forms;

use App\Models\{Task, Document};

use Atk4\Ui\{Form, Grid, JsReload, JsExpression, JsToast, VirtualPage};
use Atk4\Ui\Form\Control\{Textarea, Radio, Upload};

class AddTaskPage extends VirtualPage
{
    public $model = null;
    private $grid = null;

    public function __construct(Task $model, Grid $tasks)
    {
        parent::__construct();

        $this->model = $model;
        $this->grid = $tasks;
    }

    protected function init(): void
    {
        parent::init();

        $form = Form::addTo($this);
        $form->setModel($this->model, []);

        $form->addControl('name', [Textarea::class]);

        $group = $form->addGroup(['width' => 'two']);
        $group->addControls([
            ['solution_upload_period', ['iconLeft' => 'calendar icon', 'placeholder' => 'Date/Time']],
            ['type', [Radio::class, 'values' => Task::TYPES]]
        ]);

        $file = $form->addControl('file', [
            Upload::class,
            'placeholder' => 'Click to add a document',
            'caption' => 'Документ',
            'accept' => Document::ALLOWED_MIME_TYPES,
        ]);

        $file->field->never_persist = true;

        /**
         * onUpload handler
         */
        $file->onUpload(function ($postFile) use ($form, $file) {

            $document = new Document($this->model->persistence);

            $id = $document->onUpload($postFile, $this->model, Document::DOCUMENT_TYPE_TASK);

            if (!intval($id)) {
                return $form->error('file', $id);
            }

            $file->setFileId($id);
        });

        /**
         * onDelete handler
         */
        $file->onDelete(function ($fileId) use ($form) {

            $document = new Document($this->model->persistence);
            $document->tryLoad($fileId);

            if ($document->loaded()) {
                $document->onDelete();
            }

            return $form->js()->form('remove prompt', 'file');
        });

        /**
         * onSubmit handler
         */
        $form->onSubmit(function (Form $form) use ($file) {

            $form->model->set('teacher_id', auth()->user()->id);
            $form->model->save();

            if (intval($file->fileId)) {

                $document = new Document($this->model->persistence);
                $document->tryLoad($file->fileId);

                if ($document->loaded()) {

                    $document->set('instance_dir', $this->model->getId());
                    $document->set('downloadable_id', $this->model->getId());
                    $document->save();

                    $document->moveFromTmp();

                    $form->model->set('assignment_id', $document->getId());
                    $form->model->save();
                }
            }

            return [
                new JsReload($this->grid),
                new JsToast('Task is saved!'),
                new JsExpression('$(".atk-dialog-content").trigger("close")')
            ];
        });
    }
}