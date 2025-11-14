<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Estoque SAEP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body class="login-container d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-4 col-lg-5 col-md-6">
                <div class="card login-card shadow-lg">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h2 class="text-primary font-weight-bold">Sistema SAEP</h2>
                            <p class="text-gray-500 mb-4">Gerenciamento de Materiais de Construção</p>
                        </div>
                        
                        <form id="loginForm">
                            <div class="form-group mb-3">
                                <label class="form-label" for="username">Usuário:</label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="username" 
                                    name="username" 
                                    required 
                                    placeholder="Digite seu usuário"
                                    autocomplete="username"
                                >
                            </div>
                            
                            <div class="form-group mb-4">
                                <label class="form-label" for="password">Senha:</label>
                                <input 
                                    type="password" 
                                    class="form-control" 
                                    id="password" 
                                    name="password" 
                                    required 
                                    placeholder="Digite sua senha"
                                    autocomplete="current-password"
                                >
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                Entrar no Sistema
                            </button>
                            
                            <div class="alert alert-info" style="display: none;" id="loginInfo">
                                <small>
                                    <strong>Credenciais de teste:</strong><br>
                                    Usuário: admin<br>
                                    Senha: 123456
                                </small>
                            </div>
                            
                            <div class="text-center">
                                <small class="text-gray-500">
                                    <a href="#" onclick="showLoginInfo()" class="text-decoration-none">
                                        Mostrar credenciais de teste
                                    </a>
                                </small>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <small class="text-white-50">
                        Sistema de Estoque para Materiais de Construção
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/app.js"></script>
    <script>
        function showLoginInfo() {
            document.getElementById('loginInfo').style.display = 'block';
        }
        
        // Configurar formulário de login
        document.addEventListener('DOMContentLoaded', function() {
            setupLoginForm();
        });
    </script>
</body>
</html>