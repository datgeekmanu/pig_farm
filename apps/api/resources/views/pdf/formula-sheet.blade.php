@extends('pdf.layout')
@php use App\Support\Money; @endphp

@section('content')
    <h1>Formule — {{ $formula->nom }}</h1>
    <p class="muted">
        Phase: {{ $formula->phase ?: '—' }} · Mode: {{ $formula->mode }}
        · Coût/kg: {{ Money::format($formula->cost_per_kg, $farm->currency) }}
    </p>

    <table class="data">
        <thead>
            <tr><th>Ingrédient</th><th class="right">Incorporation %</th></tr>
        </thead>
        <tbody>
            @foreach($formula->lines as $line)
                <tr>
                    <td>{{ $line->ingredient?->nom ?? '—' }}</td>
                    <td class="right">{{ $line->percent }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h1 style="margin-top:18px">Profil nutritionnel</h1>
    <table class="data">
        <thead><tr><th>Nutriment</th><th class="right">Valeur</th></tr></thead>
        <tbody>
            @foreach($nutrition as $code => $value)
                <tr><td>{{ $code }}</td><td class="right">{{ $value }}</td></tr>
            @endforeach
        </tbody>
    </table>
@endsection
