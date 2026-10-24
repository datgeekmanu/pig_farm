<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FarmResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FarmController extends Controller
{
    // Farms the authenticated user can access (with their role per farm).
    public function index(Request $request): AnonymousResourceCollection
    {
        return FarmResource::collection($request->user()->farms);
    }
}
