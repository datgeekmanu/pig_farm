<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Expense\StoreExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ExpenseController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Expense::query()
            ->when($request->filled('from'), fn ($q) => $q->whereDate('date', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('date', '<=', $request->date('to')))
            ->when($request->filled('categorie'), fn ($q) => $q->where('categorie', $request->string('categorie')));

        return ExpenseResource::collection($query->latest('date')->paginate(20));
    }

    public function store(StoreExpenseRequest $request): JsonResponse
    {
        $expense = Expense::create($request->validated());

        return ExpenseResource::make($expense)->response()->setStatusCode(201);
    }

    public function show(Expense $expense): ExpenseResource
    {
        return ExpenseResource::make($expense);
    }

    public function update(StoreExpenseRequest $request, Expense $expense): ExpenseResource
    {
        $expense->update($request->validated());

        return ExpenseResource::make($expense);
    }

    public function destroy(Expense $expense): JsonResponse
    {
        $expense->delete();

        return response()->json(['message' => 'Expense deleted.']);
    }
}
