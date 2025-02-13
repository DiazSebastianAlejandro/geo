<?php

namespace App\Models;

use PDO;

class TournamentPlayer extends BaseModel {
    protected static string $table = "tournament_players";

    public int $tournament_id;
    public int $player_id;

    public function __construct(int $tournament_id, int $player_id) {
        parent::__construct();
        $this->tournament_id = $tournament_id;
        $this->player_id = $player_id;
    }

    public static function findByTournament(int $tournamentId): array {
        $sql = "SELECT * FROM " . static::$table . " WHERE tournament_id = ?";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$tournamentId]);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $players = [];

        foreach ($results as $row) {
            $players[] = new TournamentPlayer($row['tournament_id'], $row['player_id']);
        }

        return $players;
    }
}
