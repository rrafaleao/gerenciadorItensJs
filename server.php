<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Conexão com o banco de dados
$servername = "localhost";
$username = "root"; // altere conforme seu usuário
$password = ""; // altere conforme sua senha
$dbname = "gerenciador_itens";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Função para tratar os dados de entrada
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Rotas da API
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Listar todos os itens ou um específico
        if (isset($_GET['id'])) {
            $id = test_input($_GET['id']);
            $sql = "SELECT * FROM itens WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $item = $result->fetch_assoc();
            
            if ($item) {
                echo json_encode($item);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Item não encontrado."));
            }
        } else {
            $sql = "SELECT * FROM itens";
            $result = $conn->query($sql);
            $items = array();
            
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
            
            echo json_encode($items);
        }
        break;
        
    case 'POST':
        // Criar um novo item
        $data = json_decode(file_get_contents("php://input"), true);
        
        $nome = test_input($data['nome']);
        $quantidade = test_input($data['quantidade']);
        $preco = test_input($data['preco']);
        $descricao = test_input($data['descricao']);
        $ativo = isset($data['ativo']) ? test_input($data['ativo']) : 1;
        
        $sql = "INSERT INTO itens (nome, quantidade, preco, descricao, ativo) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sidsi", $nome, $quantidade, $preco, $descricao, $ativo);
        
        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(array("message" => "Item criado com sucesso.", "id" => $stmt->insert_id));
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Não foi possível criar o item."));
        }
        break;
        
    case 'PUT':
        // Atualizar um item existente
        $data = json_decode(file_get_contents("php://input"), true);
        
        $id = test_input($data['id']);
        $nome = test_input($data['nome']);
        $quantidade = test_input($data['quantidade']);
        $preco = test_input($data['preco']);
        $descricao = test_input($data['descricao']);
        $ativo = isset($data['ativo']) ? test_input($data['ativo']) : 1;
        
        $sql = "UPDATE itens SET nome = ?, quantidade = ?, preco = ?, descricao = ?, ativo = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sidsii", $nome, $quantidade, $preco, $descricao, $ativo, $id);
        
        if ($stmt->execute()) {
            echo json_encode(array("message" => "Item atualizado com sucesso."));
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Não foi possível atualizar o item."));
        }
        break;
        
    case 'DELETE':
        // Excluir um item
        $data = json_decode(file_get_contents("php://input"), true);
        $id = test_input($data['id']);
        
        $sql = "DELETE FROM itens WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(array("message" => "Item excluído com sucesso."));
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Não foi possível excluir o item."));
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Método não permitido."));
        break;
}

$conn->close();
?>