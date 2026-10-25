@extends('pdf.layout')
@php use App\Support\Money; @endphp

@section('content')
    <h1>Reçu de vente #{{ $sale->id }}</h1>
    <p class="muted">
        Date: {{ $sale->date_vente?->format('d/m/Y') }}
        @if($sale->acheteur) · Acheteur: {{ $sale->acheteur }}@endif
        @if($sale->acheteur_tel) · {{ $sale->acheteur_tel }}@endif
    </p>

    <table class="data">
        <thead>
            <tr>
                <th>Désignation</th>
                <th class="right">Qté</th>
                <th class="right">Poids (kg)</th>
                <th class="right">Prix/kg</th>
                <th class="right">Remise</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
                <tr>
                    <td>{{ $item->type_porc ?: 'Porc' }}</td>
                    <td class="right">{{ $item->quantite }}</td>
                    <td class="right">{{ $item->poids_total_kg }}</td>
                    <td class="right">{{ Money::format($item->prix_kg, $farm->currency) }}</td>
                    <td class="right">{{ $item->remise_pct }}%</td>
                    <td class="right">{{ Money::format($item->line_total, $farm->currency) }}</td>
                </tr>
            @endforeach
            <tr class="totals">
                <td colspan="5" class="right">TOTAL</td>
                <td class="right">{{ Money::format($sale->total, $farm->currency) }}</td>
            </tr>
        </tbody>
    </table>

    <p class="muted">Mode de paiement: {{ $sale->mode_paiement ?: '—' }} · Statut: {{ $sale->statut_paiement }}</p>
@endsection
