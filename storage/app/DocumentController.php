<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function files(Document $document, Request $request): JsonResponse
    {
        $files = $document->files;

        return response()->json([
            'data' => [
                'files' => $files,
            ]
        ]);
    }
}
