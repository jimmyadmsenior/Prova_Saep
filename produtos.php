<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos - Sistema de Estoque SAEP</title>
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
                <a class="nav-link active" href="produtos.php">Produtos</a>
                <a class="nav-link" href="estoque.php">Movimentações</a>
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
            <h1 class="h3 mb-0 text-gray-800">Gerenciamento de Produtos</h1>
            <button class="btn btn-primary" id="addProductBtn">
                Adicionar Produto
            </button>
        </div>

        <div class="row">
            <div class="col-12">
                <!-- Card da Tabela de Produtos -->
                <div class="card border-left-primary shadow mb-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">Materiais de construção cadastrados no sistema</h6>
                    </div>
                    <div class="card-body">
                        <!-- Campo de busca -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text">🔍</span>
                                    <input type="text" class="form-control" id="searchProduct" placeholder="Digite o nome do produto para buscar...">
                                    <button class="btn btn-outline-secondary" type="button" id="clearSearch">Limpar</button>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Nome do Produto</th>
                                        <th>Categoria</th>
                                        <th>Estoque Atual</th>
                                        <th>Preço Unitário</th>
                                        <th>Data de Cadastro</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody id="productsTable">
                                    <tr>
                                        <td colspan="6" class="text-center text-gray-500">
                                            Nenhum produto cadastrado.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
        </div>
    </div>

    <!-- Modal para Adicionar/Editar Produto -->
    <div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="productModalLabel">Adicionar Produto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="productForm">
                        <input type="hidden" id="productId">
                        
                        <div class="mb-3">
                            <label for="productName" class="form-label">Nome do Produto:</label>
                            <input type="text" class="form-control" id="productName" required placeholder="Ex: Cimento Portland CP II">
                        </div>

                        <div class="mb-3">
                            <label for="productCategory" class="form-label">Categoria:</label>
                            <select class="form-select" id="productCategory" required>
                                <option value="">Selecione uma categoria...</option>
                                <option value="Cimento e Argamassa">Cimento e Argamassa</option>
                                <option value="Tijolos e Blocos">Tijolos e Blocos</option>
                                <option value="Ferro e Aço">Ferro e Aço</option>
                                <option value="Madeira">Madeira</option>
                                <option value="Telhas e Coberturas">Telhas e Coberturas</option>
                                <option value="Tubos e Conexões">Tubos e Conexões</option>
                                <option value="Outros">Outros</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="productStock" class="form-label">Estoque Inicial:</label>
                                    <input type="number" class="form-control" id="productStock" required min="0" placeholder="Ex: 100">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="productPrice" class="form-label">Preço Unitário (R$):</label>
                                    <input type="number" class="form-control" id="productPrice" required min="0" step="0.01" placeholder="Ex: 29.90">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning" onclick="resetExampleData()">Resetar Dados</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="Products.saveProduct()">Salvar Produto</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/app.js"></script>
    <script>
        // Função de logout
        function logout() {
            if (confirm('Deseja realmente sair do sistema?')) {
                db.logout();
                window.location.href = 'index.php';
            }
        }

        // Função para resetar dados de exemplo
        function resetExampleData() {
            if (confirm('Isto irá remover todos os dados e recriar os dados de exemplo. Continuar?')) {
                localStorage.removeItem('saep_products');
                localStorage.removeItem('saep_movements');
                db.addExampleProducts();
                location.reload();
            }
        }

        // Inicialização específica da página
        document.addEventListener('DOMContentLoaded', function() {
            // Verificar autenticação
            if (!db.isAuthenticated()) {
                window.location.href = 'index.php';
                return;
            }
            
            // Atualizar nome do usuário
            Utils.updateUserName();
            
            // Inicializar produtos
            Products.init();
        });
    </script>
</body>
</html>