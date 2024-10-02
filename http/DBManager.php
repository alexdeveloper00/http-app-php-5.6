<?php 
namespace Http;
use PDO;

class DBManager {
    private $m_pdo = [];
    private $m_connection_name;
    private static $m_instances = [];

    private $m_result;
    private $m_stmt;

    public static function getInstance($connection_name = 'configurator') {
        if (!isset(self::$m_instances[$connection_name]) || self::$m_instances[$connection_name] === NULL) {
            self::$m_instances[$connection_name] = new DBManager($connection_name);
        }

        return self::$m_instances[$connection_name];
    }

    private function __construct($connection_name = 'configurator') {
        global $config;

        if (!isset($config['db'][$connection_name])) {
            die('Connection fault.');
        }

        $this->m_connection_name = $connection_name;
        
        $conf = $config['db'][$connection_name];
        $pdo = new PDO(sprintf('%s=%s;Database=%s', $conf['type'], $conf['host'], $conf['dbname']), $conf['user'], $conf['password']);
        $pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->m_pdo[$connection_name] = $pdo;
    }

    public function query($sQuery, $params = []) {
        $pdo = $this->m_pdo[$this->m_connection_name];

        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare($sQuery);
            $result = $stmt->execute($params);

            $this->m_result = $result;
            $this->m_stmt = $stmt;

            $pdo->commit();
        } catch (\PDOException $e) {
            $pdo->rollBack();
            $this->m_result = NULL;
            $this->m_stmt = NULL;
        }

        return $this;
    }

    public function directQuery($sQuery, $vtParams = []) {
        $pdo = $this->m_pdo[$this->m_connection_name];

        $stmt = $pdo->prepare($sQuery);
        $result = $stmt->execute($vtParams);

        $this->m_result = $result;
        $this->m_stmt = $stmt;
        return $this;
    } 

    public function fetch() {
        $toReturn = NULL;

        if ($this->m_stmt && $this->m_result) {
            $toReturn = $this->m_stmt->fetch();
        }

        $this->reset();
        return $toReturn;
    }

    public function fetchAll() {
        $toReturn = NULL;

        if ($this->m_stmt && $this->m_result) {
            $toReturn = $this->m_stmt->fetchAll();
        }

        $this->reset();
        return $toReturn;
    }

    private function reset() {
        $this->m_stmt = NULL;
        $this->m_result = NULL;
    }

    public function getResult() {
        return $this->m_result;
    }
}