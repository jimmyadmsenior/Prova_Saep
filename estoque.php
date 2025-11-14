<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movimentações - Sistema de Estoque SAEP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                Sistema SAEP
            </a>
            
            <div class="navbar-nav me-auto">
                <a class="nav-link" href="dashboard.php">Dashboard</a>
                <a class="nav-link" href="produtos.php">Produtos</a>
                <a class="nav-link active" href="estoque.php">Movimentações</a>
            </div>
            
            <div class="navbar-text">
                Bem-vindo, <strong id="userName">Carregando...</strong>
                <button class="btn btn-outline-light btn-sm ms-3" onclick="logout()">Sair</button>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <!-- Header da página -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Movimentações de Estoque</h1>
        </div>

        <!-- Card de Nova Movimentação -->
        <div class="card border-left-primary shadow mb-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Nova Movimentação de Estoque</h6>
            </div>
            <div class="card-body">
                <form id="movementForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label" for="productSelect">Produto:</label>
                                <select class="form-select" id="productSelect" required>
                                    <option value="">Selecione um produto...</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label" for="movementType">Tipo de Movimentação:</label>
                                <select class="form-select" id="movementType" required>
                                    <option value="">Selecione o tipo...</option>
                                    <option value="entrada">Entrada (Compra/Recebimento)</option>
                                    <option value="saida">Saída (Venda/Uso)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label" for="quantity">Quantidade:</label>
                                <input type="number" class="form-control" id="quantity" min="1" placeholder="Ex: 50" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label" for="movementDate">Data da Movimentação:</label>
                                <input type="date" class="form-control" id="movementDate" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label" for="reason">Motivo/Observação (opcional):</label>
                                <input type="text" class="form-control" id="reason" placeholder="Ex: Compra do fornecedor">
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary">
                            Registrar Movimentação
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="clearForm()">
                            Limpar Formulário
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Card de Histórico -->
        <div class="card border-left-info shadow">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Histórico de Movimentações</h6>
            </div>
            <div class="card-body">
                <p class="text-gray-500 mb-4">Registro completo de todas as entradas e saídas de materiais</p>
                
                <div class="table-responsive">
                    <table class="table table-bordered" id="movementHistory">
                        <thead>
                            <tr>
                                <th>DATA</th>
                                <th>PRODUTO</th>
                                <th>TIPO</th>
                                <th>QUANTIDADE</th>
                                <th>MOTIVO/OBSERVAÇÃO</th>
                            </tr>
                        </thead>
                        <tbody id="movementHistory">
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    Carregando movimentações...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/app.js"></script>
    <script>
        // Inicialização específica da página
        document.addEventListener('DOMContentLoaded', function() {
            // Verificar autenticação
            if (!db.isAuthenticated()) {
                window.location.href = 'index.php';
                return;
            }
            
            // Atualizar nome do usuário
            Utils.updateUserName();
            
            // Configurar data atual
            document.getElementById('movementDate').value = new Date().toISOString().split('T')[0];
            
            // Carregar produtos
            loadProducts();
            
            // Carregar histórico
            loadMovementHistory();
            
            // Configurar form
            setupMovementForm();
        });
    </script>
</body>
</html>