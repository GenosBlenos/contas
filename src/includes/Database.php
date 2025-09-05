<?php
require_once __DIR__ . '/Logger.php';
class Database {
    private static $instance = null;
    private $connection;
    private $logger;
    private $queryLog = [];
    private $queryCount = 0;
    private $transactionLevel = 0;
    private $lastQuery;
    private $preparedStatements = [];

    private function __construct() {
        require_once __DIR__ . '/db_config.php';
        $this->logger = Logger::getInstance();
        try {
            $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            // Configurar conexão
            $this->connection->set_charset("utf8mb4");
            $this->connection->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, 1);
            if ($this->connection->connect_error) {
                // Log detalhado do erro
                $msg = "Erro na conexão com o banco de dados: " . $this->connection->connect_error .
                    " | Host: " . DB_HOST .
                    " | User: " . DB_USER .
                    " | DB: " . DB_NAME;
                $this->logger->error($msg);
                throw new Exception($msg);
            }
            // Configurar modo estrito
            $this->query("SET SESSION sql_mode = 'STRICT_ALL_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
            // Configurar timezone
            $this->query("SET time_zone = 'SYSTEM'");
        } catch (Exception $e) {
            $this->logger->error('Erro de conexão com o banco de dados', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Exibe mensagem detalhada para debug
            die("Erro ao conectar ao banco de dados: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function prepare($sql) {
        $this->lastQuery = $sql;
        $startTime = microtime(true);
        
        try {
            $stmt = $this->connection->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Erro ao preparar query: " . $this->connection->error);
            }
            
            $this->logQuery($sql, microtime(true) - $startTime);
            return $stmt;

        } catch (Exception $e) {
            $this->logger->error('Erro ao preparar query', [
                'query' => $sql,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function query($sql) {
        $this->lastQuery = $sql;
        $startTime = microtime(true);
        
        try {
            $result = $this->connection->query($sql);
            
            if ($result === false) {
                throw new Exception("Erro na execução da query: " . $this->connection->error);
            }
            
            $this->logQuery($sql, microtime(true) - $startTime);
            return $result;

        } catch (Exception $e) {
            $this->logger->error('Erro na execução da query', [
                'query' => $sql,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function execute($sql, $params = []) {
        $stmt = $this->prepare($sql);
        
        if (!empty($params)) {
            $types = '';
            $values = [];
            
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } elseif (is_string($param)) {
                    $types .= 's';
                } else {
                    $types .= 'b';
                }
                $values[] = $param;
            }
            
            $stmt->bind_param($types, ...$values);
        }
        
        $success = $stmt->execute();
        
        if (!$success) {
            $this->logger->error('Erro na execução do prepared statement', [
                'query' => $sql,
                'params' => $params,
                'error' => $stmt->error
            ]);
            throw new Exception("Erro na execução da query: " . $stmt->error);
        }
        
        return $stmt;
    }

    public function beginTransaction() {
        if ($this->transactionLevel === 0) {
            $this->connection->begin_transaction();
        }
        $this->transactionLevel++;
        
        $this->logger->debug('Iniciando transação', [
            'level' => $this->transactionLevel
        ]);
    }

    public function commit() {
        if ($this->transactionLevel === 1) {
            $this->connection->commit();
            $this->logger->debug('Commit realizado');
        }
        $this->transactionLevel = max(0, $this->transactionLevel - 1);
    }

    public function rollback() {
        if ($this->transactionLevel === 1) {
            $this->connection->rollback();
            $this->logger->warning('Rollback realizado', [
                'last_query' => $this->lastQuery
            ]);
        }
        $this->transactionLevel = max(0, $this->transactionLevel - 1);
    }

    public function escape($string) {
        return $this->connection->real_escape_string($string);
    }

    public function lastInsertId() {
        return $this->connection->insert_id;
    }

    public function affectedRows() {
        return $this->connection->affected_rows;
    }

    private function logQuery($sql, $executionTime) {
        $this->queryCount++;
        $this->queryLog[] = [
            'sql' => $sql,
            'time' => $executionTime,
            'timestamp' => microtime(true)
        ];

        // Log queries que demoram mais de 1 segundo
        if ($executionTime > 1) {
            $this->logger->warning('Query lenta detectada', [
                'query' => $sql,
                'execution_time' => $executionTime
            ]);
        }
    }

    public function getQueryLog() {
        return $this->queryLog;
    }

    public function getQueryCount() {
        return $this->queryCount;
    }

    public function getAverageQueryTime() {
        if (empty($this->queryLog)) {
            return 0;
        }

        $total = 0;
        foreach ($this->queryLog as $query) {
            $total += $query['time'];
        }

        return $total / count($this->queryLog);
    }

    public function __destruct() {
        // Fechar prepared statements
        foreach ($this->preparedStatements as $stmt) {
            $stmt->close();
        }

        // Fechar conexão
        if ($this->connection) {
            $this->connection->close();
        }
    }
}
