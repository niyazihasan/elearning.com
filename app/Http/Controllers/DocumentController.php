<?php

namespace App\Http\Controllers;

use App\Models\Document;

use Illuminate\Http\Request;

class DocumentController extends AtkController
{
    public function show(Request $request) 
    {
        $document = new Document($this->atkDb);
        $document->addCondition('hash', $request->document)
                 ->tryLoad($request->id);

        if (!$document->loaded()) {
            abort(404);
        }

        $path = $document->getLocalUrl();
        $headers = $document->getDownloadHeaders();

        return response()->file($path, $headers);
    }
}