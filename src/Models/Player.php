<?php

namespace App\Models;

class Player extends BaseModel {
    protected static string $table = "players";

    public string $name;
    public int $skill_level;
    public string $gender;
    public int $strength;
    public int $speed;
    public int $reaction_time;

    public function __construct(string $name, int $skill_level, string $gender, int $strength = 0, int $speed = 0, int $reaction_time = 0) {
        self::initDatabase();
        $this->name = $name;
        $this->skill_level = $skill_level;
        $this->gender = $gender;
        $this->strength = $strength;
        $this->speed = $speed;
        $this->reaction_time = $reaction_time;
    }

    public function save(): bool {
        return parent::create([
                                  "name" => $this->name,
                                  "skill_level" => $this->skill_level,
                                  "gender" => $this->gender,
                                  "strength" => $this->strength,
                                  "speed" => $this->speed,
                                  "reaction_time" => $this->reaction_time
                              ]);
    }
}
