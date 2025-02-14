<?php

namespace App\Tests;

use DateTime;
use App\Models\Player;
use PHPUnit\Framework\TestCase;

class PlayerTest extends TestCase
{
    /**
     * console: vendor/bin/phpunit --filter testPlayerIsCreatedWithCorrectValues src/Tests/PlayerTest.php
     */
    public function testPlayerIsCreatedWithCorrectValues()
    {
        $player = new Player("Juan Pérez", 85, Player::GENDER_MALE);

        $this->assertSame("Juan Pérez", $player->name);
        $this->assertSame(85, $player->skill_level);
        $this->assertSame(Player::GENDER_MALE, $player->gender);
        $this->assertSame(0, $player->strength);
        $this->assertSame(0, $player->speed);
        $this->assertSame(0, $player->reaction_time);
        $this->assertInstanceOf(DateTime::class, $player->created_at);
    }

    /**
     * console: vendor/bin/phpunit --filter testPlayerCanBeCreatedWithCustomStats src/Tests/PlayerTest.php
     */
    public function testPlayerCanBeCreatedWithCustomStats()
    {
        $player = new Player("Ana López", 92, Player::GENDER_FEMALE, 50, 60, 70);

        $this->assertSame(Player::GENDER_FEMALE, $player->gender);
        $this->assertSame(50, $player->strength);
        $this->assertSame(60, $player->speed);
        $this->assertSame(70, $player->reaction_time);
    }

    /**
     * console: vendor/bin/phpunit --filter testCannotCreatePlayerWithNegativeSkillLevel src/Tests/PlayerTest.php
     */
    public function testCannotCreatePlayerWithNegativeSkillLevel()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Skill level cannot be negative.");

        new Player("Jugador Inválido", -10, "M");
    }

    /**
     * console: vendor/bin/phpunit --filter testPlayerAcceptsValidGenders src/Tests/PlayerTest.php
     */
    public function testPlayerAcceptsValidGenders()
    {
        $malePlayer = new Player("John Doe", 90, Player::GENDER_MALE);
        $this->assertSame(Player::GENDER_MALE, $malePlayer->gender);

        $femalePlayer = new Player("Jane Doe", 85, Player::GENDER_FEMALE);
        $this->assertSame(Player::GENDER_FEMALE, $femalePlayer->gender);
    }

    /**
     * console: vendor/bin/phpunit --filter testPlayerRejectsInvalidGender src/Tests/PlayerTest.php
     */
    public function testPlayerRejectsInvalidGender()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Gender must be either 'Male' or 'Female'.");

        new Player("Invalid Gender", 80, "Other");
    }

}
