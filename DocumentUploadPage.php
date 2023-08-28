<?php

namespace App\Models\Forms;

use App\Models\Document;

use Atk4\Data\Model;
use Atk4\Ui\{Form, Grid, View, JsExpression, JsReload, JsToast, VirtualPage};
use Atk4\Ui\Form\Control\Upload;

class DocumentUploadPage extends VirtualPage
{
    public $model = null;
    private $downloadable = null;
    private $documentType = '';
    private $additionalGrid = null;

    public function __construct(Document $model, Model $downloadable, string $documentType, Grid $grid)
    {
        parent::__construct();

        $this->model = $model;
        $this->downloadable = $downloadable;
        $this->documentType = $documentType;
        $this->additionalGrid = $grid;
    }

    protected function init(): void
    {
        parent::init();

        $form = $this->add([Form::class]);
        $form->setModel($this->model, []);
        $form->buttonSave->set('Upload');

        $file = $form->addControl('file', [Upload::class,
                                           'placeholder' => 'Click to add a document',
                                           'caption' => $this->model->caption,
                                           'accept' => Document::ALLOWED_MIME_TYPES]);

        $file->field->never_persist = true;
        $file->field->required = true;

        /**
         * onUpload handler
         */
        $file->onUpload(function ($postFile) use ($file, $form) {

            $document = new Document($this->model->persistence);

            $id = $document->onUpload($postFile, $this->downloadable, $this->documentType);

            $document->tryLoad($id);

            if ($document->loaded()) {

                $document->set('instance_dir', $this->getApp()->page);
                $document->set('downloadable_id', $this->getApp()->page);
                $document->save();
            }

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

                return [
                    new JsReload($this->additionalGrid),
                    new JsToast('Document is saved!'),
                    new JsExpression('$(".atk-dialog-content").trigger("close")')
                ];
            }
        });
    }
}