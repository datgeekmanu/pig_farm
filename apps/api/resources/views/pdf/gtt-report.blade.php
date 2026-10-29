@extends('pdf.layout')

@section('content')
    <h1>Rapport GTTT — Troupeau de truies</h1>
    <p class="muted">{{ $periode }}</p>

    <table class="data">
        <tr><th>Nombre de saillies</th><td>{{ $gtt['nb_saillies'] }}</td></tr>
        <tr><th>Nombre de mises bas</th><td>{{ $gtt['nb_mises_bas'] }}</td></tr>
        <tr><th>Taux de mise bas</th><td>{{ $gtt['taux_mise_bas_pct'] ?? '—' }}%</td></tr>
        <tr><th>Nés vivants (total)</th><td>{{ $gtt['nes_vivants_total'] }}</td></tr>
        <tr><th>Nés vivants / portée</th><td>{{ $gtt['nes_vivants_moyen'] ?? '—' }}</td></tr>
        <tr><th>Sevrés (total)</th><td>{{ $gtt['sevres_total'] }}</td></tr>
        <tr><th>Mortalité allaitement</th><td>{{ $gtt['mortalite_allaitement_pct'] ?? '—' }}%</td></tr>
    </table>
@endsection
