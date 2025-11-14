<?php
/**
 * Configuração do Banco de Dados - Sistema SAEP
 * Conexão com MySQL usando PDO
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'saep_db';
    private $username = 'root';
    private $password = '';
    private $conn;
    
    /**
     * Conectar ao banco de dados
     * @return PDO|null
     */
    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Erro de conexão: " . $e->getMessage();
        }
        
        return $this->conn;
    }
    
    /**
     * Executar stored procedure
     * @param string $procedure Nome da procedure
     * @param array $params Parâmetros
     * @return array
     */
    public function callProcedure($procedure, $params = []) {
        try {
            $placeholders = str_repeat('?,', count($params));
            $placeholders = rtrim($placeholders, ',');
            
            $sql = "CALL {$procedure}({$placeholders})";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            throw new Exception("Erro ao executar procedure: " . $e->getMessage());
        }
    }
}

/**
 * Configurações gerais do sistema
 */
class Config {
    // Configurações de segurança
    const JWT_SECRET = 'saep_secret_key_2025';
    const JWT_EXPIRE = 86400; // 24 horas
    
    // Configurações da aplicação
    const APP_NAME = 'Sistema SAEP';
    const APP_VERSION = '1.0.0';
    const TIMEZONE = 'America/Sao_Paulo';
    
    // Configurações de upload
    const UPLOAD_PATH = '../uploads/';
    const MAX_FILE_SIZE = 5242880; // 5MB
    
    // Inicializar configurações
    public static function init() {
        date_default_timezone_set(self::TIMEZONE);
        
        // Headers CORS
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json; charset=utf-8');
        
        // Tratar OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }
}
?>