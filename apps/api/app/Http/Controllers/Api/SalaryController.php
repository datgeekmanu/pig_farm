<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Salary\StoreSalaryRequest;
use App\Http\Resources\SalaryResource;
use App\Models\Salary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SalaryController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Salary::query()
            ->when($request->filled('mois'), fn ($q) => $q->where('mois_concerne', $request->string('mois')));

        return SalaryResource::collection($query->latest('date_paiement')->paginate(20));
    }

    public function store(StoreSalaryRequest $request): JsonResponse
    {
        $salary = Salary::create($request->validated());

        return SalaryResource::make($salary)->response()->setStatusCode(201);
    }

    public function show(Salary $salary): SalaryResource
    {
        return SalaryResource::make($salary);
    }

    public function update(StoreSalaryRequest $request, Salary $salary): SalaryResource
    {
        $salary->update($request->validated());

        return SalaryResource::make($salary);
    }

    public function destroy(Salary $salary): JsonResponse
    {
        $salary->delete();

        return response()->json(['message' => 'Salary deleted.']);
    }
}
