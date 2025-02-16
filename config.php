<?php 

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
    private $sql;
    private $params = [];

    public function __construct() {
        try {
            $this->conexao = new PDO('mysql:host='. DB_HOST .';dbname='. DB_NAME . ';charset=utf8mb4', DB_USER_ROOT, DB_PASS_ROOT);
            $this->conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die('ERROR: '. $e->getMessage()); // Melhorar o tratamento de erros em produção
        }
    }

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new Conexao();
        }
        return self::$instance;
    }

    public function getLastInsertId() {
        return $this->conexao->lastInsertId();
    }

    public function select($colunas = "*")
    {
        $this->sql = "SELECT " . (is_array($colunas) ? implode(", ", $colunas) : $colunas) . " FROM ";
        return $this;
    }

    public function from($tabela)
    {
        $this->sql .= $tabela;
        return $this;
    }

    public function join($tabela, $condicao, $tipo = "INNER") {
        $this->sql .= " " . $tipo . " JOIN " . $tabela . " ON " . $condicao;
        return $this;
    }

    public function where($condicao, $params = []) {
        $this->sql .= " WHERE " . $condicao;
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    public function orderBy($campo, $direcao = "ASC") {
        $this->sql .= " ORDER BY " . $campo . " " . $direcao;
        return $this;
    }

    public function limit($limite) {
        $this->sql .= " LIMIT " . $limite;
        return $this;
    }

    public function offset($inicio) {
        $this->sql .= " OFFSET " . $inicio;
        return $this;
    }

    public function groupBy($campo) {
        $this->sql .= " GROUP BY " . $campo;
        return $this;
    }

    public function having($condicao, $params = []) {
        $this->sql .= " HAVING " . $condicao;
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    public function createTable($tableName, $columns, $foreignKeys = []) {
        $sql = "CREATE TABLE IF NOT EXISTS {$tableName} (";
        $columnsSql = [];

        foreach ($columns as $columnName => $columnType) {
            if (is_array($columnType) && isset($columnType['foreign_key'])) {
                $fk = $columnType['foreign_key'];
                $columnsSql[] = "{$columnName} {$columnType['type']}";
            } else {
                $columnsSql[] = "{$columnName} {$columnType}";
            }
        }

        $sql .= implode(", ", $columnsSql);

        // Tratamento de múltiplas FKs (novo)
        if (!empty($foreignKeys)) {
            $fkSql = [];
            foreach ($foreignKeys as $fk) {
                $fkSql[] = "FOREIGN KEY ({$fk['column']}) REFERENCES {$fk['table']}({$fk['reference']})";
            }
            $sql .= ", " . implode(", ", $fkSql);
        }

        $sql .= ")";

        $this->sql = $sql;
        return $this->execute();
    }


    public function dropTable($tableName) {
        $sql = "DROP TABLE IF EXISTS {$tableName}";

        $this->sql = $sql;
        return $this->execute();
    }

    public function alterTable($tableName, $action, $columnName, $newColumnType = null) {
        $sql = "ALTER TABLE {$tableName} {$action} COLUMN {$columnName}";
        if ($newColumnType) {
            $sql .= " {$newColumnType}";
        }

        $this->sql = $sql;
        return $this->execute();
    }


    public function insert($tableName, $data) {
        $columns = implode(", ", array_keys($data));
        $values = ":" . implode(", :", array_keys($data));
        $sql = "INSERT INTO {$tableName} ({$columns}) VALUES ({$values})";

        $this->sql = $sql;
        $this->params = $data;
        return $this->execute();
    }

    public function update($tableName, $data, $where) {
        $updates = [];
        foreach ($data as $key => $value) {
            $updates[] = "{$key} = :{$key}";
        }
        $sql = "UPDATE {$tableName} SET " . implode(", ", $updates) . " WHERE {$where}";

        $this->sql = $sql;
        $this->params = $data;

        return $this->execute();
    }

    public function delete($tableName, $where) {
        $sql = "DELETE FROM {$tableName} WHERE {$where}";

        $this->sql = $sql;
        return $this->execute();
    }

    public function execute() {
        if (empty($this->sql)) {
            $this->logError("Tentativa de executar uma query vazia.");
            throw new Exception("Query was empty");
        }

        try {
            $stmt = $this->conexao->prepare($this->sql);

            foreach ($this->params as $key => $value) {
                $paramType = PDO::PARAM_STR;
                if (is_int($value)) {
                    $paramType = PDO::PARAM_INT;
                } elseif (is_null($value)) {
                    $paramType = PDO::PARAM_NULL;
                } elseif (is_bool($value)) {
                    $value = $value ? 1 : 0;
                    $paramType = PDO::PARAM_INT;
                }
                $stmt->bindValue(":" . $key, $value, $paramType);
            }

            $stmt->execute();
            $this->logQuery($this->sql, $this->params);

            if (stripos($this->sql, "SELECT") === 0) {
                return $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }
            return true;

        } finally {
            $this->sql = null;
            $this->params = [];
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

$db = Conexao::getInstance();

?>