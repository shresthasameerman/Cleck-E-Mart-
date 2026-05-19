<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/offline_store.php';

function db_driver(): string
{
    $driver = strtolower((string) (getenv('DB_DRIVER') ?: 'oracle'));
    return in_array($driver, ['offline', 'oracle'], true) ? $driver : 'oracle';
}

function db_is_offline(): bool
{
    return db_driver() !== 'oracle';
}

function db_connect()
{
    static $conn = null;

    if (db_is_offline()) {
        return null;
    }

    if ($conn !== null) {
        return $conn;
    }

    if (!extension_loaded('oci8') || !function_exists('oci_connect')) {
        throw new RuntimeException('OCI8 extension is not loaded in this PHP runtime. Use DB_DRIVER=offline for local mode, or enable php-oci8.');
    }

    $username = getenv('ORACLE_USERNAME') ?: 'CLECK';
    $password = getenv('ORACLE_PASSWORD') ?: 'Oracle123#Apex';
    $connectionString = getenv('ORACLE_CONNECTION_STRING') ?: 'localhost:1521/XEPDB1';

    $conn = @oci_connect($username, $password, $connectionString, 'AL32UTF8');
    if ($conn === false) {
        $conn = null;
        $error = oci_error();
        throw new RuntimeException('Oracle connection failed: ' . ($error['message'] ?? 'unknown error'));
    }

    return $conn;
}

function db_parse(string $sql)
{
    if (db_is_offline()) {
        throw new RuntimeException('SQL parser is not used in offline mode.');
    }

    if (!function_exists('oci_parse')) {
        throw new RuntimeException('OCI8 parser function is unavailable. Ensure the oci8 extension is installed and enabled.');
    }

    $statement = oci_parse(db_connect(), $sql);
    if ($statement === false) {
        $error = oci_error(db_connect());
        throw new RuntimeException('Failed to parse SQL: ' . ($error['message'] ?? 'unknown error'));
    }

    return $statement;
}

function db_execute_statement($statement, array $binds = []): bool
{
    if (db_is_offline()) {
        throw new RuntimeException('Statement execution is not used in offline mode.');
    }

    foreach ($binds as $name => $value) {
        ${$name} = $value;
        if (!oci_bind_by_name($statement, ':' . $name, ${$name})) {
            $error = oci_error($statement);
            throw new RuntimeException('Failed to bind parameter :' . $name . ': ' . ($error['message'] ?? 'unknown error'));
        }
    }

    $ok = @oci_execute($statement);
    if ($ok === false) {
        $error = oci_error($statement);
        throw new RuntimeException('SQL execution failed: ' . ($error['message'] ?? 'unknown error'));
    }

    return true;
}

function db_fetch_all(string $sql, array $binds = []): array
{
    if (db_is_offline()) {
        throw new RuntimeException('db_fetch_all SQL path is disabled in offline mode.');
    }

    $statement = db_parse($sql);
    db_execute_statement($statement, $binds);

    $rows = [];
    while (($row = oci_fetch_array($statement, OCI_ASSOC | OCI_RETURN_LOBS)) !== false) {
        $rows[] = $row;
    }

    oci_free_statement($statement);

    return $rows;
}

function db_fetch_one(string $sql, array $binds = []): ?array
{
    if (db_is_offline()) {
        throw new RuntimeException('db_fetch_one SQL path is disabled in offline mode.');
    }

    $statement = db_parse($sql);
    db_execute_statement($statement, $binds);

    $row = oci_fetch_array($statement, OCI_ASSOC | OCI_RETURN_LOBS) ?: null;
    oci_free_statement($statement);

    return $row;
}

function db_execute(string $sql, array $binds = []): bool
{
    if (db_is_offline()) {
        throw new RuntimeException('db_execute SQL path is disabled in offline mode.');
    }

    $statement = db_parse($sql);
    $ok = db_execute_statement($statement, $binds);
    oci_free_statement($statement);

    return $ok;
}

function db_next_id(string $table, string $column): int
{
    if (db_is_offline()) {
        throw new RuntimeException('db_next_id SQL path is disabled in offline mode.');
    }

    if (!preg_match('/^[a-zA-Z_\"]+$/', $table) || !preg_match('/^[a-zA-Z_\"]+$/', $column)) {
        throw new InvalidArgumentException('Unsafe table or column identifier provided.');
    }

    $clean_table = trim($table, '"');
    
    // Calculate MAX(id) + 1 directly instead of using sequences
    $sql = 'SELECT NVL(MAX(' . $column . '), 0) + 1 AS NEXT_ID FROM ' . $table;
    $row = db_fetch_one($sql);

    if ($row === null || !isset($row['NEXT_ID'])) {
        throw new RuntimeException('Unable to calculate next id for ' . $table . '.');
    }

    return (int) $row['NEXT_ID'];
}

function db_begin(): void
{
    if (db_is_offline()) {
        return;
    }

    if (!@oci_execute(oci_parse(db_connect(), 'BEGIN NULL; END;'), OCI_NO_AUTO_COMMIT)) {
        // No-op: write operations explicitly control commit via db_commit.
    }
}

function db_commit(): void
{
    if (db_is_offline()) {
        return;
    }

    @oci_commit(db_connect());
}

function db_rollback(): void
{
    if (db_is_offline()) {
        return;
    }

    @oci_rollback(db_connect());
}
