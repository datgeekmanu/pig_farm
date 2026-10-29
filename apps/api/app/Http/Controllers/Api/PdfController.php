<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Farm;
use App\Models\Formula;
use App\Models\Sale;
use App\Models\WeightRecord;
use App\Support\CurrentFarm;
use App\Support\FinanceSummary;
use App\Support\FormulaCalculator;
use App\Support\Pdf;
use App\Support\ReproStats;
use App\Support\Stats;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PdfController extends Controller
{
    public function saleReceipt(Sale $sale): Response
    {
        return Pdf::render('pdf.receipt', $this->farm(), ['sale' => $sale->load('items')])
            ->stream("recu-vente-{$sale->id}.pdf");
    }

    public function batchSummary(Batch $batch): Response
    {
        $latestPerPig = WeightRecord::where('batch_id', $batch->id)
            ->orderByDesc('weighed_on')->orderByDesc('id')->get()
            ->groupBy('pig_id')->map(fn ($r) => (float) $r->first()->weight_kg)->values()->all();

        $stats = Stats::summary($latestPerPig) + [
            'deaths' => $batch->deathsCount(),
            'sold' => $batch->soldCount(),
            'current_head' => $batch->currentHead(),
        ];

        return Pdf::render('pdf.batch-summary', $this->farm(), compact('batch', 'stats'))
            ->stream("lot-{$batch->id}.pdf");
    }

    public function formulaSheet(Formula $formula): Response
    {
        $formula->load('lines.ingredient');
        $calc = $formula->lines->map(fn ($l) => ['ingredient' => $l->ingredient, 'percent' => (float) $l->percent])->all();
        $nutrition = FormulaCalculator::nutrition($calc);

        return Pdf::render('pdf.formula-sheet', $this->farm(), compact('formula', 'nutrition'))
            ->stream("formule-{$formula->id}.pdf");
    }

    public function financeReport(Request $request): Response
    {
        $from = $request->date('from')?->toDateString();
        $to = $request->date('to')?->toDateString();
        $s = FinanceSummary::compute($from, $to);

        return Pdf::render('pdf.finance-report', $this->farm(), [
            'ca' => $s['chiffre_affaires'],
            'depenses' => $s['depenses_totales'],
            'benefice' => $s['benefice_brut'],
            'par_categorie' => $s['par_categorie'],
            'periode' => $this->periode($from, $to),
        ])->stream('rapport-financier.pdf');
    }

    public function gttReport(Request $request): Response
    {
        $from = $request->date('from')?->toDateString();
        $to = $request->date('to')?->toDateString();

        return Pdf::render('pdf.gtt-report', $this->farm(), [
            'gtt' => ReproStats::gtt($from, $to),
            'periode' => $this->periode($from, $to),
        ])->stream('rapport-gtt.pdf');
    }

    private function farm(): Farm
    {
        return Farm::findOrFail(app(CurrentFarm::class)->id());
    }

    private function periode(?string $from, ?string $to): string
    {
        if (! $from && ! $to) {
            return 'Toutes périodes';
        }

        return 'Du '.($from ?? '…').' au '.($to ?? '…');
    }
}
