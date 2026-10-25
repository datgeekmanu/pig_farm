<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 12px; color: #222; margin: 0; padding: 28px 32px; }
        .header { border-bottom: 3px solid {{ $color }}; padding-bottom: 10px; margin-bottom: 18px; }
        .header table { width: 100%; }
        .brand { font-size: 20px; font-weight: bold; color: {{ $color }}; }
        .contact { font-size: 10px; color: #666; }
        .logo { max-height: 56px; max-width: 160px; }
        h1 { font-size: 16px; margin: 0 0 12px; }
        table.data { width: 100%; border-collapse: collapse; margin: 10px 0; }
        table.data th { background: {{ $color }}; color: #fff; text-align: left; padding: 6px 8px; font-size: 11px; }
        table.data td { padding: 6px 8px; border-bottom: 1px solid #eee; }
        .right { text-align: right; }
        .totals td { font-weight: bold; border-top: 2px solid {{ $color }}; }
        .muted { color: #888; }
        .footer { margin-top: 24px; padding-top: 10px; border-top: 1px solid #ddd; font-size: 10px; color: #666; }
        .kpi { display: inline-block; width: 30%; padding: 8px; }
        .kpi .v { font-size: 16px; font-weight: bold; color: {{ $color }}; }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td>
                    <div class="brand">{{ $farm->name }}</div>
                    <div class="contact">
                        @if($farm->address){{ $farm->address }}<br>@endif
                        @if($farm->phone)Tél: {{ $farm->phone }} @endif
                        @if($farm->email) · {{ $farm->email }}@endif
                    </div>
                </td>
                <td class="right">
                    @if($logo)<img class="logo" src="{{ $logo }}">@endif
                </td>
            </tr>
        </table>
    </div>

    @yield('content')

    @if($farm->payment_info)
        <div class="footer">
            <strong>Paiement:</strong><br>{!! nl2br(e($farm->payment_info)) !!}
        </div>
    @endif
</body>
</html>
