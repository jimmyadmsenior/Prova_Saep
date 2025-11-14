<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/database.php';

class ProductAPI {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        switch ($method) {
            case 'GET':
                $this->getProducts();
                break;
            case 'POST':
                $this->createProduct();
                break;
            case 'PUT':
                $this->updateProduct();
                break;
            case 'DELETE':
                $this->deleteProduct();
                break;
            default:
                http_response_code(405);
                echo json_encode(['error' => 'Método não permitido']);
        }
    }
    
    private function getProducts() {
        try {
            $conn = $this->db->getConnection();
            if (!$conn) {
                throw new Exception('Erro de conexão com o banco');
            }
            
            $stmt = $conn->prepare("
                SELECT p.id, p.nome, p.descricao, p.categoria_id, p.unidade_medida, 
                       p.quantidade_disponivel, p.estoque_minimo, p.preco_unitario, 
                       p.data_validade, p.data_criacao, c.nome as categoria_nome
                FROM produtos p
                LEFT JOIN categorias c ON p.categoria_id = c.id
                ORDER BY p.nome
            ");
            
            $stmt->execute();
            $products = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'data' => $products
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    private function createProduct() {
        try {
            $rawInput = file_get_contents('php://input');
            error_log('Dados recebidos na API: ' . $rawInput);
            
            $input = json_decode($rawInput, true);
            
            if (!$input) {
                throw new Exception('Dados JSON inválidos: ' . json_last_error_msg());
            }
            
            if (!isset($input['nome']) || empty(trim($input['nome']))) {
                throw new Exception('Nome do produto é obrigatório');
            }
            
            error_log('Produto a ser criado: ' . print_r($input, true));
            
            $conn = $this->db->getConnection();
            if (!$conn) {
                throw new Exception('Erro de conexão com o banco');
            }
            
            $categoriaId = $this->getCategoriaId($conn, $input['categoria'] ?? 'Outros');
            
            $stmt = $conn->prepare("
                INSERT INTO produtos (nome, descricao, categoria_id, preco_unitario, quantidade_disponivel, estoque_minimo, unidade_medida, data_criacao)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $result = $stmt->execute([
                trim($input['nome']),
                trim($input['descricao'] ?? ''),
                $categoriaId,
                floatval($input['preco_unitario'] ?? 0),
                intval($input['quantidade_estoque'] ?? 0),
                intval($input['estoque_minimo'] ?? 5),
                'unidade'
            ]);
            
            if (!$result) {
                throw new Exception('Falha ao inserir produto no banco');
            }
            
            $productId = $conn->lastInsertId();
            error_log('Produto criado com ID: ' . $productId);
            
            if (!empty($input['quantidade_estoque']) && $input['quantidade_estoque'] > 0) {
                $this->registrarMovimentacao($conn, $productId, 'entrada', $input['quantidade_estoque'], 'Estoque inicial');
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Produto criado com sucesso',
                'id' => $productId,
                'data' => $input
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    private function updateProduct() {
        try {
            $rawInput = file_get_contents('php://input');
            error_log('Dados de atualização recebidos: ' . $rawInput);
            
            $input = json_decode($rawInput, true);
            
            if (!$input) {
                throw new Exception('Dados JSON inválidos: ' . json_last_error_msg());
            }
            
            if (!isset($input['id']) || empty($input['id'])) {
                throw new Exception('ID do produto é obrigatório para atualização');
            }
            
            if (!isset($input['nome']) || empty(trim($input['nome']))) {
                throw new Exception('Nome do produto é obrigatório');
            }
            
            $conn = $this->db->getConnection();
            if (!$conn) {
                throw new Exception('Erro de conexão com o banco');
            }
            
            $categoriaId = $this->getCategoriaId($conn, $input['categoria'] ?? 'Outros');
            
            $stmt = $conn->prepare("SELECT quantidade_disponivel FROM produtos WHERE id = ?");
            $stmt->execute([$input['id']]);
            $produtoAtual = $stmt->fetch();
            
            if (!$produtoAtual) {
                throw new Exception('Produto não encontrado');
            }
            
            $quantidadeAtual = $produtoAtual['quantidade_disponivel'];
            $novaQuantidade = intval($input['stock'] ?? $input['quantidade_estoque'] ?? 0);
            
            $stmt = $conn->prepare("
                UPDATE produtos SET 
                    nome = ?, 
                    descricao = ?, 
                    categoria_id = ?, 
                    preco_unitario = ?, 
                    quantidade_disponivel = ?, 
                    estoque_minimo = ?
                WHERE id = ?
            ");
            
            $result = $stmt->execute([
                trim($input['nome']),
                trim($input['description'] ?? $input['descricao'] ?? ''),
                $categoriaId,
                floatval($input['price'] ?? $input['preco_unitario'] ?? 0),
                $novaQuantidade,
                intval($input['estoque_minimo'] ?? 5),
                $input['id']
            ]);
            
            if (!$result) {
                throw new Exception('Falha ao atualizar produto no banco');
            }
            
            if ($quantidadeAtual != $novaQuantidade) {
                $diferenca = $novaQuantidade - $quantidadeAtual;
                $tipo = $diferenca > 0 ? 'entrada' : 'saida';
                $quantidade = abs($diferenca);
                $motivo = 'Ajuste de estoque via edição';
                
                $this->registrarMovimentacao($conn, $input['id'], $tipo, $quantidade, $motivo);
            }
            
            error_log('Produto atualizado com sucesso: ID ' . $input['id']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Produto atualizado com sucesso',
                'id' => $input['id']
            ]);
            
        } catch (Exception $e) {
            error_log('Erro ao atualizar produto: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    private function deleteProduct() {
        try {
            $id = $_GET['id'] ?? null;
            
            if (!$id) {
                throw new Exception('ID do produto é obrigatório para exclusão');
            }
            
            $conn = $this->db->getConnection();
            if (!$conn) {
                throw new Exception('Erro de conexão com o banco');
            }
            
            $stmt = $conn->prepare("SELECT id, nome FROM produtos WHERE id = ?");
            $stmt->execute([$id]);
            $produto = $stmt->fetch();
            
            if (!$produto) {
                throw new Exception('Produto não encontrado');
            }
            
            $stmt = $conn->prepare("DELETE FROM movimentacoes_estoque WHERE produto_id = ?");
            $stmt->execute([$id]);
            
            $stmt = $conn->prepare("DELETE FROM produtos WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            if (!$result) {
                throw new Exception('Falha ao excluir produto do banco');
            }
            
            error_log('Produto excluído com sucesso: ID ' . $id);
            
            echo json_encode([
                'success' => true,
                'message' => 'Produto excluído com sucesso'
            ]);
            
        } catch (Exception $e) {
            error_log('Erro ao excluir produto: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    private function getCategoriaId($conn, $nomeCategoria) {
        $stmt = $conn->prepare("SELECT id FROM categorias WHERE nome = ?");
        $stmt->execute([$nomeCategoria]);
        $categoria = $stmt->fetch();
        
        if ($categoria) {
            return $categoria['id'];
        }
        
        $stmt = $conn->prepare("INSERT INTO categorias (nome) VALUES (?)");
        $stmt->execute([$nomeCategoria]);
        return $conn->lastInsertId();
    }
    
    private function registrarMovimentacao($conn, $produtoId, $tipo, $quantidade, $observacoes = '') {
        $stmt = $conn->prepare("
            INSERT INTO movimentacoes_estoque (produto_id, tipo_movimentacao, quantidade, data_movimentacao, observacoes)
            VALUES (?, ?, ?, NOW(), ?)
        ");
        
        $stmt->execute([$produtoId, $tipo, $quantidade, $observacoes]);
    }
}

$api = new ProductAPI();
$api->handleRequest();
?>
