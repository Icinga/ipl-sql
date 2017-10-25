<?php

namespace ipl\Sql;

/**
 * The SQL helper provides a set of static methods for quoting and escaping identifiers to make their use safe in SQL
 * queries or fragments
 */
class Sql
{
    /**
     * SQL AND operator
     */
    const all = 'AND';

    /**
     * SQL OR operator
     */
    const any = 'OR';

    /**
     * Quote an identifier for use in an SQL statement by the given quote character(s)
     *
     * If you allow user input in your queries or if you are using special identifiers like reserved keywords,
     * use this method to quote SQL statement names such as table or field names by the given quote character(s).
     *
     * If the quote character is a single character string, this character is used for both the opening and the
     * closing quote. Else, the first character is used for the opening quote and the second one for the closing quote.
     *
     * The default quote character is the double quote (") which is used by databases that behave close to ANSI SQL.
     *
     * For MySQL the default identifier quote character is the backtick (`). If you have to quote identifiers for MySQL,
     * you may either provide the backtick as quote character to this method, or set the MySQL SQL mode to support
     * ANSI quotes which then treats " as an identifier quote character.
     *
     * For Microsoft SQL Server the default quote character sequence is []. If you have to quote identifiers for
     * MSSQLServer, you may either provide the [] quote character sequence to this method, or set MSSQLServer option
     * QUOTED_IDENTIFIER to ON which then treats " as an identifier quote character.
     *
     * @param   string  $identifier         The identifier to quote
     * @param   string  $quoteCharacter     The quote character(s)
     *
     * @return  string                      The quoted identifier
     */
    public static function quoteIdentifier($identifier, $quoteCharacter = '"')
    {
        if (strlen($quoteCharacter) === 1) {
            return $quoteCharacter . $identifier . $quoteCharacter;
        } else {
            return $quoteCharacter[0] . $identifier . $quoteCharacter[1];
        }
    }
}
