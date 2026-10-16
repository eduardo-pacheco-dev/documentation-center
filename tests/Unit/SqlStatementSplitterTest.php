<?php

use App\Services\Backups\SqlStatementSplitter;

test('it splits statements separated by semicolons', function () {
    $statements = (new SqlStatementSplitter)->split("SELECT 1;\nSELECT 2;");

    expect($statements)->toBe(['SELECT 1', 'SELECT 2']);
});

test('it keeps semicolons that live inside string literals', function () {
    $statements = (new SqlStatementSplitter)->split("INSERT INTO t (a) VALUES ('a;b'); SELECT 1;");

    expect($statements)->toBe(["INSERT INTO t (a) VALUES ('a;b')", 'SELECT 1']);
});

test('it keeps semicolons inside quoted identifiers', function () {
    $statements = (new SqlStatementSplitter)->split('SELECT `a;b` FROM t; SELECT 2;');

    expect($statements)->toBe(['SELECT `a;b` FROM t', 'SELECT 2']);
});

test('it understands escaped and doubled quotes', function () {
    $sql = "INSERT INTO t (a) VALUES ('It\\'s; ok'); INSERT INTO t (a) VALUES ('a''b; c');";

    $statements = (new SqlStatementSplitter)->split($sql);

    expect($statements)->toHaveCount(2)
        ->and($statements[0])->toBe("INSERT INTO t (a) VALUES ('It\\'s; ok')")
        ->and($statements[1])->toBe("INSERT INTO t (a) VALUES ('a''b; c')");
});

test('it ignores semicolons inside comments', function () {
    $sql = "SELECT 1 /* ; */; SELECT 2 -- ;\n; SELECT 3;";

    $statements = (new SqlStatementSplitter)->split($sql);

    expect($statements)->toHaveCount(3)
        ->and($statements[0])->toBe('SELECT 1 /* ; */')
        ->and($statements[1])->toContain('SELECT 2')
        ->and($statements[2])->toBe('SELECT 3');
});

test('it keeps executable conditional comments intact', function () {
    $sql = 'CREATE TABLE t (a INT) /*!50100 PARTITION BY HASH (a) */; SELECT 1;';

    $statements = (new SqlStatementSplitter)->split($sql);

    expect($statements)->toBe([
        'CREATE TABLE t (a INT) /*!50100 PARTITION BY HASH (a) */',
        'SELECT 1',
    ]);
});

test('it returns the trailing statement without a terminating semicolon', function () {
    $statements = (new SqlStatementSplitter)->split('SELECT 1; SELECT 2');

    expect($statements)->toBe(['SELECT 1', 'SELECT 2']);
});

test('it ignores empty statements', function () {
    $statements = (new SqlStatementSplitter)->split("SELECT 1;;\n;\n");

    expect($statements)->toBe(['SELECT 1']);
});
