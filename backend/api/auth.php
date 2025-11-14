<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/database.php';

class AuthAPI {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function authenticate() {
        try {
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);
            
            if (!$input || !isset($input['username']) || !isset($input['password'])) {
                throw new Exception('Username e password são obrigatórios');
            }
            
            $username = trim($input['username']);
            $password = $input['password'];
            
            $conn = $this->db->getConnection();
            if (!$conn) {
                throw new Exception('Erro de conexão com o banco');
            }
            
            // Buscar usuário no banco
            $stmt = $conn->prepare("SELECT id, username, password, nome FROM usuarios WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if (!$user) {
                throw new Exception('Usuário não encontrado');
            }
            
            // Verificar senha
            if (password_verify($password, $user['password'])) {
                echo json_encode([
                    'success' => true,
                    'user' => [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'name' => $user['nome']
                    ]
                ]);
            } else {
                throw new Exception('Senha incorreta');
            }
            
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $api = new AuthAPI();
    $api->authenticate();
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
}
?>