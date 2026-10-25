<?php

namespace App\Support;

use App\Models\Expense;
use App\Models\Salary;
use App\Models\Sale;

class FinanceSummary
{
    /**
     * CA / dépenses / bénéfice + monthly series + category breakdown for a period.
     * Farm-scoped automatically. Money values are minor units.
     *
     * @return array<string, mixed>
     */
    public static function compute(?string $from, ?string $to): array
    {
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

        $months = [];
        foreach ($sales as $s) {
            $k = $s->date_vente->format('Y-m');
            $months[$k]['ca'] = ($months[$k]['ca'] ?? 0) + $s->total;
        }
        foreach ($expenses as $e) {
            $k = $e->date->format('Y-m');
            $months[$k]['depenses'] = ($months[$k]['depenses'] ?? 0) + $e->montant;
        }
        foreach ($salaries as $s) {
            $k = $s->date_paiement->format('Y-m');
            $months[$k]['depenses'] = ($months[$k]['depenses'] ?? 0) + $s->montant;
        }
        ksort($months);
        $monthly = collect($months)->map(fn ($m, $key) => [
            'mois' => $key,
            'ca' => $m['ca'] ?? 0,
            'depenses' => $m['depenses'] ?? 0,
            'benefice' => ($m['ca'] ?? 0) - ($m['depenses'] ?? 0),
        ])->values();

        $byCategory = $expenses->groupBy('categorie')->map(fn ($g) => (int) $g->sum('montant'));
        if ($depSalaires > 0) {
            $byCategory['Salaire'] = ($byCategory['Salaire'] ?? 0) + $depSalaires;
        }
        $breakdown = $byCategory->map(fn ($montant, $categorie) => compact('categorie', 'montant'))->values();

        $sujets = (int) Sale::query()
            ->when($from, fn ($q) => $q->whereDate('date_vente', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('date_vente', '<=', $to))
            ->withSum('items as sujets', 'quantite')->get()->sum('sujets');

        return [
            'chiffre_affaires' => $ca,
            'depenses_totales' => $depTotal,
            'benefice_brut' => $ca - $depTotal,
            'sujets_vendus' => $sujets,
            'par_mois' => $monthly,
            'par_categorie' => $breakdown,
        ];
    }
}
