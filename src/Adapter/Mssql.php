<?php

namespace ipl\Sql\Adapter;

use ipl\Sql\Config;
use PDO;
use RuntimeException;

class Mssql extends BaseAdapter
{
    protected $quoteCharacter = ['[', ']'];

    protected $escapeCharacter = '[[]';

    protected $limitRequiresOrder = true;

    public function getDsn(Config $config)
    {
        $drivers = array_intersect(['dblib', 'mssql', 'sybase', 'freetds'], PDO::getAvailableDrivers());

        if (empty($drivers)) {
            throw new RuntimeException('No PDO driver available for connecting to a Microsoft SQL Server');
        }

        $dsn = "{$drivers[0]}:host={$config->host}";

        if (! empty($config->port)) {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $seperator = ',';
            } else {
                $seperator = ':';
            }

            $dsn .= "{$seperator}{$config->port}";
        }

        $dsn .= ";dbname={$config->dbname}";

        if (! empty($config->charset)) {
            $dsn .= ";charset={$config->charset}";
        }

        return $dsn;
    }

    public function renderLimitReturnedRows($limit, $offset)
    {
        if ($offset === null && $limit === null) {
            return '';
        }

        $limit = (int) $limit;
        $offset = (int) $offset;

        return "OFFSET $offset ROWS FETCH NEXT $limit ROWS ONLY";
    }
}
