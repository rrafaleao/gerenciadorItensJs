document.addEventListener('DOMContentLoaded', function() {
    const itemForm = document.getElementById('itemForm');
    const itemsTable = document.getElementById('itemsTable').getElementsByTagName('tbody')[0];
    const filterInput = document.getElementById('filterInput');
    
    // Carregar itens ao iniciar
    loadItems();
    
    // Adicionar/Editar item
    itemForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const id = document.getElementById('itemId').value;
        const item = {
            nome: document.getElementById('nome').value,
            quantidade: parseInt(document.getElementById('quantidade').value),
            preco: parseFloat(document.getElementById('preco').value),
            descricao: document.getElementById('descricao').value,
            ativo: document.getElementById('ativo').checked ? 1 : 0
        };
        
        if (id) {
            item.id = id;
            updateItem(item);
        } else {
            createItem(item);
        }
    });
    
    // Filtrar itens
    filterInput.addEventListener('input', function() {
        loadItems(filterInput.value);
    });
    
    // Carregar itens
    function loadItems(filter = '') {
        fetch('server.php' + (filter ? `?filter=${encodeURIComponent(filter)}` : ''))
            .then(response => response.json())
            .then(data => {
                itemsTable.innerHTML = '';
                data.forEach(item => {
                    const row = itemsTable.insertRow();
                    row.innerHTML = `
                        <td>${item.id}</td>
                        <td>${item.nome}</td>
                        <td>${item.quantidade}</td>
                        <td>R$ ${item.preco}</td>
                        <td>${item.descricao.substring(0, 50)}${item.descricao.length > 50 ? '...' : ''}</td>
                        <td>${item.ativo ? 'Ativo' : 'Inativo'}</td>
                        <td>
                            <button onclick="editItem(${item.id})" class="btn-edit">Editar</button>
                            <button onclick="deleteItem(${item.id})" class="btn-delete">Excluir</button>
                        </td>
                    `;
                });
            })
            .catch(error => console.error('Erro ao carregar itens:', error));
    }
    
    // Criar item
    function createItem(item) {
        fetch('server.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(item)
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            resetForm();
            loadItems();
        })
        .catch(error => console.error('Erro ao criar item:', error));
    }
    
    // Editar item
    window.editItem = function(id) {
        fetch(`server.php?id=${id}`)
            .then(response => response.json())
            .then(item => {
                document.getElementById('itemId').value = item.id;
                document.getElementById('nome').value = item.nome;
                document.getElementById('quantidade').value = item.quantidade;
                document.getElementById('preco').value = item.preco;
                document.getElementById('descricao').value = item.descricao;
                document.getElementById('ativo').checked = item.ativo;
                
                document.getElementById('formTitle').textContent = 'Editar Item';
                document.getElementById('submitBtn').textContent = 'Atualizar';
            })
            .catch(error => console.error('Erro ao carregar item para edição:', error));
    }
    
    // Atualizar item
    function updateItem(item) {
        fetch('server.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(item)
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            resetForm();
            loadItems();
        })
        .catch(error => console.error('Erro ao atualizar item:', error));
    }
    
    // Excluir item
    window.deleteItem = function(id) {
        if (confirm('Tem certeza que deseja excluir este item?')) {
            fetch('server.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                loadItems();
            })
            .catch(error => console.error('Erro ao excluir item:', error));
        }
    }
    
    // Resetar formulário
    function resetForm() {
        document.getElementById('itemId').value = '';
        document.getElementById('itemForm').reset();
        document.getElementById('formTitle').textContent = 'Adicionar Item';
        document.getElementById('submitBtn').textContent = 'Salvar';
    }
});