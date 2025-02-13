<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class TournamentsTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('tournaments');
        $table->addColumn('name', 'string', ['limit' => 100])
            ->addColumn('gender', 'enum', ['values' => ['Male', 'Female'], 'null' => false])
            ->addColumn('champion_id', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('qty_participants', 'integer')
            ->addColumn('start_at', 'datetime', ['null' => false])
            ->addColumn('finish_at', 'datetime', ['null' => true])
            ->addColumn('draw_generated_at', 'datetime', ['null' => true])
            ->addForeignKey('champion_id', 'players', 'id', ['delete' => 'SET NULL', 'update' => 'CASCADE'])
            ->create();
    }
}
