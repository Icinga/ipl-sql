<?php

namespace ipl\Sql\Adapter;

use ipl\Sql\Connection;

interface AdapterInterface
{
    /**
     * Set the client time zone
     *
     * @param   Connection  $db
     *
     * @return  $this
     */
    public function setClientTimezone(Connection $db);

    /**
     * Quote a string so that it can be safely used as table or column name, even if it is a reserved name
     *
     * The quote character depends on the underlying database adapter that is being used.
     *
     * @param   string  $identifier
     *
     * @return  string
     */
    public function quoteIdentifier($identifier);
}
