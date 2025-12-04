<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource as ClientApiResource;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientApiController extends Controller
{
    public function index(Request $request)
    {
        $q = Client::query()->orderBy('name');
        if ($type = $request->query('type')) {
            $q->where('type', $type);
        }
        return ClientApiResource::collection($q->paginate(50));
    }
}
