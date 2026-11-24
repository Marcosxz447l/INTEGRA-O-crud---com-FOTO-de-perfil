<?php

// Informa que o conteúdo enviado pelo PHP será no formato JSON,
// utilizando a codificação de caracteres UTF-8.
header('Content-Type: application/json; charset=utf8');

// Inclui o arquivo de conexão com o banco de dados.
// O require_once evita que o mesmo arquivo seja incluído mais de uma vez.
require_once 'conexão.php';

// Identifica o método HTTP utilizado na requisição,
// como GET, POST, PUT ou DELETE.
$metodo = $_SERVER['REQUEST_METHOD'];

// Define a pasta onde serão armazenadas as imagens enviadas.
$uploadDir = 'uploads/';

// Verifica se a pasta de uploads ainda não existe.
if (!file_exists($uploadDir)) {

    // Cria a pasta de uploads com permissão 0777
    // e permite criar diretórios intermediários, se necessário.
    mkdir($uploadDir, 0777, true);

}
  //==================================
  //FUNÇÃO AUXILIAR DE UPLOAD(ENVIO)
  //==================================

// Cria uma função responsável por salvar a foto enviada pelo usuário.
// Recebe o arquivo enviado ($file) e o diretório onde será armazenado ($uploadDir).
function salvarFoto($file, $uploadDir){

 // Verifica se nenhum arquivo foi enviado ou se ocorreu algum erro no envio.
// Caso exista algum problema, retorna null.

if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
  return null;
}

// Obtém a extensão do arquivo e transforma todas as letras em minúsculas.
$extensao = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

// Define uma lista de extensões de imagem permitidas.
$extesoesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

// Verifica se a extensão do arquivo está na lista de extensões permitidas.
if (!in_array($extensao, $extesoesPermitidas)) {

// Retorna null caso a extensão não seja permitida.
  return null;
}

 // Gera um nome único para a imagem, utilizando o prefixo 'profele_'.
 // O true permite adicionar mais informações para aumentar a exclusividade do nome.
 // Depois, acrescenta a extensão original do arquivo.
 $novoNome = uniqid('profele_', true) . '.' . $extensao;

 // Junta o diretório de uploads com o novo nome da imagem,
 // formando o caminho completo onde o arquivo será salvo.
 $caminhoFinal = $uploadDir . $novoNome;

 // Move o arquivo enviado temporariamente para a pasta de destino.
 // Verifica se o processo de envio foi realizado com sucesso.
 if (move_uploaded_file($file['tmp_name'], $caminhoFinal)) {

     // Retorna o caminho da imagem caso ela tenha sido salva corretamente.
     return $caminhoFinal;
 }

  // Retorna null caso não seja possível salvar a imagem.
  return null;
 }

 switch ($metodo) {
   //=================================
   //read(consulta - metodo GET)
   //=================================

   case 'GET':
    if(isset($_GET['id'])){
      $stmt = $pdo->prepare("SELECT *FROM usuarios WHERE id= :id");

      $stmt->execute(' :id => $_GET['id']');

      usuario = $stmt->fetch();

      if ($usuário) {
        echo json_encode($usuario);
      }else{
        http_reponse_code(404);
        echo json_encode(['erro'=> 'Usuário não encontrado']);
      }
    }else{
      $usuarios = $pdo->query("SELECT * FROM usuarios ORDER BY id DESC") ->fetchALL();

      echo json_encode ($usuarios);
    }

     break;
     //=========================================================
     // CREATE / UPDATE (CRIAÇÃO E ATUALIZAÇÃO - METODO POST)
     //=========================================================
     case'POST':

     // Recebe o ID do usuário enviado pelo formulário.
     // Caso não exista, define o valor como null.
     $id = $_POST['id'] ?? null;

     // Recebe o nome do usuário e remove os espaços extras do início e do final.
     $nome = trim($_POST['nome'] ?? '');

     // Recebe o e-mail do usuário e remove os espaços extras.
     $email = trim($_POST['email'] ?? '');

     // Verifica se o nome ou o e-mail estão vazios.
     if (empty($nome)  || empty ($emai)){

       // Define o código de resposta HTTP 400, indicando uma requisição inválida.
       http_reponse_code(400);

       // Retorna uma mensagem de erro no formato JSON.
       echo json_encode(['erro' => 'Prencha nome e email']);

       // Encerra a execução do código.
       exit;
     }

     // Verifica se uma foto foi enviada.
     // Se existir, chama a função salvarFoto para armazenar a imagem.
     $caminhoFoto = isset($_FILES['foto'])? salvarFoto($_FILES['foto']), $uploadDir : null;

     // Verifica se existe um ID para identificar um usuário já cadastrado.
     if ($id) {

       // Prepara uma consulta SQL para buscar a foto atual do usuário.
       $stmt = $pdo->prepare("SELECT foto FROM usuarios WHERE id= :id");

       // Executa a consulta utilizando o ID recebido.
       $stmt->execute([':id' => $id]);

       // Armazena os dados do usuário encontrados no banco de dados.
       $usuarioAtual = $stmt-fetch();

       // Verifica se uma nova foto foi enviada e salva.
       if ($caminhoFoto) {

         // Verifica se o usuário possui uma foto antiga
         // e se o arquivo dessa foto ainda existe.
         if (!empty($usuariosAtual['foto']) &&
       file_exists($usuariosAtual['foto'])) {

         // Exclui a foto antiga do servidor.
         unlink($usuarioAtual['foto'])
         }

         // Define o comando SQL para atualizar os dados do usuário.
         $sql = "UPDATE usuarios SET nome= :nome, email= :email WHERE id = :id"

         // Organiza os dados que serão utilizados na atualização.
         $param= [':nome' => $nome, 'email' => $email,':foto' => $caminhoFoto, ':id' => id];

     }else{

       // Define o comando SQL para atualizar apenas o nome e o e-mail.
       $sql = "UPDATE usuarios SET nome= :nome, email= :email WHERE id = :id";

       // Organiza os dados do usuário que serão atualizados.
       $param= [':nome' => $nome, 'email' => $email,':id' => $id];
     }

     // Prepara o comando SQL para ser executado no banco de dados.
     $stmt = pdo->prepare($sql);

     // Executa a atualização utilizando os parâmetros definidos.
     $stmt->execute($params);

     // Retorna uma mensagem informando que o usuário foi atualizado com sucesso.
     echo json_encode(['mensagem' => 'Usuario atualizado com sucesso']);

   }else{
     try {
       $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, foto) VALUES (:nome, :email, :foto)");

      http_reponse_code(201)
      echo json_encode(['mensagem' => 'Usuario criado com sucesso']);
    } catch (PDOException $e) {

        http_reponse_code(400)
        echo json_encode(['erro'=> 'Email já cadastrado']);

     }
   }
   break;
   //===================================
   //DELETE (Exclusão - Método DELTE)
   //===================================


   case 'DELETE':

   if (isset($_GET['id'])) {
     $id = $_GET['id'];

     $stmt = $pdo->prepare("SELECT foto FROM usuarios WHERE id= :id");
     $stmt->execute([':id' => $id]);
     $usuarioAtual = $stmt-fetch();

     if ($usuario && !empty($usuario['foto']) && file_exists($usuario['foto'])) {
       unlink($usuario['foto']);
     }
     $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id= :id")
      $stmt->execute([':id' => $id]);

      echo json_encode(['mensagem' =>'Usuario removido com sucesso'])
   }else{
     http_reponse_code(400)
     echo json_encode(['erro' => 'ID não informado']);
   }
   break;

   default:
   http_reponse_code(405)
   echo json_encode(['erro' => 'Metodo não permitido']);
   break;

}
  ?>
