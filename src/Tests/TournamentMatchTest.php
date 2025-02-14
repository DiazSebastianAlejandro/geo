<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Models\TournamentMatch;
use App\Models\Player;
use App\Models\Tournament;
use App\Models\BaseModel;
use PDO;
use PDOStatement;

class TournamentMatchTest extends TestCase
{
    /**
     * Command: vendor/bin/phpunit --filter testCreateMatch src/Tests/TournamentMatchTest.php
     */
    public function testCreateMatch(): void {
        $tournament = new Tournament("Test Tournament", rand(0, 1) ? Tournament::GENDER_MALE : Tournament::GENDER_FEMALE, 8);
        $tournament->save();
        $player1 = new Player("Player 1", 80, $tournament->gender, 10, 10, 5);
        $player1->save();
        $player2 = new Player("Player 2", 75, $tournament->gender, 8, 12, 6);
        $player2->save();

        $match = new TournamentMatch($tournament->id, $player1->id, $player2->id, "Quarterfinal", 1);
        $match->save();

        $savedMatch = TournamentMatch::find($match->id);
        $this->assertInstanceOf(TournamentMatch::class, $savedMatch);
        $this->assertEquals($tournament->id, $savedMatch->tournament_id);
        $this->assertEquals("Quarterfinal", $savedMatch->round);
    }

    /**
     * Command: vendor/bin/phpunit --filter testGetMatchesByRound src/Tests/TournamentMatchTest.php
     */
    public function testGetMatchesByRound(): void
    {
        $matches = TournamentMatch::getMatchesByRound(1, "Quarterfinal");
        $this->assertNotEmpty($matches, "No matches found for the given tournament and round.");

        $this->assertIsArray($matches);
        foreach ($matches as $match) {
            $this->assertInstanceOf(TournamentMatch::class, $match);
            $this->assertEquals("Quarterfinal", $match->round);
        }
    }

    /**
     * Command: vendor/bin/phpunit --filter testGetLastRound src/Tests/TournamentMatchTest.php
     */
    public function testGetLastRound(): void
    {
        $tournament = new Tournament("Test Tournament", rand(0, 1) ? Tournament::GENDER_MALE : Tournament::GENDER_FEMALE, 8);
        $tournament->save();
        $player1 = new Player("Player 1", 80, $tournament->gender, 10, 10, 5);
        $player1->save();
        $player2 = new Player("Player 2", 75, $tournament->gender, 8, 12, 6);
        $player2->save();

        $match = new TournamentMatch($tournament->id, $player1->id, $player2->id, "Quarterfinal", 1);
        $match->save();
        $lastRound = TournamentMatch::getLastRound($tournament->id);
        if ($lastRound !== null) {
            $this->assertIsString($lastRound);
        } else {
            $this->assertNull($lastRound);
        }
    }

    /**
     * Command: vendor/bin/phpunit --filter testDetermineWinner src/Tests/TournamentMatchTest.php
     */
    public function testDetermineWinner(): void {
        $tournamentGender = rand(0, 1) ? Tournament::GENDER_MALE : Tournament::GENDER_FEMALE;
        $tournament = new Tournament("Test Tournament", $tournamentGender, 8);
        $tournament->save();

        $player1 = new Player("Player 1", 80, $tournament->gender, 10, 10, 5);
        $player1->save();
        $player2 = new Player("Player 2", 75, $tournament->gender, 8, 12, 6);
        $player2->save();

        $match = new TournamentMatch($tournament->id, $player1->id, $player2->id, "Quarterfinal", 1);
        $match->save();

        $winner = $match->determineWinner($player1, $player2);
        $this->assertContains($winner, [$player1->id, $player2->id]);
    }

    /**
     * Command: vendor/bin/phpunit --filter testPlayMatch src/Tests/TournamentMatchTest.php
     */
    public function testPlayMatch(): void
    {
        $tournamentGender = rand(0, 1) ? Tournament::GENDER_MALE : Tournament::GENDER_FEMALE;
        $tournament = new Tournament("Test Tournament", $tournamentGender, 8);
        $tournament->save();

        $player1 = new Player("Player 1", 80, $tournament->gender, 10, 10, 5);
        $player1->save();
        $player2 = new Player("Player 2", 75, $tournament->gender, 8, 12, 6);
        $player2->save();

        $match = new TournamentMatch($tournament->id, $player1->id, $player2->id, "Quarterfinal", 1);
        $match->save();
        $match->playMatch();

        $this->assertNotNull($match->winner_id);
    }

}

