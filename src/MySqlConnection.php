<?php

namespace Axn\Illuminate\Database;

use Illuminate\Database\MySqlConnection as BaseMySqlConnection;
use Axn\Illuminate\Database\Query\Grammars\MySqlGrammar as QueryGrammar;
use Axn\Illuminate\Database\Schema\Grammars\MySqlGrammar as SchemaGrammar;

class MySqlConnection extends BaseMySqlConnection
{
    /**
     * Get the default query grammar instance.
     *
     * @return \Illuminate\Database\Query\Grammars\MySqlGrammar
     */
    protected function getDefaultQueryGrammar()
    {
        return $this->withTablePrefix(new QueryGrammar);
    }

    /**
     * Get the default schema grammar instance.
     *
     * @return \Illuminate\Database\Schema\Grammars\MySqlGrammar
     */
    protected function getDefaultSchemaGrammar()
    {
        return $this->withTablePrefix(new SchemaGrammar);
    }
}
