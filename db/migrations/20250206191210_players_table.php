<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class PlayersTable extends AbstractMigration {
    public function change() {
        $table = $this->table('players');
        $table->addColumn('name', 'string', ['limit' => 100])
            ->addColumn('skill_level', 'integer', ['limit' => 100])
            ->addColumn('gender', 'enum', ['values' => ['Male', 'Female']])
            ->addColumn('strength', 'integer', ['default' => 0])
            ->addColumn('speed', 'integer', ['default' => 0])
            ->addColumn('reaction_time', 'integer', ['default' => 0])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->create();
    }
}
