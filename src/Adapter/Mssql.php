<?php

namespace ipl\Sql\Adapter;

class Mssql extends BaseAdapter
{
    protected $quoteCharacter = ['[', ']'];

    protected $escapeCharatcer = '[[]';
}
