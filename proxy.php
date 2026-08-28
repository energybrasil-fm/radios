<?php
// Libera o CORS para qualquer domínio (o seu player JavaScript conseguirá ler)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Lida com requisições OPTIONS do navegador (Preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Verifica se a URL foi passada
if (!isset($_GET['url'])) {
    http_response_code(400);
    echo "Erro: O parâmetro 'url' é obrigatório.";
    exit;
}

$url = $_GET['url'];

// Validação básica de URL para segurança
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    echo "Erro: URL fornecida é inválida.";
    exit;
}

// Inicia o cURL
$ch = curl_init($url);

// Configurações do cURL
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Retorna como string
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Segue redirecionamentos
curl_setopt($ch, CURLOPT_TIMEOUT, 8); // Timeout de 8 segundos para não travar seu servidor
// Um User-Agent comum para evitar bloqueios em servidores Shoutcast/Icecast rigorosos
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
// Ignora verificação de SSL (útil para rádios IP:PORTA sem certificado HTTPS válido)
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

// Executa a requisição
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
$curlError = curl_error($ch);

curl_close($ch);

// Verifica se houve erro
if ($response === false || $httpCode >= 400) {
    http_response_code(500);
    echo "Erro no Proxy PHP. HTTP Code: " . $httpCode . " | Curl Error: " . $curlError;
    exit;
}

// Se o servidor original enviou um Content-Type (ex: text/plain, application/json), repassa para o JS
if ($contentType) {
    header("Content-Type: " . $contentType);
} else {
    // Fallback de segurança para 100hitz
    header("Content-Type: text/plain; charset=UTF-8"); 
}

// Imprime os dados brutos obtidos da rádio
echo $response;
?>
