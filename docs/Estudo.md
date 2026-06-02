1.HTTP HEADERS

O "Header" HTTP é uma linha de texto que carrega informações sobre a requisição (do navegador para o servidor) ou sobre a resposta (do servidor para o navegador).

Pensa neles como o envelope de uma carta:

O envelope diz de onde vem, para onde vai, que tipo de conteúdo tem e se precisa de tratamento especial (ex: "Fragile").
O Header HTTP diz ao servidor/navegador coisas como: qual é o tipo de dados, se está autorizado, qual o tamanho do conteúdo, etc.

Exemplos comuns:
-Content-Type: Indica o formato do conteúdo (ex: application/json ou text/html).

-Authorization: Carrega tokens (como JWT) para validar que o usuário tem permissão para acessar aquele recurso.

-User-Agent: Identifica qual navegador e sistema operacional está fazendo a requisição.

-Cache-Control: Define por quanto tempo o navegador deve salvar o arquivo em cache.


2. O QUE SÃO CORS?

CORS- Cross-Origin Resource Sharing

Política de Segurança que Controla Recursos
CORS é um conjunto de regras de segurança definidas no protocolo HTTP que controlam como os navegadores web devem permissões (share) recursos (como fontes, imagens, ou respostas de API) de um domínio diferente daquele que originou a requisição.

Sem CORS (Restrição por Defeito)
Historicamente, os navegadores implementam uma política de "Same-Origin" (Mesma Origem). Para que uma requisição seja permitida:

O protocolo (http/https) deve ser igual.

O domínio (hostname) deve ser igual.

A porta deve ser igual.

Exemplo: Se o teu backend PHP está em localhost:8000, um frontend JS rodando em localhost:3000 (ou arquivo local file://) NÃO consegue aceder à API sem permissão explícita. Isso impede que sites maliciosos leiam dados do teu servidor.

O cors faz uma requesição de verificação (Preflight Request), o navegador envia um pedido(OPTIONS) antes de enviar a requisição real (com método POST, PUT, etc.), em muitos casos

Por exemplo:

header('Access-Control-Allow-Origin: *');

Está a dizer ao navegador: "Permita que qualquer origem (qualquer site/domínio) faça requisições a esta API".

É uma solução de desenvolvimento (dev) ou para APIs públicas.

Para produção, é mais seguro restringir:

Se o frontend estiver em http://localhost:3000:

header('Access-Control-Allow-Origin: http://localhost:3000');
