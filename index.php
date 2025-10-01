<?php
// index.php - Versão FINAL CORRIGIDA para UOL Host - MANTENDO TODOS OS EMAILS
session_start();

// ===== CONFIGURAÇÕES CORRIGIDAS PARA UOL HOST =====
// REMOVIDO: error_reporting, ini_set que causam erro 500
// Essas configurações devem ser feitas no painel UOL Host

// Configurações básicas
define('UPLOAD_FOLDER', __DIR__ . '/static/uploads');
define('METADATA_FILE', __DIR__ . '/file_metadata.json');
define('ALLOWED_EXTENSIONS', ['png', 'jpg', 'jpeg', 'gif', 'pdf', 'doc', 'docx', 'txt', 'webp']);

// ===== CONFIGURAÇÕES DE EMAIL MANTIDAS COMPLETAS =====
define('SMTP_SERVER', 'smtps.uhserver.com');
define('SMTP_PORT', 465);
define('SMTP_USERNAME', 'contato@tecpoint.net.br');
define('SMTP_PASSWORD', 'tecpoint@2024B');

// Criar diretório de uploads com permissões CORRETAS para UOL Host
if (!file_exists(UPLOAD_FOLDER)) {
   if (!mkdir(UPLOAD_FOLDER, 0755, true)) { // 755 em vez de 777
       error_log("ERRO: Não foi possível criar pasta de uploads");
   }
}

// ===== FUNÇÕES PARA TEMPLATES - COMPATIBILIDADE COM FLASK =====
function flash($message, $category = 'info') {
   $_SESSION['flash_message'] = $message;
   $_SESSION['flash_category'] = $category;
}

function get_flashed_messages($with_categories = false) {
   $messages = [];
   if (isset($_SESSION['flash_message'])) {
       if ($with_categories && isset($_SESSION['flash_category'])) {
           $messages[] = [$_SESSION['flash_category'], $_SESSION['flash_message']];
       } else {
           $messages[] = $_SESSION['flash_message'];
       }
       unset($_SESSION['flash_message']);
       unset($_SESSION['flash_category']);
   }
   return $messages;
}

function url_for($endpoint, $params = []) {
   $routes = [
       'index' => '/',
       'produtos' => '/produtos',
       'servicos' => '/servicos',
       'contato' => '/contato',
       'admin_dashboard' => '/admin',
       'admin_login' => '/admin/login',
       'admin_logout' => '/admin/logout',
       'admin_add_product' => '/admin/produtos/adicionar',
       'admin_add_service' => '/admin/servicos/adicionar',
       'produto_detalhe' => '/produto/',
       'admin_edit_product' => '/admin/produtos/editar/',
       'admin_delete_product' => '/admin/produtos/excluir/',
       'admin_edit_service' => '/admin/servicos/editar/',
       'admin_delete_service' => '/admin/servicos/excluir/',
       'uploaded_file' => '/uploads/',
       'get_product' => '/admin/produtos/',
       'get_service' => '/admin/servicos/',
       'admin_delete_product_image' => '/admin/produtos/excluir-imagem/',
       'admin_delete_additional_product_image' => '/admin/produtos/excluir-imagem-adicional/',
       'admin_delete_product_pdf' => '/admin/produtos/excluir-pdf/',
       'enviar_cotacao' => '/enviar-cotacao',
       'enviar_contato_site' => '/enviar-contato-site',
       'enviar_contato_form' => '/enviar-contatoTEC',
       'enviar_servico_form' => '/enviar-serviço'
   ];
   
   $url = $routes[$endpoint] ?? '/';
   
   if (!empty($params)) {
       if (in_array($endpoint, ['produto_detalhe', 'admin_edit_product', 'admin_delete_product', 'admin_edit_service', 'admin_delete_service', 'get_product', 'get_service', 'admin_delete_product_image', 'admin_delete_additional_product_image', 'admin_delete_product_pdf'])) {
           $url .= $params['id'] ?? '';
       } elseif ($endpoint === 'uploaded_file') {
           $url .= $params['filename'] ?? '';
       } elseif ($endpoint === 'produtos' && isset($params['category'])) {
           $url .= '?category=' . urlencode($params['category']);
       }
   }
   
   return $url;
}

function redirect($url, $code = 302) {
   header("Location: $url", true, $code);
   exit;
}

function render_template($template, $variables = []) {
   extract($variables);
   include $template;
}

function request() {
   return (object) [
       'method' => $_SERVER['REQUEST_METHOD'],
       'args' => $_GET,
       'form' => $_POST,
       'files' => $_FILES,
       'json' => json_decode(file_get_contents('php://input'), true)
   ];
}

function session($key = null, $value = null) {
   if ($key === null) {
       return $_SESSION;
   }
   
   if ($value === null) {
       return $_SESSION[$key] ?? null;
   }
   
   $_SESSION[$key] = $value;
   return $value;
}

function jsonify($data, $status = 200) {
   http_response_code($status);
   header('Content-Type: application/json');
   echo json_encode($data);
   exit;
}

function json_loads($json_string) {
   try {
       if (is_array($json_string)) {
           return $json_string;
       }
       return json_decode($json_string ?: '[]', true) ?: [];
   } catch (Exception $e) {
       return [];
   }
}

function json_dumps($data) {
   return json_encode($data, JSON_UNESCAPED_UNICODE);
}

function escapeHtml($text) {
   return htmlspecialchars($text ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// ===== FUNÇÕES DE IMAGEM MANTIDAS COMPLETAS =====
function getImageUrl($filename) {
   if (!$filename || $filename === '') {
       return null;
   }
   
   $fullPath = UPLOAD_FOLDER . '/' . $filename;
   if (!file_exists($fullPath)) {
       return null;
   }
   
   return '/static/uploads/' . $filename;
}

function getProductImageUrl($filename) {
   if (!$filename || $filename === '') {
       return null;
   }
   
   $fullPath = UPLOAD_FOLDER . '/' . $filename;
   if (!file_exists($fullPath)) {
       return null;
   }
   
   return '/static/uploads/' . $filename;
}

function getLogoUrl() {
   return '/static/LogoTecPoint.png';
}

function validarImagemExiste($imagePath) {
   if (!$imagePath || $imagePath === '') {
       return false;
   }
   
   $fullPath = UPLOAD_FOLDER . '/' . $imagePath;
   return file_exists($fullPath);
}

function filtrarImagensValidas($imagePathsJson) {
   if (!$imagePathsJson) {
       return [];
   }
   
   $imagePaths = json_decode($imagePathsJson, true);
   if (!is_array($imagePaths)) {
       return [];
   }
   
   $imagensValidas = [];
   foreach ($imagePaths as $imagePath) {
       if (validarImagemExiste($imagePath)) {
           $imagensValidas[] = $imagePath;
       }
   }
   
   return $imagensValidas;
}

function jsonDecode($json_string) {
   try {
       if (is_array($json_string)) {
           return $json_string;
       }
       if (empty($json_string)) {
           return [];
       }
       if (is_string($json_string)) {
           return json_decode($json_string, true) ?: [];
       }
       return [];
   } catch (Exception $e) {
       return [];
   }
}

function produtoTemImagem($product) {
   return !empty($product['image_path']) && validarImagemExiste($product['image_path']);
}

function getFlashMessage() {
   if (isset($_SESSION['flash_message'])) {
       $message = $_SESSION['flash_message'];
       $category = $_SESSION['flash_category'] ?? 'info';
       
       $alertTypes = [
           'info' => 'info',
           'success' => 'success',
           'warning' => 'warning',
           'error' => 'danger',
           'danger' => 'danger'
       ];
       
       $result = [
           'message' => $message,
           'type' => $alertTypes[$category] ?? 'info'
       ];
       
       unset($_SESSION['flash_message']);
       unset($_SESSION['flash_category']);
       
       return $result;
   }
   return null;
}

// ===== CONFIGURAÇÃO DE BANCO DE DADOS =====
class Database {
   private static $instance = null;
   private $connection;
   
   private function __construct() {
       try {
           $db_uri = getenv('DATABASE_URL') ?: 'sqlite:' . __DIR__ . '/local.db';
           
           if (strpos($db_uri, 'postgres://') === 0) {
               $db_uri = str_replace('postgres://', 'postgresql://', $db_uri);
           }
           
           if (strpos($db_uri, 'sqlite:') === 0) {
               $db_path = str_replace('sqlite:', '', $db_uri);
               $this->connection = new PDO("sqlite:$db_path");
               $this->connection->exec("PRAGMA foreign_keys = ON");
           } else {
               $this->connection = new PDO($db_uri);
           }
           
           $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
           $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
           
       } catch(PDOException $e) {
           error_log("Erro de conexão: " . $e->getMessage());
           die("Erro de conexão com o banco de dados");
       }
   }
   
   public static function getInstance() {
       if (self::$instance === null) {
           self::$instance = new self();
       }
       return self::$instance;
   }
   
   public function getConnection() {
       return $this->connection;
   }
}

// ===== FUNÇÕES DE ARQUIVO CORRIGIDAS PARA UOL HOST =====
function allowed_file($filename) {
   if (!$filename) return false;
   $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
   return in_array($ext, ALLOWED_EXTENSIONS);
}

function secure_filename($filename) {
   return preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
}

function save_file($file) {
   if (!check_upload_folder()) {
       return null;
   }
   
   if (!$file || !isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
       error_log("Erro ao salvar arquivo: file=" . json_encode($file));
       return null;
   }
   
   if (!allowed_file($file['name'])) {
       return null;
   }
   
   if (!file_exists(UPLOAD_FOLDER)) {
       mkdir(UPLOAD_FOLDER, 0755, true); // 755 em vez de 777
   }
   
   $filename = secure_filename($file['name']);
   $unique_filename = uniqid() . '_' . $filename;
   $file_path = UPLOAD_FOLDER . '/' . $unique_filename;
   
   if (move_uploaded_file($file['tmp_name'], $file_path)) {
       chmod($file_path, 0644); // 644 em vez de 666
       save_file_metadata($unique_filename, filesize($file_path));
       error_log("Arquivo salvo com sucesso: " . $file_path . " - Nome único: " . $unique_filename);
       return $unique_filename;
   }
   
   error_log("Erro ao mover arquivo: " . $file['tmp_name'] . " para " . $file_path);
   return null;
}

function delete_file($filename) {
   if ($filename && $filename !== '') {
       $file_path = UPLOAD_FOLDER . '/' . $filename;
       if (file_exists($file_path)) {
           unlink($file_path);
           error_log("Arquivo excluído: " . $file_path);
       }
   }
}

function save_file_metadata($filename, $filesize) {
   $metadata = load_metadata();
   $metadata[$filename] = [
       'upload_date' => date('Y-m-d H:i:s'),
       'size' => $filesize
   ];
   
   try {
       file_put_contents(METADATA_FILE, json_encode($metadata, JSON_PRETTY_PRINT));
   } catch (Exception $e) {
       error_log("Erro ao salvar metadados: " . $e->getMessage());
   }
}

function load_metadata() {
   try {
       if (file_exists(METADATA_FILE)) {
           $content = file_get_contents(METADATA_FILE);
           return json_decode($content, true) ?: [];
       }
   } catch (Exception $e) {
       error_log("Erro ao carregar metadados: " . $e->getMessage());
   }
   return [];
}

// ===== FUNÇÃO DE EMAIL PARA PRODUÇÃO (UOL HOST) =====
function send_email($subject, $html_content, $to_email, $reply_to = null) {
   try {
       error_log("Enviando email para: $to_email");

       // MODO PRODUÇÃO: Usa mail() nativa do UOL Host (mais confiável)
       $headers = "MIME-Version: 1.0\r\n";
       $headers .= "Content-type: text/html; charset=UTF-8\r\n";
       $headers .= "From: TecPoint <" . SMTP_USERNAME . ">\r\n";
       $headers .= "Date: " . date('r') . "\r\n";

       if ($reply_to) {
           $headers .= "Reply-To: " . $reply_to . "\r\n";
       }

       // Parâmetros adicionais para UOL Host
       $additional_params = "-f" . SMTP_USERNAME;

       $result = @mail($to_email, $subject, $html_content, $headers, $additional_params);

       if ($result) {
           error_log("✓ Email enviado com sucesso via mail() para: $to_email");
           return true;
       }

       // FALLBACK 1: Tenta SMTP Socket
       error_log("mail() falhou, tentando SMTP socket...");
       $result = send_email_smtp_socket($subject, $html_content, $to_email, $reply_to);

       if ($result) {
           error_log("✓ Email enviado com sucesso via SMTP socket");
           return true;
       }

       // FALLBACK 2: Salva em arquivo de backup
       error_log("⚠ Todos os métodos falharam, salvando em backup");

       $email_log = __DIR__ . '/emails_backup.log';
       $log_content = "\n\n" . str_repeat("=", 80) . "\n";
       $log_content .= "[BACKUP - ENVIO FALHOU]\n";
       $log_content .= "DATA/HORA: " . date('Y-m-d H:i:s') . "\n";
       $log_content .= "PARA: $to_email\n";
       $log_content .= "ASSUNTO: $subject\n";
       $log_content .= "REPLY-TO: " . ($reply_to ?? 'N/A') . "\n";
       $log_content .= "IP ORIGEM: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\n";
       $log_content .= str_repeat("-", 80) . "\n";
       $log_content .= $html_content . "\n";
       $log_content .= str_repeat("=", 80) . "\n\n";

       file_put_contents($email_log, $log_content, FILE_APPEND);

       error_log("✓ Email salvo em backup: $email_log");
       return true; // Retorna sucesso para não bloquear o usuário

   } catch (Exception $e) {
       error_log("✗ Erro crítico ao enviar email: " . $e->getMessage());
       return false;
   }
}

// ===== FUNÇÃO DE EMAIL USANDO SOCKET SMTP NATIVO PHP =====
function send_email_smtp_socket($subject, $html_content, $to_email, $reply_to = null) {
   try {
       error_log("Tentando enviar email via SMTP socket para: $to_email");

       // Configurações de contexto SSL para Windows (ignora verificação de certificado)
       $context = stream_context_create([
           'ssl' => [
               'verify_peer' => false,
               'verify_peer_name' => false,
               'allow_self_signed' => true,
               'crypto_method' => STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT
           ]
       ]);

       // Conecta ao servidor SMTP com contexto SSL
       $smtp = stream_socket_client(
           'ssl://' . SMTP_SERVER . ':' . SMTP_PORT,
           $errno,
           $errstr,
           30,
           STREAM_CLIENT_CONNECT,
           $context
       );

       if (!$smtp) {
           error_log("Falha ao conectar ao SMTP: $errstr ($errno)");
           return false;
       }

       stream_set_timeout($smtp, 30);

       // Lê resposta inicial
       $response = fgets($smtp);
       error_log("SMTP Response: $response");

       // EHLO
       fputs($smtp, "EHLO " . SMTP_SERVER . "\r\n");
       $response = fgets($smtp);

       // AUTH LOGIN
       fputs($smtp, "AUTH LOGIN\r\n");
       fgets($smtp);

       fputs($smtp, base64_encode(SMTP_USERNAME) . "\r\n");
       fgets($smtp);

       fputs($smtp, base64_encode(SMTP_PASSWORD) . "\r\n");
       $auth_response = fgets($smtp);

       if (strpos($auth_response, '235') === false) {
           error_log("Falha na autenticação SMTP: $auth_response");
           fclose($smtp);
           return false;
       }

       // MAIL FROM
       fputs($smtp, "MAIL FROM: <" . SMTP_USERNAME . ">\r\n");
       fgets($smtp);

       // RCPT TO
       fputs($smtp, "RCPT TO: <$to_email>\r\n");
       fgets($smtp);

       // DATA
       fputs($smtp, "DATA\r\n");
       fgets($smtp);

       // Headers e corpo
       $headers = "From: TecPoint <" . SMTP_USERNAME . ">\r\n";
       $headers .= "To: <$to_email>\r\n";

       if ($reply_to) {
           $headers .= "Reply-To: <$reply_to>\r\n";
       }

       $headers .= "Subject: $subject\r\n";
       $headers .= "MIME-Version: 1.0\r\n";
       $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
       $headers .= "Date: " . date('r') . "\r\n";

       $message = $headers . "\r\n" . $html_content . "\r\n.\r\n";
       fputs($smtp, $message);

       $data_response = fgets($smtp);
       error_log("DATA Response: $data_response");

       // QUIT
       fputs($smtp, "QUIT\r\n");
       fclose($smtp);

       $success = strpos($data_response, '250') !== false;

       if ($success) {
           error_log("Email enviado com sucesso via SMTP socket para: $to_email");
       } else {
           error_log("Falha ao enviar email. Resposta: $data_response");
       }

       return $success;

   } catch (Exception $e) {
       error_log("Erro ao enviar email via SMTP socket: " . $e->getMessage());
       return false;
   }
}

// ===== FUNÇÕES ADMIN MANTIDAS COMPLETAS =====
function is_admin() {
   return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function admin_required($f) {
   return function(...$args) use ($f) {
       if (!is_admin()) {
           redirect(url_for('admin_login'));
       }
       return $f(...$args);
   };
}

function require_admin() {
   if (!is_admin()) {
       redirect(url_for('admin_login'));
   }
}

function check_upload_folder() {
   if (!file_exists(UPLOAD_FOLDER)) {
       if (!mkdir(UPLOAD_FOLDER, 0755, true)) { // 755 em vez de 777
           error_log("ERRO: Não foi possível criar pasta " . UPLOAD_FOLDER);
           return false;
       }
   }
   
   if (!is_writable(UPLOAD_FOLDER)) {
       error_log("ERRO: Pasta " . UPLOAD_FOLDER . " não tem permissão de escrita");
       return false;
   }
   
   return true;
}

// ===== FUNÇÃO PARA LIMPAR IMAGENS ÓRFÃS MANTIDA =====
function limparImagensOrfas() {
   try {
       $db = Database::getInstance()->getConnection();
       $stmt = $db->query("SELECT id, image_paths FROM products WHERE image_paths IS NOT NULL AND image_paths != ''");
       $products = $stmt->fetchAll();
       
       foreach ($products as $product) {
           $imagePaths = json_decode($product['image_paths'], true);
           if (!is_array($imagePaths)) continue;
           
           $imagensValidas = [];
           foreach ($imagePaths as $imagePath) {
               if (validarImagemExiste($imagePath)) {
                   $imagensValidas[] = $imagePath;
               }
           }
           
           if (count($imagensValidas) !== count($imagePaths)) {
               $newImagePaths = empty($imagensValidas) ? null : json_encode($imagensValidas);
               $updateStmt = $db->prepare("UPDATE products SET image_paths = ? WHERE id = ?");
               $updateStmt->execute([$newImagePaths, $product['id']]);
               error_log("Produto {$product['id']}: Imagens limpas - antes: " . count($imagePaths) . ", depois: " . count($imagensValidas));
           }
       }
   } catch (Exception $e) {
       error_log("Erro ao limpar imagens órfãs: " . $e->getMessage());
   }
}

// ===== CLASSES DE MODELO MANTIDAS COMPLETAS =====
class Product {
   private $db;
   
   public function __construct() {
       $this->db = Database::getInstance()->getConnection();
   }
   
   public static function query() {
       return new static();
   }
   
   public function order_by($column) {
       return $this;
   }
   
   public function filter_by($conditions) {
       return $this;
   }
   
   public function all() {
       return $this->getAll();
   }
   
   public function get_or_404($id) {
       $result = $this->getById($id);
       if (!$result) {
           http_response_code(404);
           include 'templates/404.html';
           exit;
       }
       return $result;
   }
   
   public function limit($num) {
       return $this;
   }
   
   public function create($data) {
       try {
           $sql = "INSERT INTO products (name, description, image_path, pdf_path, category, specs, image_paths, created_at) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
           $stmt = $this->db->prepare($sql);
           return $stmt->execute([
               $data['name'], 
               $data['description'], 
               $data['image_path'], 
               $data['pdf_path'], 
               $data['category'], 
               $data['specs'], 
               $data['image_paths'], 
               date('Y-m-d H:i:s')
           ]);
       } catch (PDOException $e) {
           error_log("Erro ao criar produto: " . $e->getMessage());
           return false;
       }
   }
   
   public function getAll($category = null) {
       try {
           if ($category && $category !== 'all') {
               $sql = "SELECT * FROM products WHERE category = ? ORDER BY created_at DESC";
               $stmt = $this->db->prepare($sql);
               $stmt->execute([$category]);
           } else {
               $sql = "SELECT * FROM products ORDER BY created_at DESC";
               $stmt = $this->db->prepare($sql);
               $stmt->execute();
           }
           
           $products = $stmt->fetchAll();
           
           foreach ($products as &$product) {
               if (!empty($product['image_paths'])) {
                   $imagensValidas = filtrarImagensValidas($product['image_paths']);
                   $product['image_paths'] = json_encode($imagensValidas);
               }
           }
           
           return $products;
       } catch (PDOException $e) {
           error_log("Erro ao buscar produtos: " . $e->getMessage());
           return [];
       }
   }
   
   public function getById($id) {
       try {
           $sql = "SELECT * FROM products WHERE id = ?";
           $stmt = $this->db->prepare($sql);
           $stmt->execute([$id]);
           $product = $stmt->fetch();
           
           if ($product && !empty($product['image_paths'])) {
               $imagensValidas = filtrarImagensValidas($product['image_paths']);
               $product['image_paths'] = json_encode($imagensValidas);
           }
           
           return $product;
       } catch (PDOException $e) {
           error_log("Erro ao buscar produto por ID: " . $e->getMessage());
           return false;
       }
   }
   
   public function update($id, $data) {
       try {
           $sql = "UPDATE products SET name = ?, description = ?, category = ?, specs = ?";
           $params = [$data['name'], $data['description'], $data['category'], $data['specs']];
           
           if (isset($data['image_path'])) {
               $sql .= ", image_path = ?";
               $params[] = $data['image_path'];
           }
           
           if (isset($data['pdf_path'])) {
               $sql .= ", pdf_path = ?";
               $params[] = $data['pdf_path'];
           }
           
           if (isset($data['image_paths'])) {
               $sql .= ", image_paths = ?";
               $params[] = $data['image_paths'];
           }
           
           $sql .= " WHERE id = ?";
           $params[] = $id;
           
           $stmt = $this->db->prepare($sql);
           return $stmt->execute($params);
       } catch (PDOException $e) {
           error_log("Erro ao atualizar produto: " . $e->getMessage());
           return false;
       }
   }
   
   public function delete($id) {
       try {
           $sql = "DELETE FROM products WHERE id = ?";
           $stmt = $this->db->prepare($sql);
           return $stmt->execute([$id]);
       } catch (PDOException $e) {
           error_log("Erro ao deletar produto: " . $e->getMessage());
           return false;
       }
   }
   
   public function getRelated($category, $exclude_id, $limit = 3) {
       try {
           $sql = "SELECT * FROM products WHERE category = ? AND id != ? LIMIT ?";
           $stmt = $this->db->prepare($sql);
           $stmt->execute([$category, $exclude_id, $limit]);
           
           $products = $stmt->fetchAll();
           
           foreach ($products as &$product) {
               if (!empty($product['image_paths'])) {
                   $imagensValidas = filtrarImagensValidas($product['image_paths']);
                   $product['image_paths'] = json_encode($imagensValidas);
               }
           }
           
           return $products;
       } catch (PDOException $e) {
           error_log("Erro ao buscar produtos relacionados: " . $e->getMessage());
           return [];
       }
   }
}

class Service {
   private $db;
   
   public function __construct() {
       $this->db = Database::getInstance()->getConnection();
   }
   
   public static function query() {
       return new static();
   }
   
   public function order_by($column) {
       return $this;
   }
   
   public function all() {
       return $this->getAll();
   }
   
   public function get_or_404($id) {
       $result = $this->getById($id);
       if (!$result) {
           http_response_code(404);
           include 'templates/404.html';
           exit;
       }
       return $result;
   }
   
   public function create($data) {
       try {
           $sql = "INSERT INTO services (name, description, features, image_path, category, created_at) 
                   VALUES (?, ?, ?, ?, ?, ?)";
           $stmt = $this->db->prepare($sql);
           return $stmt->execute([
               $data['name'], 
               $data['description'], 
               $data['features'], 
               $data['image_path'], 
               $data['category'], 
               date('Y-m-d H:i:s')
           ]);
       } catch (PDOException $e) {
           error_log("Erro ao criar serviço: " . $e->getMessage());
           return false;
       }
   }
   
   public function getAll() {
       try {
           $sql = "SELECT * FROM services ORDER BY created_at DESC";
           $stmt = $this->db->prepare($sql);
           $stmt->execute();
           $services = $stmt->fetchAll();
           
           foreach ($services as &$service) {
               $service['features_list'] = json_decode($service['features'] ?: '[]', true);
           }
           
           return $services;
       } catch (PDOException $e) {
           error_log("Erro ao buscar serviços: " . $e->getMessage());
           return [];
       }
   }
   
   public function getById($id) {
       try {
           $sql = "SELECT * FROM services WHERE id = ?";
           $stmt = $this->db->prepare($sql);
           $stmt->execute([$id]);
           $service = $stmt->fetch();
           if ($service) {
               $service['features_list'] = json_decode($service['features'] ?: '[]', true);
           }
           return $service;
       } catch (PDOException $e) {
           error_log("Erro ao buscar serviço por ID: " . $e->getMessage());
           return false;
       }
   }
   
   public function update($id, $data) {
       try {
           $sql = "UPDATE services SET name = ?, description = ?, category = ?, features = ?";
           $params = [$data['name'], $data['description'], $data['category'], $data['features']];
           
           if (isset($data['image_path'])) {
               $sql .= ", image_path = ?";
               $params[] = $data['image_path'];
           }
           
           $sql .= " WHERE id = ?";
           $params[] = $id;
           
           $stmt = $this->db->prepare($sql);
           return $stmt->execute($params);
       } catch (PDOException $e) {
           error_log("Erro ao atualizar serviço: " . $e->getMessage());
           return false;
       }
   }
   
   public function delete($id) {
       try {
           $sql = "DELETE FROM services WHERE id = ?";
           $stmt = $this->db->prepare($sql);
           return $stmt->execute([$id]);
       } catch (PDOException $e) {
           error_log("Erro ao deletar serviço: " . $e->getMessage());
           return false;
       }
   }
}

class Admin {
   private $db;
   
   public function __construct() {
       $this->db = Database::getInstance()->getConnection();
   }
   
   public static function query() {
       return new static();
   }
   
   public function filter_by($conditions) {
       return $this;
   }
   
   public function first() {
       return null;
   }
   
   public function authenticate($username, $password) {
       try {
           $sql = "SELECT * FROM admins WHERE username = ?";
           $stmt = $this->db->prepare($sql);
           $stmt->execute([$username]);
           $admin = $stmt->fetch();
           
           if ($admin && password_verify($password, $admin['password_hash'])) {
               $_SESSION['admin_logged_in'] = true;
               $_SESSION['admin_username'] = $username;
               return true;
           }
           return false;
       } catch (PDOException $e) {
           error_log("Erro na autenticação: " . $e->getMessage());
           return false;
       }
   }
   
   public function create($username, $password) {
       try {
           $sql = "INSERT INTO admins (username, password_hash) VALUES (?, ?)";
           $stmt = $this->db->prepare($sql);
           return $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
       } catch (PDOException $e) {
           error_log("Erro ao criar admin: " . $e->getMessage());
           return false;
       }
   }
   
   public function exists($username) {
       try {
           $sql = "SELECT COUNT(*) FROM admins WHERE username = ?";
           $stmt = $this->db->prepare($sql);
           $stmt->execute([$username]);
           return $stmt->fetchColumn() > 0;
       } catch (PDOException $e) {
           error_log("Erro ao verificar admin: " . $e->getMessage());
           return false;
       }
   }
}

// ===== INICIALIZAÇÃO DO BANCO MANTIDA COMPLETA =====
function init_database() {
   $db = Database::getInstance()->getConnection();
   
   try {
       $is_sqlite = strpos($db->getAttribute(PDO::ATTR_DRIVER_NAME), 'sqlite') !== false;
       
       if ($is_sqlite) {
           $auto_increment = 'INTEGER PRIMARY KEY AUTOINCREMENT';
           $varchar_type = 'TEXT';
           $text_type = 'TEXT';
           $datetime_type = 'TEXT';
           $default_time = "DEFAULT CURRENT_TIMESTAMP";
       } else {
           $auto_increment = 'SERIAL PRIMARY KEY';
           $varchar_type = 'VARCHAR';
           $text_type = 'TEXT';
           $datetime_type = 'TIMESTAMP';
           $default_time = "DEFAULT CURRENT_TIMESTAMP";
       }
       
       $db->exec("CREATE TABLE IF NOT EXISTS products (
           id $auto_increment,
           name {$varchar_type}(100) NOT NULL,
           description $text_type,
           image_path {$varchar_type}(200),
           pdf_path {$varchar_type}(200),
           category {$varchar_type}(50),
           specs $text_type,
           image_paths $text_type,
           created_at $datetime_type $default_time
       )");
       
       $db->exec("CREATE TABLE IF NOT EXISTS services (
           id $auto_increment,
           name {$varchar_type}(100) NOT NULL,
           description $text_type,
           features $text_type,
           image_path {$varchar_type}(200),
           category {$varchar_type}(50),
           created_at $datetime_type $default_time
       )");
       
       $db->exec("CREATE TABLE IF NOT EXISTS admins (
           id $auto_increment,
           username {$varchar_type}(80) UNIQUE NOT NULL,
           password_hash {$varchar_type}(255) NOT NULL
       )");
       
       $admin = new Admin();
       if (!$admin->exists('admin')) {
        $admin->create('admin', 'admin123');
    }
    
    if (!file_exists(__DIR__ . '/static/uploads')) {
        mkdir(__DIR__ . '/static/uploads', 0755, true); // 755 em vez de 777
    }
    
    limparImagensOrfas();
    
} catch (PDOException $e){
    error_log("Erro ao criar tabelas: " . $e->getMessage());
 }
}

// ===== SISTEMA DE ROTAS MANTIDO COMPLETO =====
function route() {
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$path = rtrim($path, '/');
if (empty($path)) $path = '/';

switch ($path) {
    case '/':
        handle_index();
        break;
        
    case '/servicos':
        handle_servicos();
        break;
        
    case '/contato':
        handle_contato();
        break;
        
    case '/produtos':
        handle_produtos();
        break;
        
    case '/api/servicos':
        handle_api_servicos();
        break;
        
    case '/enviar-cotacao':
        if ($method === 'POST') handle_enviar_cotacao();
        break;
        
    case '/enviar-contato-site':
        if ($method === 'POST') handle_enviar_contato_site();
        break;
        
    case '/enviar-contatoTEC':
        if ($method === 'POST') handle_enviar_contato_form();
        break;
        
    case '/enviar-serviço':
        if ($method === 'POST') handle_enviar_servico_form();
        break;
        
    case '/admin':
        require_admin();
        handle_admin_dashboard();
        break;
        
    case '/admin/login':
        handle_admin_login();
        break;
        
    case '/admin/logout':
        handle_admin_logout();
        break;
        
    case '/admin/produtos/adicionar':
        require_admin();
        handle_admin_add_product();
        break;
        
    case '/admin/servicos/adicionar':
        require_admin();
        handle_admin_add_service();
        break;
        
    default:
        if (preg_match('/^\/produto\/(\d+)$/', $path, $matches)) {
            handle_produto_detalhe($matches[1]);
        }
        elseif (preg_match('/^\/admin\/produtos\/(\d+)$/', $path, $matches)) {
            require_admin();
            if ($method === 'GET') handle_get_product($matches[1]);
        }
        elseif (preg_match('/^\/admin\/produtos\/editar\/(\d+)$/', $path, $matches)) {
            require_admin();
            if ($method === 'POST') handle_admin_edit_product($matches[1]);
        }
        elseif (preg_match('/^\/admin\/produtos\/excluir\/(\d+)$/', $path, $matches)) {
            require_admin();
            if ($method === 'POST') handle_admin_delete_product($matches[1]);
        }
        elseif (preg_match('/^\/admin\/servicos\/(\d+)$/', $path, $matches)) {
            require_admin();
            if ($method === 'GET') handle_get_service($matches[1]);
        }
        elseif (preg_match('/^\/admin\/servicos\/editar\/(\d+)$/', $path, $matches)) {
            require_admin();
            if ($method === 'POST') handle_admin_edit_service($matches[1]);
        }
        elseif (preg_match('/^\/admin\/servicos\/excluir\/(\d+)$/', $path, $matches)) {
            require_admin();
            if ($method === 'POST') handle_admin_delete_service($matches[1]);
        }
        elseif (preg_match('/^\/admin\/produtos\/excluir-imagem\/(\d+)$/', $path, $matches)) {
            require_admin();
            if ($method === 'POST') handle_admin_delete_product_image($matches[1]);
        }
        elseif (preg_match('/^\/admin\/produtos\/excluir-imagem-adicional\/(\d+)$/', $path, $matches)) {
            require_admin();
            if ($method === 'POST') handle_admin_delete_additional_product_image($matches[1]);
        }
        elseif (preg_match('/^\/admin\/produtos\/excluir-pdf\/(\d+)$/', $path, $matches)) {
            require_admin();
            if ($method === 'POST') handle_admin_delete_product_pdf($matches[1]);
        }
        elseif (preg_match('/^\/admin\/servicos\/excluir-imagem\/(\d+)$/', $path, $matches)) {
            require_admin();
            if ($method === 'POST') handle_admin_delete_service_image($matches[1]);
        }
        elseif (preg_match('/^\/uploads\/(.+)$/', $path, $matches)) {
            handle_uploaded_file($matches[1]);
        }
        else {
            http_response_code(404);
            include 'templates/404.html';
        }
        break;
}
}

// ===== HANDLERS DE PÁGINAS MANTIDOS COMPLETOS =====
function handle_index() {
$product_model = new Product();
$products = $product_model->getAll();

foreach ($products as &$produto) {
    if (isset($produto['specs'])) {
        $produto['specs'] = json_decode($produto['specs'] ?: '[]', true);
    }
    if (isset($produto['image_paths'])) {
        $produto['image_paths'] = json_decode($produto['image_paths'] ?: '[]', true);
    }
}

$template_vars = [
    'products' => $products
];

extract($template_vars);
include 'templates/index.html';
}

function handle_servicos() {
try {
    $service_model = new Service();
    $services = $service_model->getAll();
    
    $template_vars = [
        'services' => $services
    ];
    
    extract($template_vars);
    
    include 'templates/servicos.html';
} catch (Exception $e) {
    error_log("Erro ao buscar serviços: " . $e->getMessage());
    flash('Erro ao carregar serviços');
    header('Location: /');
}
}

function handle_api_servicos() {
try {
    error_log("API de serviços chamada");
    
    $service_model = new Service();
    $services = $service_model->getAll();
    
    error_log("Serviços encontrados: " . count($services));
    
    $services_processed = [];
    foreach ($services as $service) {
        $service_data = [
            'id' => $service['id'],
            'name' => $service['name'],
            'description' => $service['description'],
            'category' => $service['category'],
            'image_path' => $service['image_path'],
            'features' => $service['features']
        ];
        
        error_log("Serviço processado: " . $service['name'] . " - Imagem: " . ($service['image_path'] ?: 'nenhuma'));
        
        $services_processed[] = $service_data;
    }
    
    $response = [
        'services' => $services_processed
    ];
    
    error_log("Resposta da API: " . json_encode($response));
    
    header('Content-Type: application/json');
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Erro na API de serviços: " . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'services' => [],
        'error' => 'Erro ao carregar serviços'
    ]);
}
}

function handle_contato() {
include 'templates/contato.html';
}

function handle_produtos() {
$category = $_GET['category'] ?? 'all';
$product_model = new Product();

if ($category == 'all') {
    $products = $product_model->getAll();
} else {
    $products = $product_model->getAll($category);
}

foreach ($products as &$produto) {
    if (isset($produto['specs'])) {
        $produto['specs'] = json_decode($produto['specs'] ?: '[]', true);
    }
    if (isset($produto['image_paths'])) {
        $produto['image_paths'] = json_decode($produto['image_paths'] ?: '[]', true);
    }
}

$current_category = $category;

$template_vars = [
    'products' => $products,
    'current_category' => $current_category
];

extract($template_vars);

include 'templates/produtos.html';
}

function handle_produto_detalhe($id) {
$product_model = new Product();
$produto = $product_model->getById($id);

if (!$produto) {
    http_response_code(404);
    include 'templates/404.html';
    return;
}

if (isset($produto['specs'])) {
    if (is_string($produto['specs'])) {
        $produto['specs'] = json_decode($produto['specs'] ?: '[]', true) ?: [];
    } elseif (!is_array($produto['specs'])) {
        $produto['specs'] = [];
    }
} else {
    $produto['specs'] = [];
}

if (isset($produto['image_paths'])) {
    if (is_string($produto['image_paths'])) {
        $imagePaths = json_decode($produto['image_paths'] ?: '[]', true) ?: [];
        $imagensValidas = [];
        
        foreach ($imagePaths as $imagePath) {
            if (validarImagemExiste($imagePath)) {
                $imagensValidas[] = $imagePath;
            }
        }
        
        $produto['image_paths'] = $imagensValidas;
    } elseif (!is_array($produto['image_paths'])) {
        $produto['image_paths'] = [];
    }
} else {
    $produto['image_paths'] = [];
}

$related_products = $product_model->getRelated($produto['category'], $id);

foreach ($related_products as &$related) {
    if (isset($related['specs'])) {
        if (is_string($related['specs'])) {
            $related['specs'] = json_decode($related['specs'] ?: '[]', true) ?: [];
        } elseif (!is_array($related['specs'])) {
            $related['specs'] = [];
        }
    } else {
        $related['specs'] = [];
    }
    
    if (isset($related['image_paths'])) {
        if (is_string($related['image_paths'])) {
            $imagePaths = json_decode($related['image_paths'] ?: '[]', true) ?: [];
            $imagensValidas = [];
            
            foreach ($imagePaths as $imagePath) {
                if (validarImagemExiste($imagePath)) {
                    $imagensValidas[] = $imagePath;
                }
            }
            
            $related['image_paths'] = $imagensValidas;
        } elseif (!is_array($related['image_paths'])) {
            $related['image_paths'] = [];
        }
    } else {
        $related['image_paths'] = [];
    }
}

$template_vars = [
    'produto' => $produto,
    'product' => $produto,
    'related_products' => $related_products
];

extract($template_vars);

include 'templates/produto_detalhe.html';
}

// ===== HANDLERS DE EMAIL MANTIDOS COMPLETOS =====
function handle_enviar_cotacao() {
try {
    // PROTEÇÃO 1: Rate Limiting por IP
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $rate_limit_file = __DIR__ . '/rate_limit_cotacao_' . md5($ip) . '.txt';

    // Verifica último envio
    if (file_exists($rate_limit_file)) {
        $last_submit = (int)file_get_contents($rate_limit_file);
        $time_diff = time() - $last_submit;

        // Bloqueia se enviou há menos de 10 segundos
        if ($time_diff < 10) {
            error_log("Rate limit Cotação atingido para IP: $ip");
            http_response_code(429);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Muitas requisições. Aguarde alguns segundos.']);
            return;
        }
    }

    // PROTEÇÃO 2: Validação de campos
    $dados = [
        'nome' => trim($_POST['name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'telefone' => trim($_POST['phone'] ?? ''),
        'produto' => trim($_POST['product_name'] ?? ''),
        'categoria' => trim($_POST['product_category'] ?? ''),
        'quantidade' => trim($_POST['quantity'] ?? '1'),
        'mensagem' => trim($_POST['message'] ?? ''),
        'data' => date('d/m/Y \à\s H:i')
    ];

    if (empty($dados['nome']) || empty($dados['email']) || empty($dados['produto'])) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Campos obrigatórios não preenchidos']);
        return;
    }

    // PROTEÇÃO 3: Validação de email
    if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Email inválido']);
        return;
    }

    $html_empresa = '
    <html>
    <body style="font-family: Arial, sans-serif;">
        <h2 style="color: #00A859;">Nova Solicitação de Orçamento</h2>
        <div style="margin: 20px 0;">
            <h3>Dados do Cliente</h3>
            <p>
            <strong>Nome:</strong> ' . $dados['nome'] . '<br>
            <strong>Email:</strong> ' . $dados['email'] . '<br>
            <strong>Telefone:</strong> ' . $dados['telefone'] . '</p>
        </div>
        <div style="margin: 20px 0;">
            <h3>Produto Solicitado</h3>
            <p>
            <strong>Produto:</strong> ' . $dados['produto'] . '<br>
            <strong>Categoria:</strong> ' . $dados['categoria'] . '<br>
            <strong>Quantidade:</strong> ' . $dados['quantidade'] . '</p>
        </div>
        <div style="margin: 20px 0;">
            <h3>Mensagem</h3>
            <p>' . $dados['mensagem'] . '</p>
        </div>
        <p style="color: #666; font-style: italic;">Recebido em ' . $dados['data'] . '</p>
    </body>
    </html>
    ';

    if (send_email("Nova Cotação - " . $dados['produto'], $html_empresa, SMTP_USERNAME, $dados['email'])) {
        // PROTEÇÃO 4: Registra timestamp do envio bem-sucedido
        file_put_contents($rate_limit_file, time());

        header('Content-Type: application/json');
        echo json_encode(['message' => 'Cotação enviada com sucesso!']);
    } else {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Erro ao enviar cotação']);
    }

} catch (Exception $e) {
    error_log('Erro ao enviar cotação: ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Ocorreu um erro inesperado']);
}
}

function handle_enviar_contato_site() {
try {
    // PROTEÇÃO 1: Rate Limiting por IP
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $rate_limit_file = __DIR__ . '/rate_limit_' . md5($ip) . '.txt';

    // Verifica último envio
    if (file_exists($rate_limit_file)) {
        $last_submit = (int)file_get_contents($rate_limit_file);
        $time_diff = time() - $last_submit;

        // Bloqueia se enviou há menos de 10 segundos
        if ($time_diff < 10) {
            error_log("Rate limit atingido para IP: $ip");
            http_response_code(429);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Muitas requisições. Aguarde alguns segundos.']);
            return;
        }
    }

    // PROTEÇÃO 2: Validação de campos obrigatórios
    $dados = [
        'nome' => trim($_POST['name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'telefone' => trim($_POST['phone'] ?? ''),
        'mensagem' => trim($_POST['message'] ?? ''),
        'data' => date('d/m/Y \à\s H:i')
    ];

    if (empty($dados['nome']) || empty($dados['email']) || empty($dados['mensagem'])) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Campos obrigatórios não preenchidos']);
        return;
    }

    // PROTEÇÃO 3: Validação de email
    if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Email inválido']);
        return;
    }

    $html_content = '
    <html><body>
        <h2>Formulário de Contato Recebido - TecPoint</h2>
        <p><strong>Nome:</strong> ' . htmlspecialchars($dados['nome']) . '</p>
        <p><strong>Email:</strong> ' . htmlspecialchars($dados['email']) . '</p>
        <p><strong>Telefone:</strong> ' . htmlspecialchars($dados['telefone'] ?: 'Não informado') . '</p>
        <p><strong>Mensagem:</strong><br>' . nl2br(htmlspecialchars($dados['mensagem'])) . '</p>
        <p><strong>IP:</strong> ' . htmlspecialchars($ip) . '</p>
        <p><em>Recebido em ' . $dados['data'] . '</em></p>
    </body></html>
    ';

    if (send_email('Nova Mensagem - Site TecPoint', $html_content, SMTP_USERNAME, $dados['email'])) {
        // PROTEÇÃO 4: Registra timestamp do envio bem-sucedido
        file_put_contents($rate_limit_file, time());

        header('Content-Type: application/json');
        echo json_encode(['message' => 'Mensagem enviada com sucesso!']);
    } else {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Erro ao enviar mensagem']);
    }

} catch (Exception $e) {
    error_log('Erro: ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Erro ao enviar mensagem']);
}
}

function handle_enviar_contato_form() {
try {
    // PROTEÇÃO 1: Rate Limiting por IP
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $rate_limit_file = __DIR__ . '/rate_limit_tec_' . md5($ip) . '.txt';

    // Verifica último envio
    if (file_exists($rate_limit_file)) {
        $last_submit = (int)file_get_contents($rate_limit_file);
        $time_diff = time() - $last_submit;

        // Bloqueia se enviou há menos de 10 segundos
        if ($time_diff < 10) {
            error_log("Rate limit TEC atingido para IP: $ip");
            http_response_code(429);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Muitas requisições. Aguarde alguns segundos.']);
            return;
        }
    }

    // PROTEÇÃO 2: Validação de campos obrigatórios
    $dados = [
        'nome' => trim($_POST['name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'telefone' => trim($_POST['phone'] ?? ''),
        'mensagem' => trim($_POST['message'] ?? ''),
        'data' => date('d/m/Y \à\s H:i')
    ];

    if (empty($dados['nome']) || empty($dados['email']) || empty($dados['mensagem'])) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Campos obrigatórios não preenchidos']);
        return;
    }

    // PROTEÇÃO 3: Validação de email
    if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Email inválido']);
        return;
    }

    $html_content = '
    <html><body>
        <h2>Formulário de Contato Recebido - TecPoint</h2>
        <p><strong>Nome:</strong> ' . htmlspecialchars($dados['nome']) . '</p>
        <p><strong>Email:</strong> ' . htmlspecialchars($dados['email']) . '</p>
        <p><strong>Telefone:</strong> ' . htmlspecialchars($dados['telefone'] ?: 'Não informado') . '</p>
        <p><strong>Mensagem:</strong><br>' . nl2br(htmlspecialchars($dados['mensagem'])) . '</p>
        <p><strong>IP:</strong> ' . htmlspecialchars($ip) . '</p>
        <p><em>Recebido em ' . $dados['data'] . '</em></p>
    </body></html>
    ';

    if (send_email('Nova Mensagem TEC - Site TecPoint', $html_content, SMTP_USERNAME, $dados['email'])) {
        // PROTEÇÃO 4: Registra timestamp do envio bem-sucedido
        file_put_contents($rate_limit_file, time());

        header('Content-Type: application/json');
        echo json_encode(['message' => 'Mensagem enviada com sucesso!']);
    } else {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Erro ao enviar mensagem']);
    }

} catch (Exception $e) {
    error_log('Erro detalhado: ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Erro ao enviar mensagem']);
}
}

function handle_enviar_servico_form() {
try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $dados = [
        'nome' => trim($input['nome'] ?? ''),
        'email' => trim($input['email'] ?? ''),
        'telefone' => trim($input['telefone'] ?? ''),
        'categoria' => trim($input['categoria'] ?? ''),
        'mensagem' => trim($input['mensagem'] ?? ''),
        'data' => date('d/m/Y \à\s H:i')
    ];

    if (!$dados['nome'] || !$dados['email'] || !$dados['mensagem']) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Os campos Nome, Email e Mensagem são obrigatórios.']);
        return;
    }

    $categorias = [
        'locacao' => 'Locação de Equipamentos',
        'manutencao' => 'Manutenção de Equipamentos',
        'projetos' => 'Projetos Técnicos',
        'legalizacao' => 'Legalização junto à ANATEL',
        'implantacao' => 'Implantação de Sistemas'
    ];
    $categoria_nome = $categorias[$dados['categoria']] ?? 'Categoria não especificada';

    $html_content_cliente = '
    <html>
        <body>
            <h2>Solicitação de Serviço Recebida - TecPoint</h2>
            <p><strong>Prezado(a) ' . $dados['nome'] . ',</strong></p>
            <p>Agradecemos pelo seu contato! Recebemos a sua solicitação para o serviço abaixo:</p>
            <p><strong>Categoria do Serviço:</strong> ' . $categoria_nome . '</p>
            <p><strong>Telefone:</strong> ' . ($dados['telefone'] ?: 'Não informado') . '</p>
            <p><strong>Email:</strong> ' . $dados['email'] . '</p>
            <p><strong>Mensagem:</strong><br>' . $dados['mensagem'] . '</p>
            <p>Em breve, nossa equipe entrará em contato com você pelo telefone ou e-mail informado:</p>
            <p>Atenciosamente,<br>Equipe TecPoint</p>
            <p><em>Recebido em ' . $dados['data'] . '</em></p>
        </body>
    </html>
    ';

    $html_content_empresa = '
    <html>
        <body>
            <h2>Nova Solicitação de Serviço - TecPoint</h2>
            <p><strong>Nome do Cliente:</strong> ' . $dados['nome'] . '</p>
            <p><strong>Email do Cliente:</strong> ' . $dados['email'] . '</p>
            <p><strong>Telefone:</strong> ' . ($dados['telefone'] ?: 'Não informado') . '</p>
            <p><strong>Categoria do Serviço:</strong> ' . $categoria_nome . '</p>
            <p><strong>Mensagem do Cliente:</strong><br>' . $dados['mensagem'] . '</p>
            <p><em>Recebido em ' . $dados['data'] . '</em></p>
        </body>
    </html>
    ';

    $cliente_email_sucesso = send_email(
        'Confirmação de Solicitação de Serviço - TecPoint',
        $html_content_cliente,
        $dados['email']
    );

    $empresa_email_sucesso = send_email(
        'Nova Solicitação de Serviço - TecPoint',
        $html_content_empresa,
        SMTP_USERNAME
    );

    if ($cliente_email_sucesso && $empresa_email_sucesso) {
        header('Content-Type: application/json');
        echo json_encode(['message' => 'Mensagem enviada com sucesso!']);
    } else {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Erro ao enviar mensagem. Por favor, tente novamente.']);
    }

} catch (Exception $e) {
    error_log('Erro detalhado: ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Ocorreu um erro ao processar sua solicitação. Por favor, tente novamente.']);
}
}

// ===== HANDLERS ADMIN MANTIDOS COMPLETOS =====
function handle_admin_login() {
if (is_admin()) {
    header('Location: /admin');
    exit;
}
    
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $admin = new Admin();
    if ($admin->authenticate($username, $password)) {
        flash('Login realizado com sucesso!', 'success');
        header('Location: /admin');
        exit;
    }
    
    flash('Usuário ou senha incorretos', 'error');
}
include 'templates/admin/login.html';
}

function handle_admin_logout() {
unset($_SESSION['admin_logged_in']);
flash('Logout realizado com sucesso!', 'success');
header('Location: /admin/login');
exit;
}

function handle_admin_dashboard() {
$product_model = new Product();
$service_model = new Service();

$products = $product_model->getAll();
$services = $service_model->getAll();

foreach ($products as &$produto) {
    if (isset($produto['specs'])) {
        $produto['specs'] = json_decode($produto['specs'] ?: '[]', true);
    }
    
    if (empty($produto['image_path']) || !validarImagemExiste($produto['image_path'])) {
        $produto['image_path'] = null;
    }
    
    if (isset($produto['image_paths'])) {
        $imagePaths = json_decode($produto['image_paths'] ?: '[]', true);
        $imagensValidas = [];
        
        if (is_array($imagePaths)) {
            foreach ($imagePaths as $imagePath) {
                if (validarImagemExiste($imagePath)) {
                    $imagensValidas[] = $imagePath;
                }
            }
        }
        
        $produto['image_paths'] = $imagensValidas;
    }
}

$template_vars = [
    'products' => $products,
    'services' => $services
];

extract($template_vars);

include 'templates/admin/dashboard.html';
}

function handle_get_product($id) {
try {
    $product_model = new Product();
    $produto = $product_model->getById($id);
    
    if (!$produto) {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Produto não encontrado']);
        return;
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'name' => $produto['name'],
        'description' => $produto['description'],
        'category' => $produto['category'],
        'specs' => json_decode($produto['specs'] ?: '[]', true)
    ]);
    
} catch (Exception $e) {
    error_log("Erro ao buscar produto: " . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Erro ao buscar produto']);
}
}

function handle_admin_edit_product($id) {
error_log("Iniciando handle_admin_edit_product para o produto ID: " . $id);

$product_model = new Product();
$produto = $product_model->getById($id);

if (!$produto) {
    flash('Produto não encontrado', 'error');
    header('Location: /admin');
    exit;
}

try {
    $name = trim((string)($_POST['name'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    $category = trim((string)($_POST['category'] ?? ''));
    $specs = $_POST['specs'] ?? [];
    
    error_log("Dados recebidos do formulário: " . json_encode($_POST));
    error_log("Arquivos recebidos do formulário: " . json_encode($_FILES));

    if (empty($specs) && isset($_POST['spec'])) {
        $specs = $_POST['spec'];
    }

    if (empty($name)) {
        flash('Nome do produto é obrigatório.', 'error');
        header('Location: /admin?tab=produtos');
        exit;
    }
    if (empty($description)) {
        flash('Descrição do produto é obrigatória.', 'error');
        header('Location: /admin?tab=produtos');
        exit;
    }
    if (empty($category)) {
        flash('Categoria do produto é obrigatória.', 'error');
        header('Location: /admin?tab=produtos');
        exit;
    }

    $specs_clean = [];
    if (is_array($specs)) {
        $specs_clean = array_filter(array_map('trim', $specs), 'strlen');
    }
    if (empty($specs_clean)) {
        $specs_clean = [''];
    }

    $update_data = [
        'name' => $name,
        'description' => $description,
        'category' => $category,
        'specs' => json_encode($specs_clean)
    ];

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        if (allowed_file($_FILES['image']['name'])) {
            if ($produto['image_path']) {
                delete_file($produto['image_path']);
            }
            $new_image = save_file($_FILES['image']);
            if ($new_image) {
                $update_data['image_path'] = $new_image;
                error_log("Nova imagem principal salva: " . $new_image);
            }
        }
    }

    if (isset($_FILES['pdf']) && $_FILES['pdf']['error'] === UPLOAD_ERR_OK) {
        if (allowed_file($_FILES['pdf']['name'])) {
            if ($produto['pdf_path']) {
                delete_file($produto['pdf_path']);
            }
            $new_pdf = save_file($_FILES['pdf']);
            if ($new_pdf) {
                $update_data['pdf_path'] = $new_pdf;
                error_log("Novo PDF salvo: " . $new_pdf);
            }
        }
    }

    if (isset($_FILES['images'])) {
        error_log("Processando imagens adicionais...");
        
        $old_images = filtrarImagensValidas($produto['image_paths']);
        error_log("Imagens existentes válidas: " . json_encode($old_images));
        
        $new_images = [];
        $files = $_FILES['images'];
        
        if (isset($files['tmp_name'])) {
            if (is_array($files['tmp_name'])) {
                error_log("Processando múltiplos arquivos de imagem");
                
                foreach ($files['tmp_name'] as $key => $tmp_name) {
                    if (isset($files['error'][$key]) && $files['error'][$key] === UPLOAD_ERR_OK) {
                        $file = [
                            'name' => $files['name'][$key],
                            'tmp_name' => $tmp_name,
                            'error' => $files['error'][$key],
                            'size' => $files['size'][$key] ?? 0
                        ];
                        
                        error_log("Processando arquivo: " . $file['name'] . " - Tamanho: " . $file['size']);
                        
                        if (allowed_file($file['name']) && validate_image($file)) {
                            $img_name = save_file($file);
                            if ($img_name) {
                                $new_images[] = $img_name;
                                error_log("Nova imagem adicional salva: " . $img_name);
                            } else {
                                error_log("Erro ao salvar imagem: " . $file['name']);
                            }
                        } else {
                            error_log("Arquivo não permitido: " . $file['name']);
                        }
                    } else {
                        error_log("Erro no upload do arquivo " . $key . ": " . ($files['error'][$key] ?? 'desconhecido'));
                      }
                  }
              } 
              else if ($files['error'] === UPLOAD_ERR_OK) {
                  error_log("Processando arquivo único de imagem");
                  
                  if (allowed_file($files['name'])) {
                      $img_name = save_file($files);
                      if ($img_name) {
                          $new_images[] = $img_name;
                          error_log("Nova imagem adicional salva: " . $img_name);
                      }
                  }
              }
          }
          
          if (!empty($new_images)) {
              $all_images = array_merge($old_images, $new_images);
              $update_data['image_paths'] = json_encode($all_images);
              error_log("Todas as imagens combinadas: " . json_encode($all_images));
          } else {
              error_log("Nenhuma nova imagem foi processada");
              if (!empty($old_images)) {
                  $update_data['image_paths'] = json_encode($old_images);
              }
          }
      } else {
          error_log("Nenhum arquivo de imagem adicional foi enviado");
      }

      error_log("Dados finais a serem atualizados: " . json_encode($update_data));

      if ($product_model->update($id, $update_data)) {
          flash('Produto "' . $name . '" atualizado com sucesso!', 'success');
          error_log("Produto atualizado com sucesso!");
      } else {
          flash('Erro ao atualizar produto.', 'error');
          error_log("Erro ao atualizar produto no banco de dados");
      }
      
      header('Location: /admin?tab=produtos');
      exit;

  } catch (Exception $e) {
      error_log("Erro ao atualizar produto: " . $e->getMessage());
      error_log("Stack trace: " . $e->getTraceAsString());
      flash('Erro: ' . $e->getMessage(), 'error');
      header('Location: /admin?tab=produtos');
      exit;
  }
}

function handle_admin_add_product() {
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      try {
          $name = isset($_POST['name']) ? trim($_POST['name']) : '';
          $description = isset($_POST['description']) ? trim($_POST['description']) : '';
          $category = isset($_POST['category']) ? trim($_POST['category']) : '';
          
          $specs = [];
          if (isset($_POST['spec'])) {
              if (is_array($_POST['spec'])) {
                  $specs = array_filter(array_map('trim', $_POST['spec']), 'strlen');
              } else {
                  $spec_value = trim($_POST['spec']);
                  if (!empty($spec_value)) {
                      $specs = [$spec_value];
                  }
              }
          }

          if (empty($name)) {
              flash('Nome do produto é obrigatório.', 'error');
              header('Location: /admin/produtos/adicionar');
              exit;
          }
          if (empty($description)) {
              flash('Descrição do produto é obrigatória.', 'error');
              header('Location: /admin/produtos/adicionar');
              exit;
          }
          if (empty($category)) {
              flash('Categoria do produto é obrigatória.', 'error');
              header('Location: /admin/produtos/adicionar');
              exit;
          }
          if (empty($specs)) {
              $specs = [''];
          }

          $image_filename = '';
          if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
              if (allowed_file($_FILES['image']['name'])) {
                  $image_filename = save_file($_FILES['image']);
                  if (!$image_filename) {
                      $image_filename = '';
                      flash('Erro ao salvar imagem principal.', 'error');
                  }
              }
          }

          $pdf_filename = null;
          if (isset($_FILES['pdf']) && $_FILES['pdf']['error'] === UPLOAD_ERR_OK) {
              if (allowed_file($_FILES['pdf']['name'])) {
                  $pdf_filename = save_file($_FILES['pdf']);
              }
          }

          $additional_images = [];
          if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
              foreach ($_FILES['images']['name'] as $key => $name) {
                  if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                      $file = [
                          'name' => $name,
                          'tmp_name' => $_FILES['images']['tmp_name'][$key],
                          'error' => $_FILES['images']['error'][$key],
                          'size' => $_FILES['images']['size'][$key] ?? 0
                      ];
                      if (allowed_file($file['name']) && validate_image($file)) {
                          $img_name = save_file($file);
                          if ($img_name) {
                              $additional_images[] = $img_name;
                          }
                      }
                  }
              }
          }

          $product_data = [
              'name' => $name,
              'description' => $description ?: '',
              'category' => $category ?: 'Geral',
              'specs' => json_encode($specs),
              'image_path' => $image_filename,
              'pdf_path' => $pdf_filename,
              'image_paths' => !empty($additional_images) ? json_encode($additional_images) : null
          ];
          
          error_log("Dados do produto a serem salvos: " . json_encode($product_data));

          $product_model = new Product();
          if ($product_model->create($product_data)) {
              flash('Produto "' . $name . '" adicionado com sucesso!', 'success');
              header('Location: /admin');
              exit;
          } else {
              flash('Erro ao salvar produto.', 'error');
              header('Location: /admin/produtos/adicionar');
              exit;
          }

      } catch (Exception $e) {
          error_log("Erro ao adicionar produto: " . $e->getMessage());
          flash('Erro: ' . $e->getMessage(), 'error');
          header('Location: /admin/produtos/adicionar');
          exit;
      }
  }

  include 'templates/admin/add_product.html';
}

function handle_admin_delete_product($id) {
  $product_model = new Product();
  $produto = $product_model->getById($id);
  
  if (!$produto) {
      flash('Produto não encontrado', 'error');
      header('Location: /admin');
      exit;
  }
  
  try {
      if ($produto['image_path']) {
          delete_file($produto['image_path']);
      }
      
      if ($produto['pdf_path']) {
          delete_file($produto['pdf_path']);
      }
      
      if ($produto['image_paths']) {
          $extra_images = json_decode($produto['image_paths'], true);
          if ($extra_images && is_array($extra_images)) {
              foreach ($extra_images as $img_file) {
                  delete_file($img_file);
              }
          }
      }
      
      if ($product_model->delete($id)) {
          flash('Produto excluído com sucesso!', 'success');
      } else {
          flash('Erro ao excluir produto', 'error');
      }

  } catch (Exception $e) {
      error_log("Erro ao excluir produto: " . $e->getMessage());
      flash('Erro ao excluir produto', 'error');
  }
  
  header('Location: /admin');
  exit;
}

function handle_get_service($id) {
  try {
      $service_model = new Service();
      $servico = $service_model->getById($id);
      
      if (!$servico) {
          http_response_code(404);
          header('Content-Type: application/json');
          echo json_encode(['error' => 'Serviço não encontrado']);
          return;
      }
      
      header('Content-Type: application/json');
      echo json_encode([
          'name' => $servico['name'],
          'description' => $servico['description'],
          'category' => $servico['category'],
          'features' => $servico['features']
      ]);
      
  } catch (Exception $e) {
      error_log("Erro ao buscar serviço: " . $e->getMessage());
      http_response_code(500);
      header('Content-Type: application/json');
      echo json_encode(['error' => 'Erro ao buscar serviço']);
  }
}

function handle_admin_edit_service($id) {
  $service_model = new Service();
  $servico = $service_model->getById($id);
  
  if (!$servico) {
      flash('Serviço não encontrado', 'error');
      header('Location: /admin');
      exit;
  }
  
  try {
      $name = trim($_POST['name'] ?? '');
      $description = trim($_POST['description'] ?? '');
      $category = trim($_POST['category'] ?? '');
      $features = $_POST['features'] ?? [];
      $current_tab = $_POST['current_tab'] ?? 'servicos';

      if (empty($name)) {
          flash('Nome do serviço é obrigatório.', 'error');
          header('Location: /admin?tab=' . $current_tab);
          exit;
      }

      $features_clean = [];
      if (is_array($features)) {
          $features_clean = array_filter(array_map('trim', $features), 'strlen');
      }
      if (empty($features_clean)) {
          $features_clean = [''];
      }

      $update_data = [
          'name' => $name,
          'description' => $description,
          'category' => $category,
          'features' => json_encode($features_clean)
      ];

      if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
          if (allowed_file($_FILES['image']['name'])) {
              $old_image = $servico['image_path'];
              $new_image = save_file($_FILES['image']);
              if ($new_image) {
                  $update_data['image_path'] = $new_image;
                  if ($old_image) {
                      delete_file($old_image);
                  }
              }
          }
      }

      if ($service_model->update($id, $update_data)) {
          flash('Serviço "' . $name . '" atualizado com sucesso!', 'success');
      } else {
          flash('Erro ao atualizar serviço.', 'error');
      }

      header('Location: /admin?tab=' . $current_tab);
      exit;

  } catch (Exception $e) {
      error_log("Erro ao atualizar serviço: " . $e->getMessage());
      flash('Erro: ' . $e->getMessage(), 'error');
      header('Location: /admin?tab=servicos');
      exit;
  }
}

function handle_admin_add_service() {
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      try {
          error_log("=== ADICIONANDO NOVO SERVIÇO ===");
          error_log("POST recebido: " . json_encode($_POST));
          error_log("FILES recebido: " . json_encode($_FILES));
          
          $name = trim($_POST['name'] ?? '');
          $description = trim($_POST['description'] ?? '');
          $category = trim($_POST['category'] ?? '');
          $features = $_POST['features'] ?? [];

          error_log("Dados do serviço:");
          error_log("Nome: $name");
          error_log("Descrição: " . substr($description, 0, 100) . "...");
          error_log("Categoria: $category");
          error_log("Features: " . json_encode($features));

          if (empty($name)) {
              error_log("Erro: Nome vazio");
              flash('Nome do serviço é obrigatório.', 'error');
              header('Location: /admin/servicos/adicionar');
              exit;
          }

          if (empty($description)) {
              error_log("Erro: Descrição vazia");
              flash('Descrição do serviço é obrigatória.', 'error');
              header('Location: /admin/servicos/adicionar');
              exit;
          }

          if (empty($category)) {
              error_log("Erro: Categoria vazia");
              flash('Categoria do serviço é obrigatória.', 'error');
              header('Location: /admin/servicos/adicionar');
              exit;
          }

          $features_clean = [];
          if (is_array($features)) {
              $features_clean = array_filter(array_map('trim', $features), 'strlen');
          }
          if (empty($features_clean)) {
              $features_clean = ['Serviço especializado'];
          }

          error_log("Features processadas: " . json_encode($features_clean));

          $image_filename = null;
          if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
              error_log("Processando imagem do serviço...");
              error_log("Nome do arquivo: " . $_FILES['image']['name']);
              error_log("Tamanho: " . $_FILES['image']['size']);
              error_log("Tipo: " . $_FILES['image']['type']);
              error_log("Tmp name: " . $_FILES['image']['tmp_name']);
              
              if (allowed_file($_FILES['image']['name'])) {
                  if (!file_exists(UPLOAD_FOLDER)) {
                      mkdir(UPLOAD_FOLDER, 0755, true); // 755 em vez de 777
                      error_log("Pasta de upload criada: " . UPLOAD_FOLDER);
                  }
                  
                  $image_filename = save_file($_FILES['image']);
                  if ($image_filename) {
                      error_log("✓ Imagem do serviço salva com sucesso: " . $image_filename);
                      error_log("Caminho completo: " . UPLOAD_FOLDER . '/' . $image_filename);
                      error_log("Arquivo existe? " . (file_exists(UPLOAD_FOLDER . '/' . $image_filename) ? 'SIM' : 'NÃO'));
                  } else {
                      error_log("✗ ERRO ao salvar imagem do serviço");
                      flash('Erro ao salvar imagem do serviço.', 'error');
                      header('Location: /admin/servicos/adicionar');
                      exit;
                  }
              } else {
                  error_log("✗ Arquivo de imagem não permitido: " . $_FILES['image']['name']);
                  flash('Formato de imagem não permitido. Use JPG, PNG ou WebP.', 'error');
                  header('Location: /admin/servicos/adicionar');
                  exit;
              }
          } else {
              $upload_error = $_FILES['image']['error'] ?? 'campo não enviado';
              error_log("Nenhuma imagem ou erro no upload: " . $upload_error);
              
              if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                  $error_messages = [
                      UPLOAD_ERR_INI_SIZE => 'Arquivo muito grande (excede upload_max_filesize)',
                      UPLOAD_ERR_FORM_SIZE => 'Arquivo muito grande (excede MAX_FILE_SIZE)',
                      UPLOAD_ERR_PARTIAL => 'Upload incompleto',
                      UPLOAD_ERR_NO_TMP_DIR => 'Pasta temporária inexistente',
                      UPLOAD_ERR_CANT_WRITE => 'Erro ao escrever arquivo',
                      UPLOAD_ERR_EXTENSION => 'Upload bloqueado por extensão'
                  ];
                  
                  $error_msg = $error_messages[$_FILES['image']['error']] ?? 'Erro desconhecido';
                  error_log("Erro detalhado no upload: " . $error_msg);
                  flash('Erro no upload da imagem: ' . $error_msg, 'error');
                  header('Location: /admin/servicos/adicionar');
                  exit;
              }
          }

          $service_data = [
              'name' => $name,
              'description' => $description,
              'category' => $category,
              'features' => json_encode($features_clean, JSON_UNESCAPED_UNICODE),
              'image_path' => $image_filename
          ];

          error_log("Dados finais do serviço para salvar:");
          error_log(json_encode($service_data, JSON_PRETTY_PRINT));

          $service_model = new Service();
          if ($service_model->create($service_data)) {
              error_log("✓ Serviço criado com sucesso no banco de dados!");
              flash('Serviço "' . $name . '" adicionado com sucesso!', 'success');
              header('Location: /admin?tab=servicos');
              exit;
          } else {
              error_log("✗ ERRO ao criar serviço no banco de dados");
              flash('Erro ao salvar serviço no banco de dados.', 'error');
              header('Location: /admin/servicos/adicionar');
              exit;
          }

      } catch (Exception $e) {
          error_log("EXCEÇÃO ao adicionar serviço: " . $e->getMessage());
          error_log("Stack trace: " . $e->getTraceAsString());
          flash('Erro: ' . $e->getMessage(), 'error');
          header('Location: /admin/servicos/adicionar');
          exit;
      }
  }

  include 'templates/admin/add_service.html';
}

function handle_admin_delete_service($id) {
  $service_model = new Service();
  $servico = $service_model->getById($id);

  if (!$servico) {
      flash('Serviço não encontrado', 'error');
      header('Location: /admin');
      exit;
  }

  try {
      if ($servico['image_path']) {
          delete_file($servico['image_path']);
      }
      
      if ($service_model->delete($id)) {
          flash('Serviço excluído com sucesso!', 'success');
      } else {
          flash('Erro ao excluir serviço', 'error');
      }

  } catch (Exception $e) {
      error_log("Erro ao excluir serviço: " . $e->getMessage());
      flash('Erro ao excluir serviço', 'error');
  }

  header('Location: /admin');
  exit;
}

function handle_admin_delete_product_image($id) {
  try {
      $product_model = new Product();
      $produto = $product_model->getById($id);
      
      if (!$produto) {
          http_response_code(404);
          header('Content-Type: application/json');
          echo json_encode(['error' => 'Produto não encontrado']);
          return;
      }
      
      if ($produto['image_path']) {
          delete_file($produto['image_path']);
          $update_data = ['image_path' => null];
          $product_model->update($id, $update_data);
      }

      header('Content-Type: application/json');
      echo json_encode(['message' => 'Imagem excluída com sucesso!']);

  } catch (Exception $e) {
      error_log("Erro ao excluir imagem: " . $e->getMessage());
      http_response_code(500);
      header('Content-Type: application/json');
      echo json_encode(['error' => 'Erro ao excluir imagem']);
  }
}

function handle_admin_delete_additional_product_image($id) {
  try {
      $product_model = new Product();
      $produto = $product_model->getById($id);
      
      if (!$produto) {
          http_response_code(404);
          header('Content-Type: application/json');
          echo json_encode(['error' => 'Produto não encontrado']);
          return;
      }
      
      $data = json_decode(file_get_contents('php://input'), true);
      $image_path = $data['image_path'] ?? '';

      if (!$image_path) {
          http_response_code(400);
          header('Content-Type: application/json');
          echo json_encode(['error' => 'Caminho da imagem não fornecido']);
          return;
      }

      if ($produto['image_paths']) {
          $images = json_decode($produto['image_paths'], true);
          if ($images && is_array($images) && in_array($image_path, $images)) {
              
              delete_file($image_path);
              
              $images = array_values(array_diff($images, [$image_path]));
              $update_data = ['image_paths' => !empty($images) ? json_encode($images) : null];
              $product_model->update($id, $update_data);

              error_log("Imagem adicional excluída com sucesso: " . $image_path);
              
              header('Content-Type: application/json');
              echo json_encode(['message' => 'Imagem adicional excluída com sucesso!']);
              return;
          } else {
              http_response_code(404);
              header('Content-Type: application/json');
              echo json_encode(['error' => 'Imagem não encontrada na lista de imagens adicionais']);
              return;
          }
      } else {
          http_response_code(404);
          header('Content-Type: application/json');
          echo json_encode(['error' => 'Produto não possui imagens adicionais']);
          return;
      }

  } catch (Exception $e) {
      error_log("Erro ao excluir imagem adicional: " . $e->getMessage());
      http_response_code(500);
      header('Content-Type: application/json');
      echo json_encode(['error' => 'Erro ao excluir imagem adicional']);
  }
}

function handle_admin_delete_product_pdf($id) {
  try {
      $product_model = new Product();
      $produto = $product_model->getById($id);
      
      if (!$produto) {
          http_response_code(404);
          header('Content-Type: application/json');
          echo json_encode(['error' => 'Produto não encontrado']);
          return;
      }
      
      if ($produto['pdf_path']) {
          delete_file($produto['pdf_path']);
          $update_data = ['pdf_path' => null];
          $product_model->update($id, $update_data);
          
          header('Content-Type: application/json');
          echo json_encode(['message' => 'PDF excluído com sucesso!']);
      } else {
          http_response_code(404);
          header('Content-Type: application/json');
          echo json_encode(['error' => 'Produto não possui PDF']);
      }
      
  } catch (Exception $e) {
      error_log("Erro ao excluir PDF: " . $e->getMessage());
      http_response_code(500);
      header('Content-Type: application/json');
      echo json_encode(['error' => 'Erro ao excluir PDF']);
  }
}

function handle_admin_delete_service_image($id) {
  try {
      $service_model = new Service();
      $servico = $service_model->getById($id);
      
      if (!$servico) {
          http_response_code(404);
          header('Content-Type: application/json');
          echo json_encode(['error' => 'Serviço não encontrado']);
          return;
      }
      
      if ($servico['image_path']) {
          delete_file($servico['image_path']);
          $update_data = ['image_path' => null];
          $service_model->update($id, $update_data);
          
          header('Content-Type: application/json');
          echo json_encode(['message' => 'Imagem excluída com sucesso!']);
      } else {
          http_response_code(404);
          header('Content-Type: application/json');
          echo json_encode(['error' => 'Serviço não possui imagem']);
      }
      
  } catch (Exception $e) {
      error_log("Erro ao excluir imagem: " . $e->getMessage());
      http_response_code(500);
      header('Content-Type: application/json');
      echo json_encode(['error' => 'Erro ao excluir imagem']);
  }
}

function handle_uploaded_file($filename) {
  try {
      if ($filename !== secure_filename($filename)) {
          http_response_code(403);
          echo "Acesso negado";
          return;
      }
          
      $file_path = UPLOAD_FOLDER . '/' . $filename;
      if (!file_exists($file_path)) {
          http_response_code(404);
          echo "Arquivo não encontrado";
          return;
      }
      
      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $mime_type = finfo_file($finfo, $file_path);
      finfo_close($finfo);
      
      header('Content-Type: ' . $mime_type);
      header('Content-Length: ' . filesize($file_path));
      header('Cache-Control: public, max-age=31536000');
      
      readfile($file_path);
      
  } catch (Exception $e) {
      error_log("Erro ao servir arquivo $filename: " . $e->getMessage());
      http_response_code(500);
      echo "Erro ao acessar arquivo";
  }
}

// ===== FUNÇÕES AUXILIARES MANTIDAS COMPLETAS =====
function is_valid_email($email) {
  try {
      $parts = explode('@', $email);
      return count($parts) === 2 && strlen($parts[0]) > 0 && strlen($parts[1]) > 3 && strpos($parts[1], '.') !== false;
  } catch (Exception $e) {
      return false;
  }
}

function save_failed_email($dados) {
  try {
      $failed_data = [
          'timestamp' => date('c'),
          'dados' => $dados
      ];
      file_put_contents('failed_emails.json', json_encode($failed_data) . "\n", FILE_APPEND | LOCK_EX);
  } catch (Exception $e) {
      error_log("Erro ao salvar email falho: " . $e->getMessage());
  }
}

function clean_filename($filename) {
  $base = secure_filename($filename);
  $name_ext = pathinfo($base);
  $name = $name_ext['filename'];
  $ext = isset($name_ext['extension']) ? '.' . $name_ext['extension'] : '';
  $timestamp = date('Ymd_His');
  return $name . '_' . $timestamp . $ext;
}

function validate_image($file) {
  if (!$file) return false;

  try {
      if (!allowed_file($file['name'])) {
          return false;
      }
      
      if ($file['size'] > 50 * 1024 * 1024) {
          flash('A imagem deve ter no máximo 50MB.', 'error');
          return false;
      }
          
      return true;
  } catch (Exception $e) {
      error_log("Erro ao validar imagem: " . $e->getMessage());
      return false;
  }
}

function add_security_headers() {
  header('X-Content-Type-Options: nosniff');
  header('X-Frame-Options: SAMEORIGIN');
  header('X-XSS-Protection: 1; mode=block');
}

function json_loads_filter($json_string) {
  try {
      if (is_array($json_string)) {
          return $json_string;
      }
      return json_decode($json_string ?: '[]', true) ?: [];
  } catch (Exception $e) {
      return [];
  }
}

// ===== FUNÇÃO PRINCIPAL =====
function main() {
  init_database();
  check_upload_folder();
  add_security_headers();
  route();
}

// ===== INICIALIZAÇÃO =====
if (php_sapi_name() !== 'cli') {
  main();
}
?>
