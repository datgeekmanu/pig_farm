@extends('pdf.layout')

@section('content')
    <h1>Fiche du lot — {{ $batch->nom }}</h1>

    <table class="data">
        <tr><th>Bâtiment / Loge</th><td>{{ $batch->batiment_loge ?: '—' }}</td></tr>
        <tr><th>Date de mise en lot</th><td>{{ $batch->date_mise_en_lot?->format('d/m/Y') }}</td></tr>
        <tr><th>Effectif initial</th><td>{{ $batch->effectif_initial }}</td></tr>
        <tr><th>Décès</th><td>{{ $stats['deaths'] }}</td></tr>
        <tr><th>Vendus</th><td>{{ $stats['sold'] }}</td></tr>
        <tr><th>Effectif actuel</th><td>{{ $stats['current_head'] }}</td></tr>
        <tr><th>Poids moyen (kg)</th><td>{{ $stats['mean'] ?? '—' }}</td></tr>
        <tr><th>Homogénéité (CV%)</th><td>{{ $stats['cv_pct'] ?? '—' }}</td></tr>
        <tr><th>Statut</th><td>{{ $batch->status }}</td></tr>
    </table>
@endsection
