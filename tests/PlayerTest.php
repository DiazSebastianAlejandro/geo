<?php

require __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use App\Models\Player;
use App\Models\BaseModel;

class PlayerTest extends TestCase {
    private PDO $pdo;

    protected function setUp(): void {
        $this->pdo = $this->createMock(\PDO::class);
        BaseModel::initDatabase($this->pdo);
    }

    public function testPlayerAttributes(): void {
        $player = new Player("John Doe", 85, "Male", 10, 8, 7);

        $this->assertEquals("John Doe", $player->name);
        $this->assertEquals(85, $player->skill_level);
        $this->assertEquals("Male", $player->gender);
        $this->assertEquals(10, $player->strength);
        $this->assertEquals(8, $player->speed);
        $this->assertEquals(7, $player->reaction_time);
    }

    public function testSaveMethod(): void {
        $mockStatement = $this->createMock(\PDOStatement::class);
        $mockStatement->expects($this->once())->method('execute')->willReturn(true);

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($mockStatement);

        $player = new Player("John Doe", 85, "Male", 10, 8, 7);
        $this->assertTrue($player->save());
    }

    public function testFindMethod(): void {
        $mockStatement = $this->createMock(\PDOStatement::class);
        $mockStatement->expects($this->once())->method('execute');
        $mockStatement->expects($this->once())->method('fetch')->willReturn([
                                                                                "name" => "John Doe",
                                                                                "skill_level" => 85,
                                                                                "gender" => "Male",
                                                                                "strength" => 10,
                                                                                "speed" => 8,
                                                                                "reaction_time" => 7
                                                                            ]);

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($mockStatement);

        $this->assertNotNull(Player::find(1));
    }

    public function testDeleteMethod(): void {
        $mockStatement = $this->createMock(\PDOStatement::class);
        $mockStatement->expects($this->once())->method('execute')->willReturn(true);

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($mockStatement);

        $this->assertTrue(Player::delete(1));
    }
}
