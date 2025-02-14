<?php

namespace App\Tests;

use App\Models\Tournament;
use App\Models\TournamentPlayer;
use App\Models\TournamentMatch;
use PHPUnit\Framework\TestCase;
use DateTime;
use Exception;

class TournamentTest extends TestCase
{
    /**
     * Comand: vendor/bin/phpunit --filter testTournamentCannotBeCreatedWithInvalidParticipants src/Tests/TournamentTest.php
     */
    public function testTournamentCannotBeCreatedWithInvalidParticipants()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("The number of participants must be a power of 2 (e.g., 2, 4, 8, 16, ...).");

        new Tournament("Invalid Tournament", Tournament::GENDER_MALE, 3);
    }

    /**
     * Test: Verifica que un torneo se crea correctamente.
     * Comando: vendor/bin/phpunit --filter testTournamentIsCreatedSuccessfully src/Tests/TournamentTest.php
     */
    public function testTournamentIsCreatedSuccessfully()
    {
        $tournament = new Tournament("Test Tournament", Tournament::GENDER_MALE, 8);
        $tournament->save();

        $this->assertSame("Test Tournament", $tournament->name);
        $this->assertSame(Tournament::GENDER_MALE, $tournament->gender);
        $this->assertSame(8, $tournament->qty_participants);
        $this->assertInstanceOf(DateTime::class, $tournament->start_at);
    }

    /**
     * Test: Verifica que makeDraw falla si no hay jugadores suficientes.
     * Comando: vendor/bin/phpunit --filter testMakeDrawFailsWithInvalidPlayers src/Tests/TournamentTest.php
     */
    public function testMakeDrawFailsWithInvalidPlayers()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("The tournament must have a power of 2 players (e.g., 2, 4, 8, 16, ...).");

        $tournament = new Tournament("Test Tournament", Tournament::GENDER_MALE, 8);
        $tournament->save();
        $tournament->makeDraw();
    }

    /**
     * Test: Verifica que makeDraw genera los partidos correctamente.
     * Comando: vendor/bin/phpunit --filter testMakeDrawCreatesMatches src/Tests/TournamentTest.php
     */
    public function testMakeDrawCreatesMatches()
    {
        $tournament = new Tournament("Test Tournament", Tournament::GENDER_MALE, 4);
        $tournament->save();

        (new TournamentPlayer($tournament->id, 1))->save();
        (new TournamentPlayer($tournament->id, 2))->save();
        (new TournamentPlayer($tournament->id, 3))->save();
        (new TournamentPlayer($tournament->id, 4))->save();

        $tournament->makeDraw();
        $matches = TournamentMatch::getMatchesByRound($tournament->id, "Semi-finals");
        $this->assertCount(2, $matches);
    }

    /**
     * Test: Verifica que generateNextRound falla si no hay partidos previos.
     * Comando: vendor/bin/phpunit --filter testGenerateNextRoundFailsWithoutMatches src/Tests/TournamentTest.php
     */
    public function testGenerateNextRoundFailsWithoutMatches()
    {
        $this->expectException(\Throwable::class);
        $this->expectExceptionMessage("No matches found for the last round.");

        $tournament = new Tournament("Test Tournament", Tournament::GENDER_MALE, 4);
        $tournament->save();
        $tournament->generateNextRound();
    }

    /**
     * Test: Verifica que generateNextRound genera correctamente la siguiente ronda.
     * Comando: vendor/bin/phpunit --filter testGenerateNextRoundCreatesNextMatches src/Tests/TournamentTest.php
     */
    public function testGenerateNextRoundCreatesNextMatches()
    {
        $tournament = new Tournament("Test Tournament", Tournament::GENDER_MALE, 4);
        $tournament->save();

        (new TournamentMatch($tournament->id, 1, 2, "Semi-finals", 1, 1))->save();
        (new TournamentMatch($tournament->id, 3, 4, "Semi-finals", 3, 3))->save();

        $tournament->generateNextRound();
        $matches = TournamentMatch::getMatchesByRound($tournament->id, "Final");

        $this->assertCount(1, $matches);
    }

    /**
     * Test: Verifica que getChampion retorna null antes de finalizar el torneo.
     * Comando: vendor/bin/phpunit --filter testGetChampionReturnsNullInitially src/Tests/TournamentTest.php
     */
    public function testGetChampionReturnsNullInitially()
    {
        $tournament = new Tournament("Test Tournament", Tournament::GENDER_MALE, 4);
        $tournament->save();
        $this->assertNull($tournament->getChampion());
    }

    /**
     * Comando: vendor/bin/phpunit --filter testGetChampionReturnsWinner src/Tests/TournamentTest.php
     */
    public function testGetChampionReturnsWinner()
    {
        $tournament = new Tournament("Test Tournament return champion", Tournament::GENDER_MALE, 4);
        $tournament->save();

        (new TournamentMatch($tournament->id, 1, 3, "Final", 1, 1))->save();
        $tournament->generateNextRound();

        $champion = $tournament->getChampion();
        $this->assertNotNull($champion);
        $this->assertSame(1, $champion->id);
    }
}
