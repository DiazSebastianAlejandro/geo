<?php

namespace App\Models;

use DateTime;
use Exception;
use PDO;
use App\Helpers\Number as NumberHelper;

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
        if (!NumberHelper::isPowerOfTwo($qty_participants)) {
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

        $this->validatePlayersCount($totalPlayers);
        $this->validatePlayersStructure($players);

        shuffle($players);
        $roundName = $this->getRoundName($totalPlayers);

        if ($totalPlayers === 2) {
            $this->createSingleMatch($players, $roundName);
        } else {
            $this->createTournamentMatches($players, $roundName);
        }

        $this->updateDrawGeneratedAt();
    }

    private function createSingleMatch(array $players, string $roundName): void {
        $match = new TournamentMatch(
            $this->id,
            $players[0]->player_id,
            $players[1]->player_id,
            $roundName,
            1
        );
        $match->save();
    }

    private function createTournamentMatches(array $players, string $roundName): void {
        $matchNumber = 1;

        foreach (array_chunk($players, 2) as $pair) {
            $match = new TournamentMatch(
                $this->id,
                $pair[0]->player_id,
                $pair[1]->player_id,
                $roundName,
                $matchNumber++
            );
            $match->save();
        }
    }



    private function validatePlayersCount(int $totalPlayers): void {
        if ($totalPlayers < 2 || !NumberHelper::isPowerOfTwo($totalPlayers)) {
            throw new Exception("The tournament must have a power of 2 players (e.g., 2, 4, 8, 16, ...).");
        }
    }

    private function validatePlayersStructure(array $players): void {
        foreach ($players as $player) {
            if (empty($player->player_id)) {
                throw new Exception("Player ID missing in TournamentPlayer objects.");
            }
        }
    }

    private function updateDrawGeneratedAt(): void {
        $this->draw_generated_at = new DateTime();
        $this->update(["draw_generated_at" => $this->draw_generated_at->format('Y-m-d H:i:s')]);
    }

    public function generateNextRound(): void {
        if ($this->getChampion()) {
            return;
        }

        $lastRound = $this->getLastRound();
        $lastRoundMatches = TournamentMatch::getMatchesByRound($this->id, $lastRound);

        $winners = $this->getWinnersFromMatches($lastRoundMatches);

        if (count($winners) === 1) {
            $this->setChampion($winners[0]);
            return;
        }

        $nextRoundName = $this->getNextRoundName($lastRound);
        if (!$nextRoundName) {
            throw new Exception("No next round available.");
        }

        $this->createNextRoundMatches($winners, $nextRoundName);
    }

    private function getWinnersFromMatches(array $matches): array {
        if (empty($matches)) {
            throw new Exception("No matches found for the last round.");
        }

        $winners = array_map(function ($match) {
            if (!$match->winner_id) {
                $match->playMatch();
            }
            return $match->winner_id;
        }, $matches);

        if (!NumberHelper::isPowerOfTwo(count($winners))) {
            throw new Exception("Winners count is not a power of 2.");
        }

        return $winners;
    }

    private function setChampion(int $winnerId): void {
        $this->champion_id = $winnerId;
        $this->finish_at = new DateTime();
        $this->update([
                          "champion_id" => $this->champion_id,
                          "finish_at" => $this->finish_at->format('Y-m-d H:i:s')
                      ]);
    }


    private function createNextRoundMatches(array $winners, string $roundName): void {
        $totalWinners = count($winners);

        if ($totalWinners < 2) {
            throw new Exception("Not enough winners to create the next round.");
        }

        if ($totalWinners === 2) {
            $match = new TournamentMatch(
                $this->id,
                (int) $winners[0],
                (int) $winners[1],
                $roundName,
                1
            );
            $match->save();
            return;
        }

        $matchNumbers = range(1, $totalWinners / 2);

        for ($i = 0; $i < $totalWinners; $i += 2) {
            $match = new TournamentMatch(
                $this->id,
                (int) $winners[$i],
                (int) $winners[$i + 1],
                $roundName,
                $matchNumbers[$i / 2]
            );
            $match->save();
        }
    }




    public function getChampion(): ?Player {
        if (!$this->champion_id) {
            return null;
        }

        return Player::find($this->champion_id);
    }

    public function getLastRound(): string {
        $lastRound = TournamentMatch::getLastRound($this->id);

        if ($lastRound === null) {
            throw new Exception("No matches found for the last round.");
        }

        return $lastRound;
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
