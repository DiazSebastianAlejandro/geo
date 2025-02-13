<?php

namespace App\Models;

use Exception;
use PDO;

class TournamentMatch extends BaseModel {
    protected static string $table = "tournament_matches";

    public ?int $id = null;
    public int $tournament_id;
    public int $player_left_id;
    public int $player_right_id;
    public ?int $winner_id;
    public string $round;
    public int $match_number;

    public function __construct(int $tournament_id, int $player_left_id, int $player_right_id, string $round, int $match_number, ?int $winner_id = null) {
        parent::__construct();
        $this->tournament_id = $tournament_id;
        $this->player_left_id = $player_left_id;
        $this->player_right_id = $player_right_id;
        $this->round = $round;
        $this->match_number = $match_number;
        $this->winner_id = $winner_id;
    }

    public static function getMatchesByRound(int $tournamentId, string $round): array {
        $sql = "SELECT * FROM " . static::$table . " WHERE tournament_id = ? AND round = ?";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$tournamentId, $round]);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $matches = [];

        foreach ($results as $row) {
            $match = new TournamentMatch(
                $row['tournament_id'],
                $row['player_left_id'],
                $row['player_right_id'],
                $row['round'],
                $row['match_number'],
                $row['winner_id'] ?? null
            );

            if (isset($row['id'])) {
                $match->id = (int) $row['id'];
            }

            $matches[] = $match;
        }

        return $matches;
    }


    public static function getLastRound(int $tournamentId): ?string {
        $sql = "SELECT round FROM " . static::$table . " WHERE tournament_id = ? ORDER BY id DESC LIMIT 1";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$tournamentId]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['round'] : null;
    }

    public function determineWinner(Player $player1, Player $player2): int {
        $tournament = Tournament::find($this->tournament_id);
        if (!$tournament) {
            throw new Exception("Tournament not found.");
        }

        $p1Score = $player1->skill_level + rand(0, 20);
        $p2Score = $player2->skill_level + rand(0, 20);

        $p1Bonus = $player1->strength + $player1->speed;
        $p2Bonus = $player2->strength + $player2->speed;

        if ($tournament->gender === Tournament::GENDER_FEMALE) {
            $p1Bonus = $player1->reaction_time;
            $p2Bonus = $player2->reaction_time;
        }

        $p1Score += $p1Bonus;
        $p2Score += $p2Bonus;

        $winner = $p1Score >= $p2Score ? $player1->id : $player2->id;

        if (!$winner) {
            throw new Exception("Winner could not be determined between Player {$player1->id} and Player {$player2->id}.");
        }

        return $winner;
    }




    public function playMatch(): void {
        $player1 = Player::find($this->player_left_id);
        $player2 = Player::find($this->player_right_id);

        if (!$player1 || !$player2) {
            throw new Exception("Invalid match: one or both players are missing.");
        }

        $this->winner_id = $this->determineWinner($player1, $player2);

        if (!$this->winner_id) {
            throw new Exception("Winner could not be determined.");
        }

        $this->update(["winner_id" => $this->winner_id]);

        $updatedMatch = self::find($this->id);
        if (!$updatedMatch || !$updatedMatch->winner_id) {
            throw new Exception("Failed to save winner_id in DB.");
        }
    }


}
