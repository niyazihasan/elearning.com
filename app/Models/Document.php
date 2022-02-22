<?php

namespace App\Models;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Illuminate\Support\Facades\Storage;
use Atk4\Data\Model;

class Document extends Model
{
    public $table = 'documents';
    public $caption = 'Документ';

    const APPLICATION_PDF = 'application/pdf';
    const VIDEO_MP4 = 'video/mp4';
    const AUDIO_MPEG = 'audio/mpeg';
    const AUDIO_WAV = 'audio/x-wav';
    const IMAGE_PNG = 'image/png';
    const IMAGE_JPEG = 'image/jpeg';
    const IMAGE_GIF = 'image/gif';

    const ALLOWED_MIME_TYPES = [
        self::APPLICATION_PDF,
        self::VIDEO_MP4,
        self::AUDIO_MPEG,
        self::AUDIO_WAV,
        self::IMAGE_PNG,
        self::IMAGE_JPEG,
        self::IMAGE_GIF
    ];

    const DOCUMENT_TYPE_LECTURE = 'lecture';
    const DOCUMENT_TYPE_TASK = 'task';
    const DOCUMENT_TYPE_SOLUTION = 'solution';

    const DOCUMENT_TYPES = [
        self::DOCUMENT_TYPE_LECTURE,
        self::DOCUMENT_TYPE_TASK,
        self::DOCUMENT_TYPE_SOLUTION
    ];

    const DOCUMENT_DIRECTORY = 'documents';

    protected function init(): void
    {
        parent::init();

        $this->addField('created_by_id', ['type' => 'integer']);
        $this->addField('class_dir', ['type' => 'string']);
        $this->addField('instance_dir', ['type' => 'integer']);
        $this->addField('hash', ['type' => 'string']);
        $this->addField('name', ['type' => 'string', 'caption' => 'Име']);
        $this->addField('mime_type', ['type' => 'string']);
        $this->addField('downloadable_id', ['type' => 'integer']);
        $this->addField('downloadable_type', ['type' => 'string']);
        $this->addField('document_type', ['enum' => self::DOCUMENT_TYPES]);
        $this->addField('created_at', ['type' => 'datetime', 'default' => date('Y-m-d H:i:s'), 'caption' => 'Дата на добавяне']);
    }

    public function onUpload(array $file, Model $model, string $documentType, array $allowedMimeTypes = self::ALLOWED_MIME_TYPES)
    {
        if ($file === 'error') {
            return 'Error uploading file';
        }

        $mimeType = mime_content_type($file['tmp_name']);

        if (!in_array($mimeType, $allowedMimeTypes)) {
            return 'File type not allowed';
        }

        // 10 megabyte (MB) = 10 485 760 byte (B)
        if ($file['size'] > 10485760) {
            return 'File too big';
        }

        $file['mime_type'] = $mimeType;

        $filename = $this->createFilename($file['name']);

        $className = class_basename($model);

        $id = !empty($model->getId()) ? $model->getId() : 'tmp';

        $filePath  = 'app' . DIRECTORY_SEPARATOR;
        $filePath .= self::DOCUMENT_DIRECTORY . DIRECTORY_SEPARATOR;
        $filePath .= $className . DIRECTORY_SEPARATOR . $id;

        $storagePath = storage_path($filePath);

        $this->move($storagePath, $filename, $file['tmp_name']);

        return $this->createRecord($file, $model, $documentType, $filename);
    }

    public function onDelete()
    {
        if (file_exists($this->getLocalUrl())) {
            unlink($this->getLocalUrl());
        }

        $this->delete();
    }

    public function getNonCdnUrl()
    {
        return route('document.show', ['document' => $this->get('hash'), 'id' => $this->get('id')]);
    }

    public function getLocalUrl()
    {
        $url  = config('filesystems.disks.local.root'). DIRECTORY_SEPARATOR;
        $url .= self::DOCUMENT_DIRECTORY . DIRECTORY_SEPARATOR;
        $url .= $this->get('class_dir') . DIRECTORY_SEPARATOR;

        if ($this->get('instance_dir') > 0) {
            $url .= $this->get('instance_dir') . DIRECTORY_SEPARATOR . $this->get('hash');
        } else {
            $url .= 'tmp' . DIRECTORY_SEPARATOR . $this->get('hash');
        }

        return $url;
    }

    public function getDownloadHeaders()
    {
        $headers = [];

        switch ($this->get('mime_type')) {
            case self::APPLICATION_PDF:
                $headers = [
                    'Content-Type' => self::APPLICATION_PDF,
                    'Content-Length' => filesize($this->getLocalUrl())
                ];
                break;
            case self::VIDEO_MP4:
                $headers = [
                    'Content-Type' => self::VIDEO_MP4,
                    'Content-Length' => filesize($this->getLocalUrl())
                ];
                break;
            case self::AUDIO_MPEG:
                $headers = [
                    'Content-Type' => self::AUDIO_MPEG,
                    'Content-Length' => filesize($this->getLocalUrl())
                ];
                break;
            case self::IMAGE_PNG:
                $headers = [
                    'Content-Type' => self::IMAGE_PNG,
                    'Content-Length' => filesize($this->getLocalUrl())
                ];
                break;
            case self::IMAGE_JPEG:
                $headers = [
                    'Content-Type' => self::IMAGE_JPEG,
                    'Content-Length' => filesize($this->getLocalUrl())
                ];
                break;
            default :
                return $headers;
        }

        $headers['Content-Disposition'] = 'inline; filename="' . $this->get('name') . '"';

        return $headers;
    }

    private function createFilename(string $originalName)
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $filename = '_' . md5(microtime()) . '.' . $extension;
        return $filename;
    }

    private function createRecord(array $file, Model $model, string $documentType, string $filename)
    {
        $this->save([
            'created_by_id' => auth()->user()->id,
            'class_dir' => class_basename($model),
            'hash' => $filename,
            'name' => $file['name'],
            'mime_type' => $file['mime_type'],
            'downloadable_type' => get_class($model),
            'document_type' => $documentType
        ]);

        return $this->get('id');
    }

    private function move(string $directory, string $filename, string $tmpName)
    {
        if (!is_dir($directory)) {

            if (false === @mkdir($directory, 0777, true) && !is_dir($directory)) {
                throw new FileException(sprintf('Unable to create the "%s" directory', $directory));
            }
            
        } else if (!is_writable($directory)) {
            throw new FileException(sprintf('Unable to write in the "%s" directory', $directory));
        }

        $moved = move_uploaded_file($tmpName, $directory . DIRECTORY_SEPARATOR . $filename);

        if (!$moved) {
            throw new FileException('File not uploaded');
        }
    }

    public function moveFromTmp()
    {
        $newPath = self::DOCUMENT_DIRECTORY . DIRECTORY_SEPARATOR .
                $this->get('class_dir') . DIRECTORY_SEPARATOR .
                $this->get('instance_dir') . DIRECTORY_SEPARATOR . $this->get('hash');

        $tempPath = self::DOCUMENT_DIRECTORY . DIRECTORY_SEPARATOR .
                $this->get('class_dir') . DIRECTORY_SEPARATOR .
                'tmp' . DIRECTORY_SEPARATOR . $this->get('hash');

        Storage::move($tempPath, $newPath);
    }
}