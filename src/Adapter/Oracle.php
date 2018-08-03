<?php

namespace ipl\Sql\Adapter;

use ipl\Sql\Connection;

class Oracle extends BaseAdapter
{
    public function setClientTimezone(Connection $db)
    {
        $db->exec('ALTER SESSION SET TIME_ZONE = ?', $this->getTimezoneOffset());

        return $this;
    }
}
