<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class TournamentMatchesTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('tournament_matches');
        $table->addColumn('tournament_id', 'integer', ['null' => false, 'signed' => false])
            ->addColumn('player_left_id', 'integer', ['null' => false, 'signed' => false])
            ->addColumn('player_right_id', 'integer', ['null' => false, 'signed' => false])
            ->addColumn('winner_id', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('round', 'string', ['limit' => 50, 'null' => false])
            ->addColumn('match_number', 'string', ['limit' => 10, 'null' => false])
            ->addColumn('result', 'string', ['limit' => 50, 'null' => true])
            ->addForeignKey('tournament_id', 'tournaments', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('player_left_id', 'players', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('player_right_id', 'players', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('winner_id', 'players', 'id', ['delete' => 'SET NULL', 'update' => 'CASCADE'])
            ->create();
    }
}
