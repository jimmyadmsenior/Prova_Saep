<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Estoque SAEP</title>
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
                <a class="nav-link active" href="dashboard.php">Dashboard</a>
                <a class="nav-link" href="produtos.php">Produtos</a>
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
            <h1 class="h3 mb-0 text-gray-800">Dashboard - Visão Geral</h1>
        </div>

        <!-- Cards de Estatísticas -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total de Produtos</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalProducts">0</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-boxes fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Produtos em Estoque</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalStock">0</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Estoque Baixo</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="lowStockProducts">0</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Movimentações Hoje</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalMovements">0</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-exchange-alt fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Produtos com Estoque Baixo -->
        <div class="card border-left-warning shadow mb-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-warning">Produtos com Estoque Baixo</h6>
            </div>
            <div class="card-body">
                <div id="lowStockAlerts">
                    <p class="text-center text-muted">Carregando dados...</p>
                </div>
            </div>
        </div>

        <!-- Últimas Movimentações -->
        <div class="card border-left-info shadow">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-info">Últimas Movimentações</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="recentMovements">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Produto</th>
                                <th>Tipo</th>
                                <th>Quantidade</th>
                            </tr>
                        </thead>
                        <tbody id="lastMovements">
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    Carregando dados...
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
            console.log('Dashboard: Iniciando...');
            
            // Verificar autenticação
            if (!db.isAuthenticated()) {
                console.log('Usuário não autenticado, redirecionando...');
                window.location.href = 'index.php';
                return;
            }
            
            console.log('Usuário autenticado, carregando dashboard...');
            
            // Atualizar nome do usuário no navbar
            Utils.updateUserName();
            
            // Carregar dados do dashboard após pequena pausa para garantir que o DOM esteja pronto
            setTimeout(() => {
                Dashboard.init();
            }, 100);
        });
    </script>
</body>
</html>