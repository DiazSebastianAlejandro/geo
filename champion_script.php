<?php

require __DIR__ . "/vendor/autoload.php";

use App\Models\Player;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Models\TournamentPlayer;

// 🔹 Definir `n` entre 1 y 5 y calcular la cantidad de jugadores
$n = rand(1, 5);
$powerOfTwoPlayers = pow(2, $n);

echo "<strong>🔢 Cantidad de Jugadores (2^$n): $powerOfTwoPlayers</strong><br><br>";

// 🔹 Crear torneo
echo "<strong>🏆 Crear Torneo..</strong><br>";
$tournament = new Tournament("Grand Test", rand(0, 1) ? Tournament::GENDER_MALE : Tournament::GENDER_FEMALE, $powerOfTwoPlayers);
$tournament->save();
echo "✅ {$tournament->id}{$tournament->name} torneo {$tournament->gender} de {$tournament->qty_participants} participantes.<br><br>";

// 🔹 Crear jugadores
echo "<strong>👤 Creando participantes...</strong><br>";
$players = [];
for ($i = 1; $i <= $powerOfTwoPlayers; $i++) {
    $player = new Player("Player $i", rand(70, 100), $tournament->gender, rand(50, 90), rand(50, 90), rand(50, 90));
    $player->save();
    $players[] = $player;
    echo "✅ Saved: {$player->name}<br>";
}
echo "<br>";

// 🔹 Agregar jugadores al torneo
echo "<strong>👥 Agregar jugadores al torneo..</strong><br>";
foreach ($players as $player) {
    $tournamentPlayer = new TournamentPlayer($tournament->id, $player->id);
    $tournamentPlayer->save();
    echo "✅ Agregado: {$player->name} to Tournament<br>";
}
echo "<br>";

// 🔹 Generar el sorteo
echo "<strong>🎲 Generar cuadro...</strong><br>";
$tournament->makeDraw();
echo "✅ Cuadro generado correctamente!<br><br>";

// 🔹 Jugar rondas hasta obtener un campeón
while (!$tournament->getChampion()) {
    $currentRound = $tournament->getLastRound();
    echo "<strong>▶️ Jugando: $currentRound</strong><br>";

    // 🔹 Mostrar los partidos de la ronda actual
    $matches = TournamentMatch::getMatchesByRound($tournament->id, $currentRound);
    foreach ($matches as $match) {
        $player1 = Player::find($match->player_left_id);
        $player2 = Player::find($match->player_right_id);

        if ($player1 && $player2) {
            echo "🎾 Partido {$match->match_number}: {$player1->name} vs {$player2->name}<br>";
        } else {
            echo "⚠️ Partido {$match->match_number}: Jugador invalido.<br>";
        }
    }

    // 🔹 Jugar la ronda
    $tournament->generateNextRound();

    // 🔹 Mostrar ganadores de la ronda
    echo "<br><strong>🏆 Ganadores de $currentRound:</strong><br>";
    $matches_finishes = TournamentMatch::getMatchesByRound($tournament->id, $currentRound);
    $winners = [];
    foreach ($matches_finishes as $match) {
        if ($match->winner_id) {
            $winner = Player::find($match->winner_id);
            if ($winner) {
                $winners[] = "✅ {$winner->name}";
            }
        } else {
            echo "⚠️ Warning: Match {$match->match_number} has no winner yet.<br>";
        }
    }
    echo implode("<br>", $winners) . "<br><br>";
}

// 🔹 Mostrar campeón
$champion = $tournament->getChampion();
echo "<strong>🏆 The Campeon es: {$champion->name}!</strong><br>";
