<?php

namespace App\Models;

use DateTime;
use InvalidArgumentException;

class Player extends BaseModel {
    protected static string $table = "players";
    public const GENDER_MALE = 'Male';
    public const GENDER_FEMALE = 'Female';

    public int $id;
    public string $name;
    public int $skill_level;
    public string $gender;
    public int $strength;
    public int $speed;
    public int $reaction_time;
    public DateTime $created_at;

    public function __construct(
        string $name,
        int $skill_level,
        string $gender,
        int $strength = 0,
        int $speed = 0,
        int $reaction_time = 0
    ) {
        if ($skill_level < 0) {
            throw new InvalidArgumentException("Skill level cannot be negative.");
        }
        if (!in_array($gender, [self::GENDER_MALE, self::GENDER_FEMALE])) {
            throw new InvalidArgumentException("Gender must be either 'Male' or 'Female'.");
        }
        $this->name = $name;
        $this->skill_level = $skill_level;
        $this->gender = $gender;
        $this->strength = $strength;
        $this->speed = $speed;
        $this->reaction_time = $reaction_time;
        $this->created_at = new DateTime();
    }
}

