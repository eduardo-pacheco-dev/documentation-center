<?php

namespace App\Services\Backups;

/**
 * Splits a SQL script into the individual statements that it contains.
 *
 * The splitter is aware of single quotes, double quotes and backticks, as well
 * as line and block comments, so that semicolons inside string literals,
 * quoted identifiers or comments never start a new statement.
 */
class SqlStatementSplitter
{
    /**
     * Split the given script into executable statements.
     *
     * @return array<int, string>
     */
    public function split(string $sql): array
    {
        $statements = [];
        $current = '';
        $length = strlen($sql);
        $quote = null;

        for ($index = 0; $index < $length; $index++) {
            $character = $sql[$index];

            if ($quote !== null) {
                $current .= $character;

                if ($character === '\\' && $quote !== '`') {
                    if ($index + 1 < $length) {
                        $current .= $sql[++$index];
                    }

                    continue;
                }

                if ($character === $quote) {
                    if ($index + 1 < $length && $sql[$index + 1] === $quote) {
                        $current .= $sql[++$index];

                        continue;
                    }

                    $quote = null;
                }

                continue;
            }

            if ($character === "'" || $character === '"' || $character === '`') {
                $quote = $character;
                $current .= $character;

                continue;
            }

            if ($character === '/' && ($sql[$index + 1] ?? '') === '*') {
                $index = $this->consumeBlockComment($sql, $index, $current);

                continue;
            }

            if ($character === '#' || $this->startsLineComment($sql, $index)) {
                $index = $this->consumeLineComment($sql, $index, $current);

                continue;
            }

            if ($character === ';') {
                $this->push($statements, $current);

                continue;
            }

            $current .= $character;
        }

        $this->push($statements, $current);

        return $statements;
    }

    /**
     * Append a block comment verbatim and return the index of its last character.
     */
    private function consumeBlockComment(string $sql, int $index, string &$current): int
    {
        $end = strpos($sql, '*/', $index + 2);
        $end = $end === false ? strlen($sql) : $end + 2;

        $current .= substr($sql, $index, $end - $index);

        return $end - 1;
    }

    /**
     * Append a line comment verbatim and return the index of its last character.
     */
    private function consumeLineComment(string $sql, int $index, string &$current): int
    {
        $end = strpos($sql, "\n", $index);
        $end = $end === false ? strlen($sql) : $end;

        $current .= substr($sql, $index, $end - $index);

        return $end - 1;
    }

    /**
     * Determine whether the given position starts a "-- " style line comment.
     */
    private function startsLineComment(string $sql, int $index): bool
    {
        if (($sql[$index + 1] ?? '') !== '-') {
            return false;
        }

        $next = $sql[$index + 2] ?? '';

        return $next === '' || $next === ' ' || $next === "\t" || $next === "\n" || $next === "\r";
    }

    /**
     * Trim the buffer and, when not empty, add it as a statement.
     *
     * @param  array<int, string>  $statements
     */
    private function push(array &$statements, string &$current): void
    {
        $statement = trim($current);

        if ($statement !== '') {
            $statements[] = $statement;
        }

        $current = '';
    }
}
