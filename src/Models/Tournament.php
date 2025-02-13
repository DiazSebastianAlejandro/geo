<?php

namespace App\Models;

use DateTime;
use Exception;
use PDO;

class Tournament extends BaseModel {
    protected static string $table = "tournaments";
    public const GENDER_MALE = 'Male';
    public const GENDER_FEMALE = 'Female';

    public int $id;
    public string $name;
    public string $gender;
    public ?int $champion_id = null;
    public int $qty_participants;
    public DateTime $start_at;
    public ?DateTime $finish_at = null;
    public ?DateTime $draw_generated_at = null;

    public function __construct(string $name, string $gender, int $qty_participants) {
        if (!$this->isPowerOfTwo($qty_participants)) {
            throw new Exception("The number of participants must be a power of 2 (e.g., 2, 4, 8, 16, ...).");
        }

        parent::__construct();
        $this->name = $name;
        $this->gender = $gender;
        $this->qty_participants = $qty_participants;
        $this->start_at = new DateTime();
    }

    public function getPlayers(): array {
        return TournamentPlayer::findByTournament($this->id);
    }

    public function makeDraw(): void {
        $players = TournamentPlayer::findByTournament($this->id);
        $totalPlayers = count($players);

        if ($totalPlayers < 2 || ($totalPlayers & ($totalPlayers - 1)) !== 0) {
            throw new Exception("The tournament must have a power of 2 players (e.g., 2, 4, 8, 16, ...).");
        }

        shuffle($players);
        $roundName = $this->getRoundName($totalPlayers);

        $match_numbers = range(1, $totalPlayers / 2);

        for ($i = 0; $i < $totalPlayers; $i += 2) {
            if (!isset($players[$i]->player_id) || !isset($players[$i + 1]->player_id)) {
                throw new Exception("Player ID missing in TournamentPlayer objects.");
            }

            $match = new TournamentMatch(
                $this->id,
                $players[$i]->player_id,
                $players[$i + 1]->player_id,
                $roundName,
                $match_numbers[$i / 2]
            );
            $match->save();
        }

        $this->draw_generated_at = new DateTime();
        $this->update(["draw_generated_at" => $this->draw_generated_at->format('Y-m-d H:i:s')]);
    }



    public function init(): void {
        if ($this->draw_generated_at === null) {
            throw new Exception("The draw has not been generated yet. Run makeDraw() first.");
        }

        $currentRound = $this->getLastRound();

        if ($currentRound === "Final") {
            echo "🏆 The tournament has already finished!\n";
            return;
        }

        if ($this->isFirstRound($currentRound)) {
            echo "🎾 Starting the tournament with round: $currentRound...\n";
            $this->generateNextRound();
        } else {
            echo "🔹 Tournament has already started. Init() only plays the first round.\n";
        }
    }

    public function generateNextRound(): void {
        if ($this->getChampion()) {
            return;
        }

        $lastRound = $this->getLastRound();
        $lastRoundMatches = TournamentMatch::getMatchesByRound($this->id, $lastRound);

        if (empty($lastRoundMatches)) {
            throw new Exception("No matches found for the last round.");
        }

        $winners = [];
        foreach ($lastRoundMatches as $match) {
            if (!$match->winner_id) {
                $match->playMatch();
            }
            $winners[] = $match->winner_id;
        }

        if (!$this->isPowerOfTwo(count($winners))) {
            throw new Exception("Winners count is not a power of 2.");
        }

        if (count($winners) === 1) {
            $this->champion_id = $winners[0];
            $this->update(["champion_id" => $this->champion_id]);
            return;
        }

        $nextRoundName = $this->getNextRoundName($lastRound);
        if (!$nextRoundName) {
            throw new Exception("No next round available.");
        }

        $matchNumbers = range(1, count($winners) / 2);
        for ($i = 0; $i < count($winners) - 1; $i += 2) {
            $match = new TournamentMatch(
                $this->id,
                (int) $winners[$i],
                (int) $winners[$i + 1],
                $nextRoundName,
                $matchNumbers[$i / 2]
            );
            $match->save();
        }
    }

    private function isPowerOfTwo(int $n): bool {
        return ($n > 0) && (($n & ($n - 1)) === 0);
    }



    public function getChampion(): ?Player {
        if (!$this->champion_id) {
            return null;
        }

        return Player::find($this->champion_id);
    }




    public function getLastRound(): string {
        return TournamentMatch::getLastRound($this->id);
    }

    private function isFirstRound(string $round): bool {
        $totalPlayers = count($this->getPlayers());
        return $round === $this->getRoundName($totalPlayers);
    }

    private function getRoundName(int $playersCount): string {
        return match ($playersCount) {
            2 => "Final",
            4 => "Semi-finals",
            8 => "Quarter-finals",
            default => "R" . $playersCount,
        };
    }


    private function getNextRoundName(string $currentRound): ?string {
        return match ($currentRound) {
            "Quarter-finals" => "Semi-finals",
            "Semi-finals" => "Final",
            "Final" => null,
            default => (str_starts_with($currentRound, 'R'))
                ? $this->getRoundName((int)(substr($currentRound, 1) / 2))
                : null,
        };
    }

    public static function getAll(): array {
        self::initDatabase();
        $sql = "SELECT * FROM tournaments";
        $stmt = self::$db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


}
