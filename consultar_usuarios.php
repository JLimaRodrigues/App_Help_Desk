<html>
<head>
    <meta charset="utf-8" />
    <title>App Help Desk</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">

    <style>
        .card-consultar-chamado {
            padding: 30px 0;
            width: 100%;
            margin: 0 auto;
        }
        .pagination {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        .pagination a {
            padding: 8px 12px;
            margin: 2px;
            border: 1px solid #ddd;
            color: #007bff;
            cursor: pointer;
        }
        .pagination a.active {
            background-color: #007bff;
            color: white;
        }
        .pagination a:hover {
            background-color: #ddd;
        }
        .table-info {
            font-size: 14px;
            margin-bottom: 10px;
            text-align: right;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <a class="navbar-brand" href="#">
            <img src="logo.png" width="30" height="30" class="d-inline-block align-top" alt=""> App Help Desk
        </a>
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" href="logoff.php">SAIR</a>
            </li>
        </ul>
    </nav>

    <div class="container">    
        <div class="row">
            <div class="card-consultar-chamado">
                <div class="card">
                    <div class="card-header">Consulta de Usuários</div>
                    <div class="card-body">
                        <input type="text" id="search" class="form-control" placeholder="Pesquisar usuário... (Nome, Login, Email, Data)">
                        <br>

                        <div class="table-info" id="table-info"></div>

                        <table class="table table-bordered">
                            <thead class="thead-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>Login</th>
                                    <th>Email</th>
                                    <th>Data de Criação</th>
                                    <th>Nível</th>
                                </tr>
                            </thead>
                            <tbody id="user-table">
                                <!-- Tabela será preenchida via JS -->
                            </tbody>
                        </table>

                        <div class="pagination" id="pagination"></div>

                        <div class="row mt-5">
                            <div class="col-6">
                                <a href="home.php" class="btn btn-lg btn-warning btn-block">Voltar</a>
                            </div>
                            <div class="col-6">
                                <a href="abrir_chamado.php" class="btn btn-lg btn-success btn-block">Criar Usuário</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let paginaAtual = 1;

        function carregarUsuarios(pagina = 1, pesquisa = '') {
            let formData = new FormData();
            formData.append('pagina', pagina);
            formData.append('search', pesquisa);

            fetch('usuarios.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const tabela = document.getElementById('user-table');
                const paginacao = document.getElementById('pagination');
                const infoTabela = document.getElementById('table-info');

                tabela.innerHTML = '';
                paginacao.innerHTML = '';

                // Atualiza Info da Tabela
                infoTabela.innerHTML = `Exibindo ${data.usuarios.length} de ${data.total_usuarios} registros`;

                // Preenche a tabela
                data.usuarios.forEach(user => {
                    tabela.innerHTML += `
                        <tr>
                            <td>${user.id}</td>
                            <td>${user.nome}</td>
                            <td>${user.login}</td>
                            <td>${user.email}</td>
                            <td>${user.created_at}</td>
                            <td>${user.nivel}</td>
                        </tr>
                    `;
                });

                // Paginação
                if (data.total_paginas > 1) {
                    for (let i = 1; i <= data.total_paginas; i++) {
                        let active = i === data.pagina_atual ? 'active' : '';
                        paginacao.innerHTML += `<a class="${active}" onclick="carregarUsuarios(${i}, '${pesquisa}')">${i}</a>`;
                    }
                }

                paginaAtual = pagina;
            });
        }

        document.getElementById('search').addEventListener('input', function () {
            let pesquisa = this.value;
            if (pesquisa.length === 0) {
                carregarUsuarios(1, ''); // Volta a listar tudo
            } else {
                carregarUsuarios(1, pesquisa);
            }
        });

        window.onload = () => carregarUsuarios();
    </script>
</body>
</html>
