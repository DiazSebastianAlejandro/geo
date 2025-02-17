<?php

namespace App\Controllers;

use App\Models\Tournament;
use App\Models\Player;
use App\Models\TournamentMatch;
use App\Models\TournamentPlayer;
use Exception;
use PDO;

class TournamentController {
    protected PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * @OA\Post(
     *     path="/api/tournament/simulate",
     *     summary="Simula un torneo a partir de una lista de jugadores",
     *     tags={"Tournament"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="gender", type="string"),
     *             @OA\Property(property="players", type="array", @OA\Items(type="object",
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="skill_level", type="integer"),
     *                 @OA\Property(property="strength", type="integer"),
     *                 @OA\Property(property="speed", type="integer"),
     *                 @OA\Property(property="reaction_time", type="integer")
     *             ))
     *         )
     *     ),
     *     @OA\Response(response=200, description="Resultado del torneo")
     * )
     */
    public function simulateTournament(array $data): array {
        try {
            $this->db->beginTransaction();

            $tournament = new Tournament($data['name'], $data['gender'], count($data['players']));
            $tournament->save();

            $players = [];
            foreach ($data['players'] as $playerData) {
                $player = new Player(
                    $playerData['name'],
                    $playerData['skill_level'],
                    $data['gender'],
                    $playerData['strength'] ?? 0,
                    $playerData['speed'] ?? 0,
                    $playerData['reaction_time'] ?? 0
                );
                $player->save();
                $players[] = $player;
            }

            foreach ($players as $player) {
                $tournamentPlayer = new TournamentPlayer($tournament->id, $player->id);
                $tournamentPlayer->save();
            }

            $tournament->makeDraw();

            while (!$tournament->getChampion()) {
                $tournament->generateNextRound();
            }

            $champion = $tournament->getChampion();

            $this->db->commit();
            return [
                'Champion' => $champion->name,
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['error' => $e->getMessage()];
        }
    }



    /**
     * @OA\Get(
     *     path="/api/tournament/completed",
     *     summary="Obtiene una lista de torneos finalizados con filtros opcionales",
     *     tags={"Tournament"},
     *     @OA\Parameter(name="date", in="query", description="Fecha del torneo", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="gender", in="query", description="Género del torneo", required=false, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Lista de torneos finalizados")
     * )
     */
    public function getCompletedTournaments(array $filters = []): array {
        $sql = "SELECT * FROM tournaments WHERE 1=1";
        $params = [];

        if (!empty($filters['date'])) {
            $sql .= " AND DATE(created_at) = ?";
            $params[] = $filters['date'];
        }

        if (!empty($filters['gender'])) {
            $sql .= " AND gender = ?";
            $params[] = $filters['gender'];
        }

        if (!empty($filters['champion_name'])) {
            $playerQuery = "SELECT id FROM players WHERE name LIKE ?";
            $playerStmt = $this->db->prepare($playerQuery);
            $playerStmt->execute(["%" . $filters['champion_name'] . "%"]);
            $playerIds = $playerStmt->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($playerIds)) {
                $placeholders = implode(',', array_fill(0, count($playerIds), '?'));
                $sql .= " AND champion_id IN ($placeholders)";
                $params = array_merge($params, $playerIds);
            } else {
                return [];
            }
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
