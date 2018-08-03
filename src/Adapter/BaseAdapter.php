<?php

namespace ipl\Sql\Adapter;

use DateTime;
use DateTimeZone;
use ipl\Sql\Connection;

abstract class BaseAdapter implements AdapterInterface
{
    /**
     * Quote character to use for quoting identifiers
     *
     * The default quote character is the double quote (") which is used by databases that behave close to ANSI SQL.
     *
     * @var array
     */
    protected $quoteCharacter = ['"', '"'];

    /**
     * Character to use for escaping quote characters
     *
     * @var string
     */
    protected $escapeCharacter = '\\"';

    public function setClientTimezone(Connection $db)
    {
        return $this;
    }

    public function quoteIdentifier($identifier)
    {
        if ($identifier === '*') {
            return $identifier;
        }

        $identifier = str_replace($this->quoteCharacter[0], $this->escapeCharacter, $identifier);

        return $this->quoteCharacter[0]
            . str_replace('.', "{$this->quoteCharacter[0]}.{$this->quoteCharacter[1]}", $identifier)
            . $this->quoteCharacter[1];
    }

    protected function getTimezoneOffset()
    {
        $tz = new DateTimeZone(date_default_timezone_get());
        $offset = $tz->getOffset(new DateTime());
        $prefix = $offset >= 0 ? '+' : '-';
        $offset = abs($offset);

        $hours = (int) floor($offset / 3600);
        $minutes = (int) floor(($offset % 3600) / 60);

        return sprintf('%s%d:%02d', $prefix, $hours, $minutes);
    }
}
