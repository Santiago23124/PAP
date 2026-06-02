PAP-100_Sabores

1. Sistema de "Autossuficiência" de Variáveis de Ambiente
A função carregarEnv que implementaste é uma solução muito elegante.

Por que é boa: Em vez de dependeres exclusivamente de bibliotecas externas (como o vlucas/phpdotenv), criaste uma função em "PHP puro" que lê o teu ficheiro .env. Isto torna o teu sistema mais resiliente: se a biblioteca externa falhar ou não estiver instalada, o teu sistema tem um plano B.

Segurança: Ela garante que credenciais (como DB_PASS) nunca são escritas diretamente no código, protegendo-as de olhares indiscretos se partilhares o código.

2. Padrão de Projeto: Database (Singleton/Factory)
Ao usar uma classe Database com o método getConnection():

Centralização: Se precisares de mudar de base de dados (de 100sabores para outra), só precisas de alterar um ficheiro. Todo o resto do projeto (as classes Reserva, Mesa, etc.) apenas chama getDB().

Configuração de PDO: A utilização de PDO::ERRMODE_EXCEPTION é a forma correta de lidar com erros. Quando ocorre um erro na BD, o PHP "lança" uma exceção que o teu código try-catch apanha, evitando que o sistema apresente erros fatais "feios" aos utilizadores.

3. A Classe Config (Constantes de Negócio)
Esta classe é onde defines as regras do teu restaurante.

Manutenibilidade: Se o dono do restaurante decidir que a "Capacidade Máxima" passa de 80 para 100 pessoas, não tens de andar à procura deste valor em centenas de linhas de código. Mudas apenas uma constante aqui.

Separação de Preocupações: O código que lida com o envio de e-mails apenas consulta Config::EMAIL_RESTAURANTE. Ele não precisa de saber qual é o e-mail, ele apenas pede a constante.

4. Ligação com as Telecomunicações (Protocolo SMTP)
Repara que a nota no teu código sobre o SMTP é crucial.

Segurança em Trânsito: Quando utilizas o SMTP para enviar confirmações, o PHP está a atuar como um cliente de e-mail. Ao carregares estas credenciais via .env, estás a garantir que a comunicação (o "aperto de mão" entre o teu servidor e o servidor de e-mail) é feita com autenticação segura, sem que o teu código PHP exponha a password do e-mail.

O que isto diz ao teu examinador da PAP?
Este ficheiro é um excelente ponto de defesa na tua apresentação. Podes destacar:

"Implementei uma camada de abstração de dados": A classe Database garante que a lógica de negócio não sabe os detalhes técnicos de como a BD se liga.

"Segurança por Design": O uso de ficheiros .env e a proteção de dados sensíveis fora do código-fonte demonstra preocupações reais com a segurança da informação.

"Escalabilidade": A classe Config permite ajustar parâmetros do negócio sem risco de quebrar o sistema.

Dica de profissional: O teu carregarEnv é excelente, mas garante que o teu ficheiro .env está realmente fora do Git (usando o ficheiro .gitignore que criámos anteriormente). Se o .env estiver no GitHub, todo este esforço de segurança perde o sentido!

