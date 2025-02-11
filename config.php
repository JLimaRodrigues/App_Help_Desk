<?php 

// require_once './.env';

function carregarConstantesAmbiente($caminhoArquivo) {
    if (!file_exists($caminhoArquivo)) {
      return false;
    }
  
    $linhas = file($caminhoArquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($linhas as $linha) {
      if (strpos($linha, '=') === false) {
        continue;
      }
  
      list($chave, $valor) = explode('=', $linha, 2);
      $chave = trim($chave);
      $valor = trim($valor);
  
      // Define a constante com o nome da chave em maiúsculo
      define(strtoupper($chave), $valor);
    }
  
    return true;
  }

  carregarConstantesAmbiente('.env');

/**
 * Class de Conexão 
 * @author Jefferson Lima Rodrigues
 */
class Conexao
{
    private $conexao;
    private static $instance;

    public function __construct() {
        try {
            $this->conexao = new PDO('mysql:host='. DB_HOST .';dbname='. DB_NAME, DB_USER_ROOT, DB_PASS_ROOT);
            $this->conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die('ERROR: '. $e->getMessage()); // Melhorar o tratamento de erros em produção
        }
    }

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new Conexao();
        }
        return self::$instance->conexao; // Retorna a conexão, não a instância.
    }

    public function createTable($tableName, $columns) {
        $sql = "CREATE TABLE IF NOT EXISTS {$tableName} (";
        $columnsSql = [];

        foreach ($columns as $columnName => $columnType) {
            $columnsSql[] = "{$columnName} {$columnType}";
        }

        $sql .= implode(", ", $columnsSql) . ")";

        return $this->execute($sql);
    }


    public function dropTable($tableName) {
        $sql = "DROP TABLE IF EXISTS {$tableName}";
        return $this->execute($sql);
    }

    public function alterTable($tableName, $action, $columnName, $newColumnType = null) {
        $sql = "ALTER TABLE {$tableName} {$action} COLUMN {$columnName}";
        if ($newColumnType) {
            $sql .= " {$newColumnType}";
        }
        return $this->execute($sql);
    }


    public function insert($tableName, $data) {
        $columns = implode(", ", array_keys($data));
        $values = ":" . implode(", :", array_keys($data));
        $sql = "INSERT INTO {$tableName} ({$columns}) VALUES ({$values})";
        return $this->execute($sql, $data);
    }

    public function update($tableName, $data, $where) {
        $updates = [];
        foreach ($data as $key => $value) {
            $updates[] = "{$key} = :{$key}";
        }
        $sql = "UPDATE {$tableName} SET " . implode(", ", $updates) . " WHERE {$where}";
        return $this->execute($sql, $data);
    }

    public function delete($tableName, $where) {
        $sql = "DELETE FROM {$tableName} WHERE {$where}";
        return $this->execute($sql);
    }

    public function select($tableName, $columns = "*", $where = null, $orderBy = null, $limit = null) {
        $sql = "SELECT {$columns} FROM {$tableName}";
        if ($where) {
            $sql .= " WHERE {$where}";
        }
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        return $this->execute($sql);
    }


    public function execute($sql, $params = []) {
        try {
            $stmt = $this->conexao->prepare($sql);
            foreach ($params as $key => $value) {
                $paramType = PDO::PARAM_STR;
                if (is_int($value)) {
                    $paramType = PDO::PARAM_INT;
                } elseif (is_null($value)) {
                    $paramType = PDO::PARAM_NULL;
                } elseif (is_bool($value)) {
                    $value = $value ? 1 : 0; // Converte booleanos para 1 ou 0
                    $paramType = PDO::PARAM_INT;
                }
                $stmt->bindValue(":{$key}", $value, $paramType);
            }
            $stmt->execute();

            $this->logQuery($sql, $params); // Log da query executada

            if (stripos($sql, "SELECT") === 0) { // Verifica se é uma query de SELECT
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            return true; // Retorna true para outras operações (INSERT, UPDATE, DELETE, etc.)

        } catch (PDOException $e) {
            $this->logError("Erro na query: " . $e->getMessage() . " SQL: " . $sql);
            throw $e; // Re-lança a exceção após logar
        }
    }

    // Métodos para log
    private function logQuery($sql, $params) {
        $this->log[] = [
            'type' => 'query',
            'sql' => $sql,
            'params' => $params,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    private function logError($message) {
        $this->log[] = [
            'type' => 'error',
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    public function getLog() {
        return $this->log;
    }

    public function beginTransaction() {
        return $this->conexao->beginTransaction();
    }

    public function commit() {
        return $this->conexao->commit();
    }

    public function rollback() {
        return $this->conexao->rollBack();
    }

}

$conexao = Conexao::getInstance();

$db = new Conexao();

return $db;

?>