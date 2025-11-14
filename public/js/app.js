// Sistema de Gerenciamento de Estoque - SAEP DB
// Simulação de banco de dados usando localStorage

class SaepDB {
    constructor() {
        this.useAPI = true; // Sempre tentar usar API
        this.initializeDefaultData();
        this.checkAPIConnection();
    }

    // Verificar conexão com API
    async checkAPIConnection() {
        try {
            const response = await fetch('backend/api/products.php', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            
            if (response.ok) {
                console.log('API conectada com sucesso ao MySQL');
                this.useAPI = true;
            } else {
                console.log('API não disponível, usando apenas localStorage');
                this.useAPI = false;
            }
        } catch (error) {
            console.log('Erro ao conectar API:', error);
            this.useAPI = false;
        }
    }

    // Inicializar dados padrão se não existirem
    initializeDefaultData() {
        // Criar 3 usuários padrão se não existirem
        const users = JSON.parse(localStorage.getItem('saep_users') || '[]');
        if (users.length === 0) {
            const defaultUsers = [
                {
                    id: 1,
                    username: 'admin',
                    password: this.hashPassword('123456'), // Senha com hash
                    name: 'Administrador'
                },
                {
                    id: 2,
                    username: 'peluxo',
                    password: this.hashPassword('123456'), // Senha com hash
                    name: 'Peluxo'
                },
                {
                    id: 3,
                    username: 'zakafofo',
                    password: this.hashPassword('123456'), // Senha com hash
                    name: 'Zakafofo'
                }
            ];
            localStorage.setItem('saep_users', JSON.stringify(defaultUsers));
        }

        // Adicionar produtos de exemplo se não existirem
        const products = this.getProducts();
        if (products.length === 0) {
            this.addExampleProducts();
        }
    }

    // Função simples de hash para senhas
    hashPassword(password) {
        // Hash simples (para produção, use bcrypt ou similar)
        let hash = 0;
        for (let i = 0; i < password.length; i++) {
            const char = password.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash; // Converter para 32bit
        }
        return Math.abs(hash).toString(16);
    }

    addExampleProducts() {
        const exampleProducts = [
            {
                name: "Telhado Cerâmico Português",
                category: "Telhas e Coberturas",
                stock: 150,
                price: 2.80,
                description: "Telha cerâmica modelo português"
            },
            {
                name: "Viga de Aço H 200mm",
                category: "Ferro e Aço", 
                stock: 8,
                price: 245.50,
                description: "Viga de aço estrutural H 200mm"
            },
            {
                name: "Arame Galvanizado 14 BWG",
                category: "Ferro e Aço",
                stock: 25,
                price: 18.90,
                description: "Rolo de arame galvanizado calibre 14"
            },
            {
                name: "Tijolo Comum 6 Furos",
                category: "Tijolos e Blocos",
                stock: 3,  // Estoque baixo para testar alerta
                price: 0.85,
                description: "Tijolo cerâmico comum 9x14x19cm"
            }
        ];

        exampleProducts.forEach((product, index) => {
            product.id = Date.now() + index;
            product.createdAt = new Date().toISOString();
        });

        localStorage.setItem('saep_products', JSON.stringify(exampleProducts));

        // Adicionar algumas movimentações de exemplo
        const movements = [
            {
                id: Date.now() + 1000,
                productId: exampleProducts[0].id,
                productName: exampleProducts[0].name,
                type: 'entrada',
                quantity: 150,
                date: new Date().toISOString(),
                description: 'Estoque inicial'
            },
            {
                id: Date.now() + 1001,
                productId: exampleProducts[1].id,
                productName: exampleProducts[1].name,
                type: 'entrada',
                quantity: 8,
                date: new Date().toISOString(),
                description: 'Estoque inicial'
            }
        ];

        localStorage.setItem('saep_movements', JSON.stringify(movements));
        console.log('Dados de exemplo criados com sucesso!');
    }

    // Gerenciamento de usuários
    async authenticateUser(username, password) {
        try {
            // Tentar autenticar via API MySQL primeiro
            const response = await fetch('backend/api/auth.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    username: username,
                    password: password
                })
            });
            
            if (response.ok) {
                const result = await response.json();
                if (result.success) {
                    localStorage.setItem('saep_current_user', JSON.stringify(result.user));
                    console.log('Autenticação MySQL bem-sucedida:', result.user);
                    return true;
                } else {
                    console.error('Erro de autenticação MySQL:', result.error);
                }
            } else {
                console.error('Erro de resposta da API de autenticação:', response.status);
            }
        } catch (error) {
            console.error('Erro ao conectar com API de autenticação:', error);
        }
        
        // Fallback: tentar localStorage se API falhar
        console.log('Tentando autenticação via localStorage...');
        const users = JSON.parse(localStorage.getItem('saep_users') || '[]');
        const hashedPassword = this.hashPassword(password);
        const user = users.find(u => u.username === username && u.password === hashedPassword);
        if (user) {
            localStorage.setItem('saep_current_user', JSON.stringify(user));
            console.log('Autenticação localStorage bem-sucedida:', user);
            return true;
        }
        
        console.error('Falha na autenticação em ambos os métodos');
        return false;
    }

    getCurrentUser() {
        return JSON.parse(localStorage.getItem('saep_current_user') || 'null');
    }

    logout() {
        localStorage.removeItem('saep_current_user');
    }

    isAuthenticated() {
        return localStorage.getItem('saep_current_user') !== null;
    }

    // Gerenciamento de produtos
    getProducts() {
        return JSON.parse(localStorage.getItem('saep_products') || '[]');
    }

    async addProduct(product) {
        console.log('Adicionando produto:', product);
        
        // Primeiro salvar no localStorage
        const products = this.getProducts();
        product.id = Date.now();
        product.stock = parseInt(product.stock) || 0;
        product.createdAt = new Date().toISOString();
        products.push(product);
        localStorage.setItem('saep_products', JSON.stringify(products));
        
        // Tentar salvar no MySQL via API
        try {
            const response = await fetch('backend/api/products.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    nome: product.name,
                    descricao: product.description || '',
                    categoria: product.category,
                    preco_unitario: product.price || 0,
                    quantidade_estoque: product.stock || 0,
                    estoque_minimo: 5
                })
            });
            
            if (response.ok) {
                const result = await response.json();
                if (result.success) {
                    console.log('Produto salvo no MySQL com sucesso! ID:', result.id);
                    // Atualizar o produto no localStorage com o ID do MySQL
                    product.mysqlId = result.id;
                    localStorage.setItem('saep_products', JSON.stringify(products));
                } else {
                    console.error('Erro ao salvar no MySQL:', result.error);
                }
            } else {
                console.error('Erro de resposta da API:', response.status);
            }
        } catch (error) {
            console.error('Erro ao conectar com a API:', error);
        }
        
        return product;
    }

    async updateProduct(id, updatedProduct) {
        console.log('Atualizando produto ID:', id, 'Dados:', updatedProduct);
        
        // Atualizar no localStorage primeiro
        const products = this.getProducts();
        const index = products.findIndex(p => p.id === parseInt(id));
        if (index !== -1) {
            products[index] = { ...products[index], ...updatedProduct };
            localStorage.setItem('saep_products', JSON.stringify(products));
        }
        
        // Tentar atualizar no MySQL via API
        try {
            const response = await fetch('backend/api/products.php', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id: parseInt(id),
                    nome: updatedProduct.name,
                    categoria: updatedProduct.category,
                    descricao: updatedProduct.description || '',
                    preco_unitario: updatedProduct.price || 0,
                    stock: updatedProduct.stock || 0,
                    quantidade_estoque: updatedProduct.stock || 0,
                    estoque_minimo: 5
                })
            });
            
            if (response.ok) {
                const result = await response.json();
                if (result.success) {
                    console.log('Produto atualizado no MySQL com sucesso! ID:', id);
                } else {
                    console.error('Erro ao atualizar no MySQL:', result.error);
                }
            } else {
                console.error('Erro de resposta da API:', response.status);
            }
        } catch (error) {
            console.error('Erro ao conectar com a API para atualizar produto:', error);
        }
        
        return index !== -1 ? products[index] : null;
    }

    async deleteProduct(id) {
        console.log('Excluindo produto ID:', id);
        
        // Excluir do localStorage primeiro
        const products = this.getProducts();
        const index = products.findIndex(p => p.id === parseInt(id));
        let deletedProduct = null;
        if (index !== -1) {
            deletedProduct = products[index];
            products.splice(index, 1);
            localStorage.setItem('saep_products', JSON.stringify(products));
        }
        
        // Tentar excluir do MySQL via API
        try {
            const response = await fetch(`backend/api/products.php?id=${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            
            if (response.ok) {
                const result = await response.json();
                if (result.success) {
                    console.log('Produto excluído do MySQL com sucesso! ID:', id);
                } else {
                    console.error('Erro ao excluir do MySQL:', result.error);
                }
            } else {
                console.error('Erro de resposta da API:', response.status);
            }
        } catch (error) {
            console.error('Erro ao conectar com a API para excluir produto:', error);
        }
        
        return deletedProduct;
    }

    getProduct(id) {
        const products = this.getProducts();
        return products.find(p => p.id === parseInt(id));
    }

    // Gerenciamento de movimentações
    getMovements() {
        return JSON.parse(localStorage.getItem('saep_movements') || '[]');
    }

    addMovement(movement) {
        const movements = this.getMovements();
        movement.id = Date.now();
        movement.date = new Date().toISOString();
        movements.push(movement);
        localStorage.setItem('saep_movements', JSON.stringify(movements));
        return movement;
    }

    getLowStockProducts() {
        return this.getProducts().filter(p => (parseInt(p.stock) || 0) < 5);
    }
}

// Instância global do banco de dados
const db = new SaepDB();

// Utilitários gerais
const Utils = {
    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('pt-BR');
    },

    formatCurrency(value) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(value);
    },

    showAlert(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const container = document.querySelector('.container, .container-fluid');
        if (container) {
            container.insertBefore(alertDiv, container.firstChild);
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
    },

    redirectToLogin() {
        window.location.href = 'index.php';
    },

    checkAuthentication() {
        if (!db.isAuthenticated()) {
            this.redirectToLogin();
            return false;
        }
        this.updateUserName();
        return true;
    },

    updateUserName() {
        const userNameElement = document.getElementById('userName');
        if (userNameElement) {
            const currentUser = db.getCurrentUser();
            if (currentUser) {
                userNameElement.textContent = currentUser.name || currentUser.username || 'Usuário';
            } else {
                userNameElement.textContent = 'Usuário';
            }
        }
    }
};

// Sistema de Login
const Login = {
    setupLoginForm() {
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', this.handleLogin.bind(this));
        }
    },

    async handleLogin(e) {
        e.preventDefault();
        
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;
        
        // Desabilitar botão de login temporariamente
        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Verificando...';

        try {
            const success = await db.authenticateUser(username, password);
            if (success) {
                window.location.href = 'dashboard.php';
            } else {
                Utils.showAlert('Usuário ou senha inválidos!', 'danger');
            }
        } catch (error) {
            console.error('Erro durante login:', error);
            Utils.showAlert('Erro durante o login. Tente novamente.', 'danger');
        } finally {
            // Reabilitar botão
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    }
};

// Sistema de Navegação
const Navigation = {
    init() {
        const currentPage = window.location.pathname.split('/').pop();
        this.updateActiveLink(currentPage);
        
        // Adicionar evento de logout se o botão existir
        const logoutBtn = document.querySelector('[onclick="logout()"]');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', this.handleLogout);
        }
    },

    updateActiveLink(currentPage) {
        const links = document.querySelectorAll('.nav-link');
        links.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === currentPage) {
                link.classList.add('active');
            }
        });
    },

    handleLogout() {
        if (confirm('Deseja realmente sair do sistema?')) {
            db.logout();
            window.location.href = 'index.php';
        }
    }
};

// Sistema do Dashboard
const Dashboard = {
    init() {
        console.log('Dashboard.init: Iniciando...');
        
        if (!Utils.checkAuthentication()) {
            console.log('Falha na autenticação');
            return;
        }
        
        this.loadStats();
        this.loadLowStockAlerts();
        this.loadLastMovements();
        
        console.log('Dashboard.init: Concluído');
    },

    loadStats() {
        console.log('Dashboard.loadStats: Carregando estatísticas...');
        
        try {
            const products = db.getProducts();
            const movements = db.getMovements();
            
            console.log('Produtos encontrados:', products.length);
            console.log('Movimentações encontradas:', movements.length);
            
            const stats = {
                totalProducts: products.length,
                lowStockProducts: products.filter(p => (parseInt(p.stock) || 0) < 5).length,
                totalMovements: movements.length,
                totalStock: products.reduce((sum, p) => sum + (parseInt(p.stock) || 0), 0)
            };
            
            console.log('Estatísticas calculadas:', stats);
            
            // Atualizar elementos
            this.updateElement('totalProducts', stats.totalProducts);
            this.updateElement('lowStockProducts', stats.lowStockProducts);
            this.updateElement('totalMovements', stats.totalMovements);
            this.updateElement('totalStock', stats.totalStock);
            
        } catch (error) {
            console.error('Erro ao carregar estatísticas:', error);
        }
    },
    
    updateElement(id, value) {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value;
            console.log(`Atualizou ${id}: ${value}`);
        } else {
            console.error(`Elemento não encontrado: ${id}`);
        }
    },

    loadLowStockAlerts() {
        const lowStockProducts = db.getLowStockProducts();
        const alertsContainer = document.getElementById('lowStockAlerts');
        
        if (!alertsContainer) return;

        if (lowStockProducts.length === 0) {
            alertsContainer.innerHTML = '<p class="text-center text-muted">Nenhum produto com estoque baixo.</p>';
            return;
        }

        const alertsHTML = lowStockProducts.map(product => `
            <div class="d-flex align-items-center py-2 border-bottom">
                <div class="flex-grow-1">
                    <strong>${product.name}</strong><br>
                    <small class="text-muted">Estoque atual: ${product.stock} unidades</small>
                </div>
                <span class="badge bg-warning">Baixo</span>
            </div>
        `).join('');

        alertsContainer.innerHTML = alertsHTML;
    },

    loadLastMovements() {
        const movements = db.getMovements().slice(-5).reverse(); // Últimas 5
        const movementsContainer = document.getElementById('lastMovements');
        
        if (!movementsContainer) return;

        if (movements.length === 0) {
            movementsContainer.innerHTML = '<p class="text-center text-muted">Nenhuma movimentação encontrada.</p>';
            return;
        }

        const movementsHTML = movements.map(movement => `
            <tr>
                <td>${Utils.formatDate(movement.date)}</td>
                <td>${movement.productName}</td>
                <td>
                    <span class="badge bg-${movement.type === 'entrada' ? 'success' : 'danger'}">
                        ${movement.type.toUpperCase()}
                    </span>
                </td>
                <td>${movement.quantity}</td>
            </tr>
        `).join('');

        movementsContainer.innerHTML = movementsHTML;
    }
};

// Sistema de Produtos
const Products = {
    init() {
        console.log('Products.init: Iniciando...');
        
        if (!Utils.checkAuthentication()) {
            console.log('Falha na autenticação');
            return;
        }
        
        this.loadProducts();
        this.setupEventListeners();
    },

    loadProducts() {
        const products = db.getProducts();
        const tbody = document.getElementById('productsTable');
        
        if (!tbody) return;

        if (products.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Nenhum produto cadastrado.</td></tr>';
            return;
        }

        const productsHTML = products.map(product => `
            <tr>
                <td>${product.name}</td>
                <td>${product.category}</td>
                <td>${product.stock}</td>
                <td>${Utils.formatCurrency(product.price)}</td>
                <td>${Utils.formatDate(product.createdAt)}</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="Products.editProduct(${product.id})">
                        Editar
                    </button>
                    <button class="btn btn-sm btn-danger ms-1" onclick="Products.deleteProduct(${product.id})">
                        Excluir
                    </button>
                </td>
            </tr>
        `).join('');

        tbody.innerHTML = productsHTML;
    },

    setupEventListeners() {
        const addBtn = document.getElementById('addProductBtn');
        if (addBtn) {
            addBtn.addEventListener('click', () => this.showProductModal());
        }
        
        // Adicionar eventos de busca
        const searchInput = document.getElementById('searchProduct');
        const clearBtn = document.getElementById('clearSearch');
        
        if (searchInput) {
            searchInput.addEventListener('input', (e) => this.filterProducts(e.target.value));
        }
        
        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                searchInput.value = '';
                this.filterProducts('');
            });
        }
    },
    
    filterProducts(searchTerm) {
        const products = db.getProducts();
        const filteredProducts = products.filter(product => 
            product.name.toLowerCase().includes(searchTerm.toLowerCase())
        );
        
        this.renderProducts(filteredProducts);
    },
    
    renderProducts(products) {
        const tbody = document.getElementById('productsTable');
        
        if (!tbody) return;

        if (products.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Nenhum produto encontrado.</td></tr>';
            return;
        }

        const productsHTML = products.map(product => `
            <tr>
                <td>${product.name}</td>
                <td>${product.category}</td>
                <td>${product.stock}</td>
                <td>${Utils.formatCurrency(product.price)}</td>
                <td>${Utils.formatDate(product.createdAt)}</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="Products.editProduct(${product.id})">
                        Editar
                    </button>
                    <button class="btn btn-sm btn-danger ms-1" onclick="Products.deleteProduct(${product.id})">
                        Excluir
                    </button>
                </td>
            </tr>
        `).join('');

        tbody.innerHTML = productsHTML;
    },

    loadProducts() {
        const products = db.getProducts();
        this.renderProducts(products);
    },

    showProductModal(productId = null) {
        const modal = new bootstrap.Modal(document.getElementById('productModal'));
        const modalTitle = document.getElementById('productModalLabel');
        
        if (productId) {
            modalTitle.textContent = 'Editar Produto';
            this.populateForm(productId);
        } else {
            modalTitle.textContent = 'Adicionar Produto';
            this.clearForm();
        }
        
        modal.show();
    },

    populateForm(productId) {
        const product = db.getProduct(productId);
        if (product) {
            document.getElementById('productId').value = product.id;
            document.getElementById('productName').value = product.name;
            document.getElementById('productCategory').value = product.category;
            document.getElementById('productStock').value = product.stock;
            document.getElementById('productPrice').value = product.price;
        }
    },

    clearForm() {
        document.getElementById('productForm').reset();
        document.getElementById('productId').value = '';
    },

    async saveProduct() {
        const formData = this.getFormData();
        
        if (!this.validateForm(formData)) {
            return;
        }

        const productId = document.getElementById('productId').value;
        
        try {
            if (productId) {
                // Editar produto existente
                db.updateProduct(productId, formData);
                Utils.showAlert('Produto atualizado com sucesso!', 'success');
            } else {
                // Adicionar novo produto
                await db.addProduct(formData);
                Utils.showAlert('Produto adicionado com sucesso no sistema e banco de dados!', 'success');
            }

            // Fechar modal e recarregar lista
            const modal = bootstrap.Modal.getInstance(document.getElementById('productModal'));
            modal.hide();
            this.loadProducts();
        } catch (error) {
            console.error('Erro ao salvar produto:', error);
            Utils.showAlert('Erro ao salvar produto. Verifique o console para detalhes.', 'danger');
        }
    },

    getFormData() {
        return {
            name: document.getElementById('productName').value,
            category: document.getElementById('productCategory').value,
            stock: parseInt(document.getElementById('productStock').value) || 0,
            price: parseFloat(document.getElementById('productPrice').value) || 0
        };
    },

    validateForm(data) {
        if (!data.name.trim()) {
            Utils.showAlert('Nome do produto é obrigatório!', 'danger');
            return false;
        }
        if (!data.category) {
            Utils.showAlert('Categoria é obrigatória!', 'danger');
            return false;
        }
        return true;
    },

    editProduct(id) {
        this.showProductModal(id);
    },

    async deleteProduct(id) {
        const product = db.getProduct(id);
        if (product && confirm(`Deseja excluir o produto "${product.name}"?`)) {
            await db.deleteProduct(id);
            Utils.showAlert('Produto excluído com sucesso!', 'success');
            this.loadProducts();
        }
    }
};

// Sistema de Movimentações
const Movements = {
    init() {
        console.log('Movements.init: Iniciando...');
        
        if (!Utils.checkAuthentication()) {
            console.log('Falha na autenticação');
            return;
        }
        
        this.loadProducts();
        this.loadMovementHistory();
        this.setupEventListeners();
    },

    loadProducts() {
        const products = db.getProducts();
        const select = document.getElementById('productSelect');
        
        if (!select) return;

        // Limpar opções existentes, mantendo a primeira
        select.innerHTML = '<option value="">Selecione um produto...</option>';
        
        products.forEach(product => {
            const option = document.createElement('option');
            option.value = product.id;
            option.textContent = `${product.name} (Estoque: ${product.stock})`;
            select.appendChild(option);
        });
        
        console.log(`Carregados ${products.length} produtos no select`);
    },

    loadMovementHistory() {
        const movements = db.getMovements().slice(-10).reverse(); // Últimas 10
        const tbody = document.getElementById('movementHistory');
        
        if (!tbody) return;

        if (movements.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Nenhuma movimentação encontrada.</td></tr>';
            return;
        }

        const movementsHTML = movements.map(movement => `
            <tr>
                <td>${Utils.formatDate(movement.date)}</td>
                <td>${movement.productName}</td>
                <td>
                    <span class="badge bg-${movement.type === 'entrada' ? 'success' : 'danger'}">
                        ${movement.type === 'entrada' ? '🟢 ENTRADA' : '🔴 SAÍDA'}
                    </span>
                </td>
                <td>${movement.quantity}</td>
                <td>${movement.description || '-'}</td>
            </tr>
        `).join('');

        tbody.innerHTML = movementsHTML;
    },

    setupEventListeners() {
        const form = document.getElementById('movementForm');
        if (form) {
            form.addEventListener('submit', this.handleSubmit.bind(this));
        }
    },

    handleSubmit(e) {
        e.preventDefault();
        
        const productId = parseInt(document.getElementById('productSelect').value);
        const type = document.getElementById('movementType').value;
        const quantity = parseInt(document.getElementById('quantity').value);
        const date = document.getElementById('movementDate').value;
        const reason = document.getElementById('reason').value;
        
        if (!productId || !type || !quantity) {
            Utils.showAlert('Preencha todos os campos obrigatórios!', 'danger');
            return;
        }
        
        const product = db.getProduct(productId);
        if (!product) {
            Utils.showAlert('Produto não encontrado!', 'danger');
            return;
        }
        
        // Verificar estoque para saída
        if (type === 'saida' && quantity > product.stock) {
            Utils.showAlert(`Estoque insuficiente! Disponível: ${product.stock}`, 'danger');
            return;
        }
        
        // Criar movimentação
        const movement = {
            productId: productId,
            productName: product.name,
            type: type,
            quantity: quantity,
            date: date,
            description: reason
        };
        
        // Salvar movimentação
        db.addMovement(movement);
        
        // Atualizar estoque do produto
        const newStock = type === 'entrada' 
            ? product.stock + quantity 
            : product.stock - quantity;
            
        db.updateProduct(productId, { stock: newStock });
        
        Utils.showAlert('Movimentação registrada com sucesso!', 'success');
        
        // Limpar formulário e recarregar
        this.clearForm();
        this.loadProducts();
        this.loadMovementHistory();
    },
    
    clearForm() {
        document.getElementById('movementForm').reset();
        document.getElementById('movementDate').value = new Date().toISOString().split('T')[0];
    }
};
function setupLoginForm() {
    Login.setupLoginForm();
}

function logout() {
    Navigation.handleLogout();
}

function resetExampleData() {
    if (confirm('Isto irá remover todos os dados e recriar os dados de exemplo. Continuar?')) {
        localStorage.removeItem('saep_products');
        localStorage.removeItem('saep_movements');
        db.addExampleProducts();
        location.reload();
    }
}

// Funções para a página de estoque
function loadProducts() {
    Movements.loadProducts();
}

function loadMovementHistory() {
    Movements.loadMovementHistory();
}

function setupMovementForm() {
    Movements.setupEventListeners();
}

function clearForm() {
    Movements.clearForm();
}

// Inicialização baseada na página atual
document.addEventListener('DOMContentLoaded', function() {
    const currentPage = window.location.pathname.split('/').pop();
    
    console.log('Página atual:', currentPage);
    
    switch (currentPage) {
        case 'index.php':
            setupLoginForm();
            break;
        case 'dashboard.php':
            Dashboard.init();
            break;
        case 'produtos.php':
            Products.init();
            break;
        case 'estoque.php':
            Movements.init();
            break;
    }
});