<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use App\Services\Legal\LegalContentService;
use Illuminate\Http\Request;

class LegalContentController extends Controller
{
    private LegalContentService $legalContentService;

    public function __construct(LegalContentService $legalContentService)
    {
        $this->legalContentService = $legalContentService;
    }

    public function show(Request $request, string $key)
    {
        $includeMeta = filter_var(
            $request->query('include_meta', true),
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE,
        );

        return sendResponse(
            $this->legalContentService->getByKey($key, $includeMeta ?? true)
        );
    }
}
