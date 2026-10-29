<!DOCTYPE html>
<html lang="fr">
<body style="font-family: sans-serif; color:#222;">
    <h2>Alertes — {{ $farm->name }}</h2>
    <p>Vous avez {{ $notifications->count() }} nouvelle(s) alerte(s) :</p>
    <ul>
        @foreach($notifications as $n)
            <li><strong>{{ $n->title }}</strong>@if($n->body) — {{ $n->body }}@endif</li>
        @endforeach
    </ul>
</body>
</html>
