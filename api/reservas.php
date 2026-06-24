<?php
/**
 * API para gestão de reservas - Restaurante 100 Sabores
 * Endpoint: /api/reservas.php
 */

// Resposta para preflight requests (CORS) - deve ser primeiro!
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    http_response_code(200);
    exit;
}

// Configurar headers CORS e JSON
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

// Iniciar output buffering para evitar warnings a poluir a resposta
ob_start();

// Ativar erros PHP para diagnóstico (pode ser desativado em produção)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

/**
 * Enviar resposta JSON limpa
 */
function json_response($data, $code = 200) {
    ob_clean();
    http_response_code($code);
    echo json_encode($data);
    exit;
}

// Carregar dependências
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Reserva.php';
require_once __DIR__ . '/../classes/EmailSender.php';
require_once __DIR__ . '/../classes/Mesa.php';

// ============================================
// INSTALAÇÃO/ATUALIZAÇÃO DO BANCO
// ============================================
if (isset($_GET['install']) && $_GET['install'] == 'true') {
    executarMigration();
    exit;
}

function executarMigration() {
    global $db;
    try {
        // Ler arquivo SQL
        $sql = file_get_contents(__DIR__ . '/../sql/database_completo.sql');
        $sql = preg_replace('/^(#|--).*$/m', '', $sql);

        // Dividir em statements (separa por ;)
        $statements = array_filter(array_map('trim', explode(';', $sql)));

        foreach ($statements as $stmt) {
            if (empty($stmt)) continue;
            try {
                $db->exec($stmt);
            } catch (PDOException $e) {
                // Ignorar erros de "already exists"
                if (strpos($e->getMessage(), 'already exists') === false &&
                    strpos($e->getMessage(), 'Duplicate') === false) {
                    throw $e;
                }
            }
        }

        http_response_code(200);
        echo json_encode(['sucesso' => true, 'mensagem' => 'Migration executada com sucesso']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
    }
    exit;
}

// Obter conexão com a base de dados
$database = new Database();
$db = $database->getConnection();

// Debug: verificar se a conexão funciona
if (!$db) {
    http_response_code(500);
    echo json_encode(['erro' => 'Falha na conexão à base de dados', 'debug' => 'Verificar se MySQL está a correr no XAMPP']);
    exit;
}

// Testar query simples
try {
    $test = $db->query("SELECT 1");
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro na base de dados: ' . $e->getMessage()]);
    exit;
}

// Obter método HTTP
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? null;

// Processar diferentes ações
switch($method) {
    case 'POST':
        if ($action === 'disponibilidade') {
            verificarDisponibilidade();
        } else {
            criarReserva();
        }
        break;
    case 'GET':
        if ($action === 'mesas') {
            listarMesas();
        } elseif ($action === 'horarios') {
            listarHorariosDisponiveis();
        } elseif ($action === 'estatisticas') {
            obterEstatisticas();
        } else {
            obterReservas();
        }
        break;
    case 'PUT':
        atualizarReserva();
        break;
    case 'DELETE':
        eliminarReserva();
        break;
    default:
        http_response_code(405);
        echo json_encode(['erro' => 'Método não permitido']);
        break;
}

// Carregar classes adicionais
require_once __DIR__ . '/../classes/Mesa.php';

// Incluir classe de estatísticas (opcional)
require_once __DIR__ . '/../classes/Reserva.php';

/**
 * Criar nova reserva com associação automática de mesa
 */
function criarReserva() {
    global $db;
    try {
        // Obter dados do POST
        $input = json_decode(file_get_contents('php://input'), true);

        // Se não for JSON, tentar obter do $_POST
        if(!$input) {
            $input = $_POST;
        }

        // Criar instância da reserva
        $reserva = new Reserva($db);
        $reserva->nome = $input['nome'] ?? '';
        $reserva->email = $input['email'] ?? '';
        $reserva->telefone = $input['telefone'] ?? '';
        $reserva->data_reserva = $input['data'] ?? '';
        $reserva->hora_reserva = $input['hora'] ?? '';
        $reserva->num_pessoas = $input['num_pessoas'] ?? 0;
        $reserva->observacoes = $input['observacoes'] ?? '';
        $reserva->duracao_minutos = $input['duracao'] ?? Config::DURACAO_PADRAO_RESERVA;

        // Validar dados
        $erros = $reserva->validar();
        if(!empty($erros)) {
            http_response_code(400);
            json_response([
                'sucesso' => false,
                'erros' => $erros
            ], 400);
            return;
        }

        // Verificar disponibilidade geral (capacidade)
        if(!$reserva->verificarDisponibilidade($reserva->data_reserva, $reserva->hora_reserva, $reserva->num_pessoas)) {
            http_response_code(400);
            json_response([
                'sucesso' => false,
                'erro' => 'Não há vagas disponíveis para este horário. Por favor, escolha outro.'
            ], 400);
            return;
        }

        // Criar reserva (gera ID e já associa mesa automaticamente no criar())
        if(!$reserva->criar()) {
            throw new Exception('Falha ao inserir reserva no banco');
        }

        // Carregar info da mesa que foi associada dinamicamente no criar()
        $mesa_associada = null;
        if ($reserva->mesa_id) {
            $mesa = new Mesa($db);
            if ($mesa->obterPorId($reserva->mesa_id)) {
                $mesa_associada = [
                    'id' => $mesa->id,
                    'numero' => $mesa->numero,
                    'capacidade' => $mesa->capacidade,
                    'localizacao' => $mesa->localizacao
                ];
            }
        }

        // Enviar emails
        $emailSender = new EmailSender();
        $email_enviado = $emailSender->enviarConfirmacaoReserva($reserva);
        $notificacao_enviada = $emailSender->enviarNotificacaoRestaurante($reserva);

        http_response_code(201);
        json_response([
            'sucesso' => true,
            'mensagem' => 'Reserva criada com sucesso',
            'id' => $reserva->id,
            'mesa' => $mesa_associada ? [
                'id' => $mesa_associada['id'],
                'numero' => $mesa_associada['numero'],
                'capacidade' => $mesa_associada['capacidade'],
                'localizacao' => $mesa_associada['localizacao']
            ] : null,
            'email_enviado' => $email_enviado,
            'notificacao_enviada' => $notificacao_enviada
        ]);

    } catch(Exception $e) {
        error_log("Erro ao criar reserva: " . $e->getMessage());
        http_response_code(500);
        json_response([
            'sucesso' => false,
            'erro' => 'Erro interno do servidor'
        ], 500);
    }
}

/**
 * Obter reservas
 */
function obterReservas() {
    global $db;
    try {
        $reserva = new Reserva($db);
        
        // Verificar se é para uma data específica
        $data = $_GET['data'] ?? null;
        
        if($data) {
            $reservas = $reserva->obterPorData($data);
        } else {
            $limite = $_GET['limite'] ?? 50;
            $reservas = $reserva->obterTodas($limite);
        }

        json_response([
            'sucesso' => true,
            'reservas' => $reservas,
            'total' => count($reservas)
        ]);

    } catch(Exception $e) {
        http_response_code(500);
        echo json_encode([
            'sucesso' => false,
            'erro' => $e->getMessage()
        ]);
    }
}

/**
 * Atualizar reserva
 * Suporta atualização de status apenas OU edição completa dos dados
 */
function atualizarReserva() {
    global $db;
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? $_GET['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['sucesso' => false, 'erro' => 'ID da reserva é obrigatório']);
            return;
        }

        $reserva = new Reserva($db);

        // ---- Edição completa (quando vêm campos de dados) ----
        $temDadosCompletos = isset($input['nome']) || isset($input['email']) ||
                             isset($input['telefone']) || isset($input['data']) ||
                             isset($input['hora']) || isset($input['num_pessoas']);

        if ($temDadosCompletos) {
            // Validar campos obrigatórios
            $erros = [];
            if (empty($input['nome']))       $erros[] = 'Nome é obrigatório';
            if (empty($input['email']))      $erros[] = 'Email é obrigatório';
            if (empty($input['telefone']))   $erros[] = 'Telefone é obrigatório';
            if (empty($input['data']))       $erros[] = 'Data é obrigatória';
            if (empty($input['hora']))       $erros[] = 'Hora é obrigatória';
            if (empty($input['num_pessoas'])) $erros[] = 'Número de pessoas é obrigatório';

            if (!empty($erros)) {
                http_response_code(400);
                echo json_encode(['sucesso' => false, 'erros' => $erros]);
                return;
            }

            // Montar query de atualização completa
            $query = "UPDATE reservas SET
                        nome           = :nome,
                        email          = :email,
                        telefone       = :telefone,
                        data_reserva   = :data_reserva,
                        hora_reserva   = :hora_reserva,
                        num_pessoas    = :num_pessoas,
                        observacoes    = :observacoes,
                        status         = :status,
                        atualizada_em  = NOW()
                      WHERE id = :id";

            $stmt = $db->prepare($query);
            $stmt->bindValue(':nome',        htmlspecialchars(strip_tags($input['nome'])));
            $stmt->bindValue(':email',       htmlspecialchars(strip_tags($input['email'])));
            $stmt->bindValue(':telefone',    htmlspecialchars(strip_tags($input['telefone'])));
            $stmt->bindValue(':data_reserva', htmlspecialchars(strip_tags($input['data'])));
            $stmt->bindValue(':hora_reserva', htmlspecialchars(strip_tags($input['hora'])));
            $stmt->bindValue(':num_pessoas', (int)$input['num_pessoas'], PDO::PARAM_INT);
            $stmt->bindValue(':observacoes', htmlspecialchars(strip_tags($input['observacoes'] ?? '')));
            $stmt->bindValue(':status',      htmlspecialchars(strip_tags($input['status'] ?? 'pendente')));
            $stmt->bindValue(':id',          (int)$id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                // Tentar reassociar mesa se data/hora/pessoas mudaram
                $mesa_retorno = null;
                try {
                    require_once __DIR__ . '/../classes/Mesa.php';
                    $mesaAssociada = Mesa::associarMesaAutomatica(
                        $db,
                        (int)$id,
                        (int)$input['num_pessoas'],
                        $input['data'],
                        $input['hora']
                    );
                    if ($mesaAssociada) {
                        $mesa_retorno = [
                            'id' => $mesaAssociada['id'],
                            'numero' => $mesaAssociada['numero'],
                            'capacidade' => $mesaAssociada['capacidade'],
                            'localizacao' => $mesaAssociada['localizacao']
                        ];
                    }
                } catch (Exception $e2) {
                    // Não bloquear se falhar a reassociação de mesa
                }

                json_response([
                    'sucesso' => true,
                    'mensagem' => 'Reserva atualizada com sucesso',
                    'mesa' => $mesa_retorno
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['sucesso' => false, 'erro' => 'Erro ao atualizar reserva']);
            }
            return;
        }

        // ---- Atualização apenas de status ----
        $status = $input['status'] ?? null;
        if (!$status) {
            http_response_code(400);
            echo json_encode(['sucesso' => false, 'erro' => 'Nenhum dado para atualizar']);
            return;
        }

        if ($reserva->atualizarStatus($id, $status)) {
            echo json_encode(['sucesso' => true, 'mensagem' => 'Estado atualizado com sucesso']);
        } else {
            http_response_code(500);
            echo json_encode(['sucesso' => false, 'erro' => 'Erro ao atualizar estado']);
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
    }
}

/**
 * Eliminar reserva
 */
function eliminarReserva() {
    global $db;
    try {
        $id = $_GET['id'] ?? null;

        if(!$id) {
            http_response_code(400);
            echo json_encode([
                'sucesso' => false,
                'erro' => 'ID é obrigatório'
            ]);
            return;
        }

        $reserva = new Reserva($db);
        if($reserva->eliminar($id)) {
            json_response([
                'sucesso' => true,
                'mensagem' => 'Reserva eliminada com sucesso'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'sucesso' => false,
                'erro' => 'Erro ao eliminar reserva'
            ]);
        }

    } catch(Exception $e) {
        http_response_code(500);
        echo json_encode([
            'sucesso' => false,
            'erro' => $e->getMessage()
        ]);
    }
}

// ============================================
// NOVOS ENDPOINTS - GESTÃO DE MESAS E TURNOS
// ============================================

/**
 * Listar todas as mesas ativas
 */
function listarMesas() {
    global $db;
    try {
        $mesa = new Mesa($db);
        $apenas_ativas = $_GET['ativas'] ?? true;
        $mesas = $mesa->listarTodas($apenas_ativas);
        echo json_encode(['sucesso' => true, 'mesas' => $mesas, 'total' => count($mesas)]);
    } catch(Exception $e) {
        http_response_code(500);
        echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
    }
}

/**
 * Obter horários disponíveis para uma data
 */
function listarHorariosDisponiveis() {
    global $db;
    try {
        $data = $_GET['data'] ?? date('Y-m-d');
        $horarios_base = ['12:00', '12:30', '13:00', '13:30', '19:00', '19:30', '20:00', '20:30', '21:00', '21:30'];
        $horarios_disponiveis = [];
        foreach ($horarios_base as $hora) {
            $query = "SELECT SUM(num_pessoas) as total FROM reservas WHERE data_reserva = :data AND status IN ('pendente', 'confirmada') AND (TIME(hora_reserva) < ADDTIME(:hora, SEC_TO_TIME(120 * 60)) AND ADDTIME(hora_reserva, SEC_TO_TIME(duracao_minutos * 60)) > :hora)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':data', $data);
            $stmt->bindParam(':hora', $hora);
            $stmt->execute();
            $result = $stmt->fetch();
            $ocupacao = $result['total'] ?? 0;
            $vagas = max(0, Config::CAPACIDADE_MAXIMA - $ocupacao);
            if ($vagas > 0) {
                $horarios_disponiveis[] = ['hora' => $hora, 'vagas' => $vagas, 'ocupacao' => $ocupacao];
            }
        }
        json_response(['sucesso' => true, 'data' => $data, 'horarios' => $horarios_disponiveis]);
    } catch(Exception $e) {
        json_response(['sucesso' => false, 'erro' => $e->getMessage()], 500);
    }
}

/**
 * Verificar disponibilidade detalhada
 */
function verificarDisponibilidade() {
    global $db;
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $data = $input['data'] ?? '';
        $hora = $input['hora'] ?? '';
        $pessoas = $input['num_pessoas'] ?? 1;

        $reserva = new Reserva($db);
        $disponivel = $reserva->verificarDisponibilidade($data, $hora, $pessoas);

        $query = "SELECT SUM(num_pessoas) as total FROM reservas
                WHERE data_reserva = :data
                  AND status IN ('pendente', 'confirmada')
                  AND (TIME(hora_reserva) < ADDTIME(:hora, INTERVAL 2 HOUR)
                       AND ADDTIME(hora_reserva, INTERVAL duracao_minutos MINUTE) > :hora)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':data', $data);
        $stmt->bindParam(':hora', $hora);
        $stmt->execute();
        $result = $stmt->fetch();
        $ocupacao = $result['total'] ?? 0;

        json_response([
            'sucesso' => true,
            'disponivel' => $disponivel,
            'ocupacao' => (int)$ocupacao,
            'capacidade_maxima' => (int)Config::CAPACIDADE_MAXIMA
        ]);
    } catch(Exception $e) {
        http_response_code(500);
        echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
    }
}

/**
 * Obter estatísticas da dashboard
 */
function obterEstatisticas() {
    global $db;
    try {
        $stats = new ReservaStats($db);
        $hoje = date('Y-m-d');
        $ini_sem = date('Y-m-d', strtotime('monday this week'));
        $fim_sem = date('Y-m-d', strtotime('sunday this week'));

        $hoje_res = $db->query("SELECT COUNT(*) FROM reservas WHERE data_reserva = '$hoje'")->fetchColumn();
        $sem_res = $db->query("SELECT COUNT(*) FROM reservas WHERE data_reserva BETWEEN '$ini_sem' AND '$fim_sem'")->fetchColumn();

        echo json_encode([
            'sucesso' => true,
            'estatisticas' => [
                'total_reservas' => (int)$db->query("SELECT COUNT(*) FROM reservas")->fetchColumn(),
                'reservas_hoje' => (int)$hoje_res,
                'reservas_semana' => (int)$sem_res,
                'ocupacao_semanal' => $stats->obterOcupacaoSemanal(),
                'mesas_estatisticas' => $stats->obterEstatisticasMesas(30)
            ]
        ]);
    } catch(Exception $e) {
        http_response_code(500);
        echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
    }
}

/**
 * Atribuir mesa manualmente
 */
function atribuirMesa() {
    global $db;
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $reserva_id = $input['reserva_id'] ?? null;
        $mesa_id = $input['mesa_id'] ?? null;

        if (!$reserva_id || !$mesa_id) {
            http_response_code(400);
            echo json_encode(['sucesso' => false, 'erro' => 'Reserva ID e Mesa ID obrigatórios']);
            return;
        }

        $reserva = new Reserva($db);
        $sucesso = $reserva->atribuirMesa($reserva_id, $mesa_id);

        if ($sucesso) {
            echo json_encode(['sucesso' => true, 'mensagem' => 'Mesa atribuída com sucesso']);
        } else {
            http_response_code(400);
            echo json_encode(['sucesso' => false, 'erro' => 'Mesa não disponível']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
    }
}

?>
