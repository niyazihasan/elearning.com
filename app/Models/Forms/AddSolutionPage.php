<?php

namespace App\Models\Forms;

use App\Models\{Task, Document};

use Atk4\Ui\{Form, View, Grid, JsReload, JsExpression, JsToast, VirtualPage};
use Atk4\Ui\Form\Control\Upload;

use Illuminate\Http\Request;

class AddSolutionPage extends VirtualPage
{
    private $request = null;
    public $model = null;
    private $grid = null;

    public function __construct(Request $request, Task $model, Grid $tasks)
    {
        parent::__construct();

        $this->request = $request;
        $this->model = $model;
        $this->grid = $tasks;
    }

    protected function init(): void
    {
        parent::init();

        if ($this->request->has('task_id')) {
            $this->model->tryLoad($this->stickyGet('task_id'));
        }

        $form = Form::addTo($this);
        $form->setModel($this->model, []);

        $file = $form->addControl('file', [Upload::class,
                                           'placeholder' => 'Click to add a document',
                                           'caption' => 'Документ',
                                           'accept' => Document::ALLOWED_MIME_TYPES]);

        $file->field->never_persist = true;
        $file->field->required = true;

        /**
         * onUpload handler
         */
        $file->onUpload(function ($postFile) use ($form, $file) {

            $document = new Document($this->model->persistence);

            $id = $document->onUpload($postFile, $this->model, Document::DOCUMENT_TYPE_SOLUTION);

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

        View::addTo($form->layout, ['ui' => 'hidden divider']);

        /**
         * onSubmit handler
         */
        $form->onSubmit(function (Form $form) use ($file) {

            if (intval($file->fileId)) {

                $document = new Document($this->model->persistence);
                $document->tryLoad($file->fileId);

                if ($document->loaded()) {

                    $document->set('instance_dir', $this->model->getId());
                    $document->set('downloadable_id', $this->model->getId());
                    $document->save();

                    $form->model->set('solution_id', $document->getId());
                    $form->model->save();
                }
            }

            return [
                new JsReload($this->grid),
                new JsToast('Solution is saved!'),
                new JsExpression('$(".atk-dialog-content").trigger("close")')
            ];
        });
    }
}