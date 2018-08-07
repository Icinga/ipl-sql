<?php

namespace ipl\Sql\Adapter;

use ipl\Sql\Config;
use ipl\Sql\Connection;

interface AdapterInterface
{
    /**
     * Get the DSN string from the given connection configuration
     *
     * @param   Config  $config
     *
     * @return  string
     */
    public function getDsn(Config $config);

    /**
     * Get the PDO connect options based on the specified connection configuration
     *
     * @param   Config  $config
     *
     * @return  array
     */
    public function getOptions(Config $config);

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
