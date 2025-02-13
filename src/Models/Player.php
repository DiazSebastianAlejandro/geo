<?php

namespace App\Models;

use DateTime;
use OpenApi\Annotations as OA;

class Player extends BaseModel {
    protected static string $table = "players";

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
        $this->name = $name;
        $this->skill_level = $skill_level;
        $this->gender = $gender;
        $this->strength = $strength;
        $this->speed = $speed;
        $this->reaction_time = $reaction_time;
        $this->created_at = new DateTime();
    }
}

