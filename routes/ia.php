<?php

if (!headers_sent()) {
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json; charset=UTF-8');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    return;
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    @session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/gemini.php';

$raw = trim((string)file_get_contents('php://input'));
$raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw); // Remove UTF-8 BOM
$data = [];

if ($raw !== '') {
    $decodedRaw = json_decode($raw, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decodedRaw)) {
        $data = $decodedRaw;
    } else {
        parse_str($raw, $data);
    }
}

if (!is_array($data) || empty($data)) {
    $data = $_POST;
}

$message = trim((string)($data['message'] ?? $data['mensagem'] ?? $_POST['message'] ?? $_POST['mensagem'] ?? $_GET['message'] ?? ''));

// Requisito 12: Impedir envio de mensagens vazias
if ($message === '') {
    http_response_code(400);
    echo json_encode([
        'status' => 'erro',
        'error' => 'Mensagem não informada.',
        'resposta' => 'Por favor, digite uma mensagem para continuar.'
    ], JSON_UNESCAPED_UNICODE);
    return;
}

// Identificação dos 3 Contextos (Requisitos 6, 7, 8, 10 e 11)
// 'geral' (Pet.IA) | 'doador' (Chat com Doador) | 'adotante' (Chat com Adotante)
$contexto = strtolower(trim((string)($data['contexto'] ?? $data['tipo_conversa'] ?? $_GET['contexto'] ?? 'geral')));
$petId = (int)($data['petId'] ?? $data['id_pet'] ?? $_GET['petId'] ?? $_GET['id_pet'] ?? 0);
$usuarioId = (int)($data['usuarioId'] ?? $data['id_usuario'] ?? ($_SESSION['usuario_id'] ?? 0));

// Requisito 32 & 35: Exigência de autenticação obrigatória no backend para o Chat-Doador
if ($contexto === 'doador') {
    $idAutenticado = (int)($_SESSION['usuario_id'] ?? 0);
    if ($idAutenticado <= 0) {
        http_response_code(401);
        echo json_encode([
            'status' => 'erro',
            'error' => 'Para conversar com o doador, você precisa entrar na sua conta ou criar uma conta no PetAlert.',
            'mensagem' => 'Para conversar com o doador, você precisa entrar na sua conta ou criar uma conta no PetAlert.',
            'autenticacao_obrigatoria' => true
        ], JSON_UNESCAPED_UNICODE);
        return;
    }
}

$db = Database::getConnection();
$pet = null;

if ($petId > 0) {
    try {
        $stmtPet = $db->prepare("
            SELECT a.id_pet, a.nome AS nome_pet, a.especie, COALESCE(a.raca, a.raça, 'SRD') AS raca,
                   a.idade, a.peso, a.porte, a.cor, a.descricao, a.motivo_doacao, a.foto,
                   COALESCE(u.id_usuario, d.id_usuario) AS id_doador,
                   COALESCE(u.nome, 'Tutor(a) do pet') AS nome_doador,
                   COALESCE(u.cidade, '') AS cidade_doador,
                   COALESCE(u.estado, '') AS estado_doador
            FROM tb_animal a
            LEFT JOIN tb_usuario u ON u.id_usuario = a.id_usuario
            LEFT JOIN tb_doapet d ON d.id_pet = a.id_pet
            WHERE a.id_pet = :id_pet
            LIMIT 1
        ");
        $stmtPet->execute([':id_pet' => $petId]);
        $pet = $stmtPet->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (Exception $e) {
        $pet = null;
    }
}

// Definição das variáveis reais do pet (sem inventar dados)
$nomePet = $pet['nome_pet'] ?? 'Pet';
$especie = $pet['especie'] ?? 'Animal';
$raca = $pet['raca'] ?? 'SRD';
$idade = !empty($pet['idade']) ? $pet['idade'] . ' ano(s)' : 'não informada';
$peso = !empty($pet['peso']) && (float)$pet['peso'] > 0 ? $pet['peso'] . ' kg' : 'não informado';
$porte = $pet['porte'] ?? 'não informado';
$cor = $pet['cor'] ?? 'não informada';
$descricao = $pet['descricao'] ?? 'Sem informações adicionais cadastradas.';
$motivoDoacao = $pet['motivo_doacao'] ?? 'Doação responsável.';
$nomeDoador = $pet['nome_doador'] ?? 'Tutor(a) do pet';

// -------------------------------------------------------------------------
// Construção do System Instruction para cada Contexto
// -------------------------------------------------------------------------
if ($contexto === 'doador') {
    // MODO DOADOR: IA simula o tutor do pet em primeira pessoa
    $systemInstruction = "Você está simulando uma conversa em primeira pessoa como {$nomeDoador}, tutor(a) responsável pelo pet {$nomePet} cadastrado no PetAlert.
Você está conversando diretamente com uma pessoa interessada em adotar o(a) {$nomePet}.

Dados cadastrais REAIS do pet no banco de dados:
- Nome do Pet: {$nomePet}
- Espécie: {$especie}
- Raça: {$raca}
- Idade: {$idade}
- Peso: {$peso}
- Porte: {$porte}
- Cor: {$cor}
- Breve história / descrição cadastrada: {$descricao}
- Motivo da doação informado: {$motivoDoacao}
- Seu nome (doador/tutor): {$nomeDoador}

DIRETRIZES ABSOLUTAS DO MODO DOADOR:
1. Fale SEMPRE em primeira pessoa (\"eu\", \"meu pet\", \"coloquei o {$nomePet} para adoção porque...\") de maneira natural, humana e acolhedora.
2. NUNCA se apresente como \"Pet.IA\" e NUNCA diga \"Olá! Sou a Pet.IA\".
3. NUNCA exiba mensagens genéricas de assistente do PetAlert. Você está personificando o doador real do animal.
4. Responda SOMENTE utilizando as informações cadastradas acima sobre o pet e sobre você.
5. NÃO invente fatos pessoais, endereço residencial, número de telefone, acontecimentos ou membros da família.
6. Se a pessoa perguntar algo que NÃO consta no cadastro acima (ex.: vacinas específicas não citadas, castração não citada, hábitos particulares não descritos), responda exatamente:
\"Essa informação não está cadastrada para mim no PetAlert.\"
7. Seja afetuoso(a) e demonstre carinho pelo futuro do(a) {$nomePet}.";

} elseif ($contexto === 'adotante') {
    // MODO ADOTANTE: IA simula uma pessoa interessada em adotar
    $systemInstruction = "Você está simulando uma conversa em primeira pessoa como uma pessoa interessada em adotar o pet {$nomePet} cadastrado no PetAlert.
O tutor do pet se chama {$nomeDoador}.

Dados cadastrais do animal:
- Nome: {$nomePet}
- Espécie: {$especie}
- Raça: {$raca}
- Idade: {$idade}
- Peso: {$peso}
- Porte: {$porte}
- Cor: {$cor}
- Motivo da doação informado pelo tutor: {$motivoDoacao}

DIRETRIZES ABSOLUTAS DO MODO ADOTANTE:
1. Converse em primeira pessoa (\"eu\", \"gostei muito do {$nomePet}\", \"tenho interesse em adotar o {$nomePet}\") com tom carinhoso, empático e educado.
2. NUNCA diga \"Olá! Sou a Pet.IA\" e NUNCA aja como assistente ou robô de sistema.
3. NUNCA invente dados pessoais do adotante. Se algo específico não estiver disponível, responda:
\"Essa informação ainda não está cadastrada.\"
4. Faça perguntas pertinentes sobre a adaptação, temperamento e cuidados do {$nomePet} com base nos dados reais.";

} else {
    // MODO PET.IA (GERAL): Assistente Geral oficial do PetAlert
    $systemInstruction = "Você é a Pet.IA, a assistente virtual oficial e acolhedora da plataforma PetAlert (proteção, adoção e bem-estar animal).
Diretrizes fundamentais que você DEVE seguir em todas as respostas:
1. Responda SEMPRE em português do Brasil (pt-BR) com tom acolhedor, empático, claro e use emojis amigáveis 🐾🐶🐱.
2. A Pet.IA NÃO deve fingir ser um doador.
3. A Pet.IA NÃO deve fingir ser um adotante.
4. Orientações sobre cuidados gerais: alimentação equilibrada por faixa etária, água fresca sempre disponível, higiene, passeios e carinho.
5. Adoção Responsável: encoraje a reflexão sobre tempo, espaço e custos. Oriente o usuário a acessar a aba 'Pets' para ver os animais esperando por um lar amoroso.
6. Cadastrar animal para doação: informe que na aba 'Doar pet' é possível cadastrar espécie, raça, idade, porte, foto e motivo da doação.
7. Doações: informe que na aba 'Doações' é possível fazer doações financeiras via PIX ou doar insumos e materiais (como ração, cobertores e medicamentos).
8. Denúncias de maus-tratos: oriente que na aba 'Denúncias' o usuário deve relatar os fatos, local e anexar fotos ou vídeos comprobatórios.
9. ATENÇÃO MÉDICA E EMERGÊNCIAS: Se a pergunta indicar qualquer emergência, acidente, sangramento, vômito persistente, engasgo, convulsão, envenenamento ou dor intensa, oriente a pessoa a PROCURAR IMEDIATAMENTE UM MÉDICO VETERINÁRIO OU HOSPITAL VETERINÁRIO 24H! Enfatize claramente que você é uma IA e que suas dicas NUNCA substituem a avaliação clínica de um profissional veterinário.";
}

// Montar histórico de conversação multi-turn
$contents = [];
$incomingHistory = $data['history'] ?? [];

if (is_array($incomingHistory) && !empty($incomingHistory)) {
    foreach ($incomingHistory as $item) {
        $role = ($item['role'] ?? '') === 'assistant' || ($item['role'] ?? '') === 'model' ? 'model' : 'user';
        $text = trim((string)($item['text'] ?? $item['content'] ?? ''));
        if ($text !== '') {
            $contents[] = [
                'role' => $role,
                'parts' => [['text' => $text]]
            ];
        }
    }
}

// Garante que a última mensagem do usuário seja enviada
if (empty($contents) || end($contents)['role'] !== 'user' || end($contents)['parts'][0]['text'] !== $message) {
    $contents[] = [
        'role' => 'user',
        'parts' => [['text' => $message]]
    ];
}

$payload = [
    'contents' => $contents,
    'system_instruction' => [
        'parts' => [['text' => $systemInstruction]]
    ],
    'generationConfig' => [
        'temperature' => 0.7,
        'maxOutputTokens' => 8192
    ]
];

// Requisito 4 e 14: Chave obtida exclusivamente no backend (.env)
$apiKey = get_gemini_api_key();

$reply = null;
$successfulModel = null;
$authErrorDetected = false;

if (!empty($apiKey)) {
    // Modelos oficiais suportados pela API Gemini
    $models = ['gemini-3.5-flash', 'gemini-3.5-flash-lite', 'gemini-3.8-flash', 'gemini-3.6-flash'];

    foreach ($models as $model) {
        // Autenticação oficial: parâmetro ?key= com a chave no backend
        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . rawurlencode($apiKey);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $endpoint,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 45,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response && $httpCode === 200) {
            $decoded = json_decode($response, true);
            $candidateParts = $decoded['candidates'][0]['content']['parts'] ?? [];
            $aggregatedText = '';
            foreach ($candidateParts as $part) {
                // Pula partes de raciocínio interno (thought) se houver
                if (!empty($part['thought'])) {
                    continue;
                }
                if (isset($part['text'])) {
                    $aggregatedText .= $part['text'];
                }
            }
            if ($aggregatedText !== '') {
                $reply = trim($aggregatedText);
                $successfulModel = $model;
                break;
            }
        } elseif ($httpCode === 401) {
            $authErrorDetected = true;
            break;
        }
    }
}

// Resposta com sucesso da API Gemini
if ($reply) {
    try {
        $stmtLog = $db->prepare("INSERT INTO tb_ia (pergunta, resposta, categoria) VALUES (:p, :r, :c)");
        $stmtLog->execute([
            ':p' => $message,
            ':r' => $reply,
            ':c' => "{$contexto}:{$successfulModel}"
        ]);
    } catch (Exception $e) {}

    echo json_encode([
        'status' => 'sucesso',
        'contexto' => $contexto,
        'reply' => $reply,
        'resposta' => $reply,
        'response' => $reply,
        'model' => $successfulModel
    ], JSON_UNESCAPED_UNICODE);
    return;
}

// -------------------------------------------------------------------------
// Tratamento de Resposta quando Gemini estiver indisponível ou sem chave
// (Respeita estritamente o personagem de cada um dos 3 contextos!)
// -------------------------------------------------------------------------
$msgLower = mb_strtolower($message, 'UTF-8');

if ($contexto === 'doador') {
    // FALLBACK MODO DOADOR: Fala em 1ª pessoa com base nos dados do banco
    if (str_contains($msgLower, 'motivo') || str_contains($msgLower, 'por que') || str_contains($msgLower, 'porque') || str_contains($msgLower, 'doando') || str_contains($msgLower, 'doação')) {
        $fallbackReply = "O motivo que cadastrei para a doação do(a) {$nomePet} foi: {$motivoDoacao}.";
    } elseif (str_contains($msgLower, 'idade') || str_contains($msgLower, 'quantos anos') || str_contains($msgLower, 'ano')) {
        $fallbackReply = "O(A) {$nomePet} tem {$idade}.";
    } elseif (str_contains($msgLower, 'peso') || str_contains($msgLower, 'pesa') || str_contains($msgLower, 'quilos') || str_contains($msgLower, 'kg')) {
        $fallbackReply = "O peso cadastrado do(a) {$nomePet} é de {$peso}.";
    } elseif (str_contains($msgLower, 'raça') || str_contains($msgLower, 'raca') || str_contains($msgLower, 'espécie') || str_contains($msgLower, 'especie')) {
        $fallbackReply = "O(A) {$nomePet} é da espécie {$especie}, raça {$raca}.";
    } elseif (str_contains($msgLower, 'cor')) {
        $fallbackReply = "A cor dele(a) é {$cor}.";
    } elseif (str_contains($msgLower, 'porte')) {
        $fallbackReply = "O porte do(a) {$nomePet} é {$porte}.";
    } elseif (str_contains($msgLower, 'história') || str_contains($msgLower, 'historia') || str_contains($msgLower, 'sobre') || str_contains($msgLower, 'detalhe') || str_contains($msgLower, 'descrição')) {
        $fallbackReply = "Sobre o(a) {$nomePet}: {$descricao}";
    } elseif (str_contains($msgLower, 'endereço') || str_contains($msgLower, 'telefone') || str_contains($msgLower, 'casa') || str_contains($msgLower, 'onde mora') || str_contains($msgLower, 'família')) {
        $fallbackReply = "Essa informação não está cadastrada para mim no PetAlert.";
    } else {
        $fallbackReply = "Oi! Eu sou o(a) {$nomeDoador}, responsável pelo(a) {$nomePet}. Ele(a) é um(a) {$raca} de {$idade}, porte {$porte}. O motivo da doação foi: {$motivoDoacao}. Gostaria de saber mais algum detalhe sobre ele(a)?";
    }

} elseif ($contexto === 'adotante') {
    // FALLBACK MODO ADOTANTE: Fala em 1ª pessoa como alguém querendo adotar
    if (str_contains($msgLower, 'endereço') || str_contains($msgLower, 'telefone') || str_contains($msgLower, 'cpf') || str_contains($msgLower, 'onde mora')) {
        $fallbackReply = "Essa informação ainda não está cadastrada.";
    } else {
        $fallbackReply = "Oi! Eu gostei bastante do(a) {$nomePet} e gostaria muito de saber mais sobre a rotina dele(a). Ele(a) parece ser um companheiro incrível para a minha família!";
    }

} else {
    // FALLBACK PET.IA GERAL: Orientações gerais da plataforma
    if (str_contains($msgLower, 'emergência') || str_contains($msgLower, 'sangue') || str_contains($msgLower, 'veneno') || str_contains($msgLower, 'intoxicação') || str_contains($msgLower, 'engasgou')) {
        $fallbackReply = "🚨 **ATENÇÃO: POSSÍVEL EMERGÊNCIA VETERINÁRIA!**\n\nPor favor, leve seu animalzinho **imediatamente** a uma clínica ou hospital veterinário com atendimento 24 horas!\n\nVocê pode consultar opções próximas na aba **Veterinário** do PetAlert. Não tente medicamentos humanos sem orientação profissional!";
    } elseif (str_contains($msgLower, 'adotar') || str_contains($msgLower, 'adoção')) {
        $fallbackReply = "🐾 **Adoção Responsável no PetAlert:**\n\n1. Acesse a aba **Pets** para ver os animais cadastrados.\n2. Escolha o pet desejado e confira seus detalhes.\n3. Lembre-se: adotar é um compromisso para toda a vida! Avalie tempo, espaço e custos.";
    } elseif (str_contains($msgLower, 'denúncia') || str_contains($msgLower, 'maus-tratos')) {
        $fallbackReply = "🚨 **Como Denunciar Maus-tratos:**\n\n1. Acesse a aba **Denúncias** no menu superior.\n2. Relate com detalhes a situação e o local exato.\n3. É fundamental anexar foto ou vídeo da ocorrência para apuração.";
    } elseif (str_contains($msgLower, 'doar pet') || str_contains($msgLower, 'cadastrar pet')) {
        $fallbackReply = "🐶 **Como Cadastrar um Pet para Doação:**\n\n1. Acesse a aba **Doar pet** no menu.\n2. Preencha os dados do animal (nome, espécie, idade, porte e cor).\n3. Inclua uma foto nítida e conte o motivo da doação para encontrar um tutor amoroso.";
    } else {
        $fallbackReply = "Olá! Sou a **Pet.IA**, assistente geral do PetAlert! 🐾\n\nEstou aqui para orientar sobre:\n• **Adoção Responsável** (aba Pets);\n• **Cadastro de pets para doação** (aba Doar pet);\n• **Doações financeiras e insumos** (aba Doações);\n• **Denúncias de maus-tratos** (aba Denúncias);\n• **Clínicas veterinárias** (aba Veterinário).\n\nComo posso orientar você agora?";
    }
}

$aviso = null;
if (empty($apiKey)) {
    $aviso = 'A chave GEMINI_API_KEY precisa ser configurada no arquivo .env do backend para respostas dinâmicas.';
} elseif ($authErrorDetected) {
    $aviso = 'Chave GEMINI_API_KEY retornou HTTP 401 (Credenciais inválidas). Verifique a chave no .env.';
}

echo json_encode([
    'status' => 'sucesso',
    'contexto' => $contexto,
    'reply' => $fallbackReply,
    'resposta' => $fallbackReply,
    'response' => $fallbackReply,
    'model' => 'petalert-smart-fallback',
    'aviso' => $aviso
], JSON_UNESCAPED_UNICODE);
return;