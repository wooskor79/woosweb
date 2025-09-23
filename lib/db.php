<?php
/**
 * lib/db.php
 * 데이터베이스 연결(PDO)을 관리합니다.
 */

function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        // 실제 운영 환경에서는 사용자에게 상세 오류를 노출하지 않는 것이 좋습니다.
        throw new PDOException($e->getMessage(), (int)$e->getCode());
    }
    return $pdo;
}

/**
 * SELECT 쿼리를 실행하고 모든 결과를 반환합니다.
 * @param string $sql - 실행할 SQL 쿼리
 * @param array $params - 바인딩할 파라미터 배열
 * @return array
 */
function db_query_all(string $sql, array $params = []): array {
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * SELECT 쿼리를 실행하고 하나의 결과만 반환합니다.
 * @param string $sql
 * @param array $params
 * @return array|null
 */
function db_query_one(string $sql, array $params = []): ?array {
    $row = db_query_all($sql, $params);
    return $row[0] ?? null;
}

/**
 * INSERT, UPDATE, DELETE 등 결과를 반환하지 않는 쿼리를 실행합니다.
 * @param string $sql
 * @param array $params
 * @return bool
 */
function db_execute(string $sql, array $params = []): bool {
    $stmt = db()->prepare($sql);
    return $stmt->execute($params);
}