<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class TournamentPlayersTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('tournament_players');
        $table->addColumn('tournament_id', 'integer', ['null' => false, 'signed' => false])
            ->addColumn('player_id', 'integer', ['null' => false, 'signed' => false])
            ->addForeignKey('tournament_id', 'tournaments', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('player_id', 'players', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addIndex(['tournament_id', 'player_id'], ['unique' => true])
            ->create();
    }
}
