<?php

namespace Axn\Illuminate\Database\Schema\Grammars;

use Illuminate\Database\Schema\Grammars\MySqlGrammar as BaseMySqlGrammar;
use Illuminate\Support\Fluent;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;

class MySqlGrammar extends BaseMySqlGrammar
{
    /**
     * Compile a create table command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @param  \Illuminate\Database\Connection  $connection
     * @return string
     */
    public function compileCreate(Blueprint $blueprint, Fluent $command, Connection $connection)
    {
        if (!isset($blueprint->engine)) {
            $blueprint->engine = 'InnoDB';
        }

        return parent::compileCreate($blueprint, $command, $connection);
    }
}
