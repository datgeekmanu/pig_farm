<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Farm;
use App\Models\Salary;
use App\Models\Sale;
use App\Support\CurrentFarm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    // Finance dashboard: CA / dépenses / bénéfice brut + monthly series + category breakdown.
    public function overview(Request $request): JsonResponse
    {
        [$from, $to] = [$request->date('from'), $request->date('to')];

        $sales = Sale::query()
            ->when($from, fn ($q) => $q->whereDate('date_vente', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('date_vente', '<=', $to))
            ->get(['date_vente', 'total']);

        $expenses = Expense::query()
            ->when($from, fn ($q) => $q->whereDate('date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('date', '<=', $to))
            ->get(['date', 'montant', 'categorie']);

        $salaries = Salary::query()
            ->when($from, fn ($q) => $q->whereDate('date_paiement', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('date_paiement', '<=', $to))
            ->get(['date_paiement', 'montant']);

        $ca = (int) $sales->sum('total');
        $depSalaires = (int) $salaries->sum('montant');
        $depTotal = (int) $expenses->sum('montant') + $depSalaires;

        // Monthly series (grouped in PHP for DB portability).
        $months = [];
        foreach ($sales as $s) {
            $months[$s->date_vente->format('Y-m')]['ca'] = ($months[$s->date_vente->format('Y-m')]['ca'] ?? 0) + $s->total;
        }
        foreach ($expenses as $e) {
            $months[$e->date->format('Y-m')]['depenses'] = ($months[$e->date->format('Y-m')]['depenses'] ?? 0) + $e->montant;
        }
        foreach ($salaries as $s) {
            $months[$s->date_paiement->format('Y-m')]['depenses'] = ($months[$s->date_paiement->format('Y-m')]['depenses'] ?? 0) + $s->montant;
        }
        ksort($months);
        $monthly = collect($months)->map(fn ($m, $key) => [
            'mois' => $key,
            'ca' => $m['ca'] ?? 0,
            'depenses' => $m['depenses'] ?? 0,
            'benefice' => ($m['ca'] ?? 0) - ($m['depenses'] ?? 0),
        ])->values();

        // Category breakdown (salaries folded into "Salaire").
        $byCategory = $expenses->groupBy('categorie')->map(fn ($g) => (int) $g->sum('montant'));
        if ($depSalaires > 0) {
            $byCategory['Salaire'] = ($byCategory['Salaire'] ?? 0) + $depSalaires;
        }
        $breakdown = $byCategory->map(fn ($montant, $categorie) => compact('categorie', 'montant'))->values();

        $farm = Farm::find(app(CurrentFarm::class)->id());

        return response()->json(['data' => [
            'chiffre_affaires' => $ca,
            'depenses_totales' => $depTotal,
            'benefice_brut' => $ca - $depTotal,
            'seuil_rentabilite' => $farm?->seuil_rentabilite,
            'sujets_vendus' => (int) Sale::query()
                ->when($from, fn ($q) => $q->whereDate('date_vente', '>=', $from))
                ->when($to, fn ($q) => $q->whereDate('date_vente', '<=', $to))
                ->withSum('items as sujets', 'quantite')->get()->sum('sujets'),
            'par_mois' => $monthly,
            'par_categorie' => $breakdown,
        ]]);
    }
}
