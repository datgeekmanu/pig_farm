@extends('pdf.layout')
@php use App\Support\Money; @endphp

@section('content')
    <h1>Rapport financier</h1>
    <p class="muted">{{ $periode }}</p>

    <div>
        <div class="kpi"><div>Chiffre d'affaires</div><div class="v">{{ Money::format($ca, $farm->currency) }}</div></div>
        <div class="kpi"><div>Dépenses</div><div class="v">{{ Money::format($depenses, $farm->currency) }}</div></div>
        <div class="kpi"><div>Bénéfice brut</div><div class="v">{{ Money::format($benefice, $farm->currency) }}</div></div>
    </div>

    <h1 style="margin-top:18px">Dépenses par catégorie</h1>
    <table class="data">
        <thead><tr><th>Catégorie</th><th class="right">Montant</th></tr></thead>
        <tbody>
            @forelse($par_categorie as $row)
                <tr><td>{{ $row['categorie'] }}</td><td class="right">{{ Money::format($row['montant'], $farm->currency) }}</td></tr>
            @empty
                <tr><td colspan="2" class="muted">Aucune dépense</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
