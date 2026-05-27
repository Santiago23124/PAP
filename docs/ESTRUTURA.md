# Guia da Nova Estrutura de Ficheiros e Pastas - PAP "100 Sabores"

Este documento explica de forma simples e detalhada a utilidade de todas as novas pastas e ficheiros que foram adicionados e configurados no seu projeto XAMPP para o correto funcionamento das variáveis de ambiente (`.env`), gestão de dependências (`Composer`), envio de e-mails (`PHPMailer`) e segurança (`.htaccess`).

---

## 📁 As Novas Pastas

### 1. `vendor/`
*   **O que é?** Esta pasta é criada e gerida automaticamente pelo **Composer** (o gestor de pacotes oficial do PHP).
*   **O que contém?**
    *   **Bibliotecas Externas**: Pastas como `phpmailer/` (para envio seguro de e-mails) e `vlucas/phpdotenv/` (para ler o ficheiro `.env`).
    *   **Pollyfills e Utilitários**: Dependências secundárias necessárias para a compatibilidade e funcionamento das bibliotecas.
    *   **O ficheiro `vendor/autoload.php`**: É o ficheiro mais importante desta pasta! Ele serve como um "carregador automático". Quando o importamos, o PHP passa a saber onde encontrar todas as classes e bibliotecas instaladas na pasta `vendor/` automaticamente, sem termos de fazer `require_once` manuais para cada ficheiro individual.
*   **Nota de Segurança/Git**: Esta pasta **nunca** deve ser enviada para repositórios de controlo de versão (como o Git/GitHub). Ela é gerada localmente correndo o comando `composer install`.

---

## 📄 Os Novos Ficheiros na Raiz do Projeto

### 1. `composer.json`
*   **O que é?** É o "bilhete de identidade" do seu projeto em termos de dependências de código.
*   **Para que serve?** Ele lista todas as bibliotecas de que o seu projeto necessita para funcionar (neste caso, `vlucas/phpdotenv` e `phpmailer/phpmailer`), bem como as versões aceitáveis das mesmas.
*   **Importância**: Se levar o seu projeto para outro computador, basta ter o Composer instalado e correr `composer install` para que o PHP descarregue e recrie a pasta `vendor/` exatamente com as mesmas versões.

### 2. `composer.lock`
*   **O que é?** É um ficheiro de registo gerado automaticamente pelo Composer.
*   **Para que serve?** Ele grava as versões exatas de cada biblioteca que foram instaladas no momento em que correu o comando de instalação. Isso garante que o projeto irá rodar exatamente da mesma forma em qualquer outro computador ou servidor de produção, sem diferenças de atualização de código.

### 3. `.env` (Environment Variables)
*   **O que é?** É o ficheiro de configuração onde residem as credenciais sensíveis (senhas da base de dados, tokens SMTP do Gmail, etc.).
*   **Para que serve?** Isola as credenciais confidenciais do código fonte. Ao invés de escrever a senha do banco de dados diretamente dentro do ficheiro PHP (como estava anteriormente), o código lê esta informação dinamicamente a partir do `.env` usando a variável global `$_ENV`.
*   **Nota de Segurança Importante**: **Nunca partilhe ou envie o ficheiro `.env` para o Git/GitHub**. Cada ambiente (computador de desenvolvimento, servidor de teste, servidor de produção) terá o seu próprio `.env` específico e privado.

### 4. `.env.example`
*   **O que é?** Uma cópia ou modelo do ficheiro `.env`, mas **sem as senhas ou credenciais reais**.
*   **Para que serve?** Serve de instrução e documentação para outros programadores (ou para si mesmo no futuro). Quando alguém descarrega o projeto do Git, sabe quais são as variáveis que precisa de definir no seu próprio ficheiro `.env` local. Este sim, **pode e deve ser enviado para o Git**.

### 5. `.htaccess`
*   **O que é?** Um ficheiro de configuração para servidores web **Apache** (que é o servidor web integrado no XAMPP).
*   **Para que serve?** 
    *   **Segurança Máxima**: Ele contém diretivas que impedem que qualquer utilizador tente aceder ao seu ficheiro `.env` ou `.env.example` diretamente pelo navegador (ex: escrevendo `http://localhost/PAP-100_Sabores/.env`). O Apache recusará o acesso com um erro de **403 Forbidden**.
    *   **Organização**: Também desativa a listagem pública das pastas por parte do Apache (`Options -Indexes`), impedindo que terceiros naveguem livremente na sua árvore de ficheiros se não existir um ficheiro de entrada como `index.php`.

---

## ⚙️ Ficheiro de Teste Criado

### 1. `api/teste_env_email.php`
*   **O que é?** Um script utilitário criado para validar de forma rápida se o sistema de variáveis de ambiente e o servidor de emails estão perfeitamente conectados.
*   **Como usar?** Basta aceder a `http://localhost/PAP-100_Sabores/api/teste_env_email.php` a partir do seu navegador local. O script executará um envio de e-mail de teste simulando a confirmação de uma reserva e retornará um resultado limpo em formato JSON informando o estado do envio.

---

## 💡 Como tudo se conecta no seu código?

A integração foi feita de forma muito elegante e limpa para não ter de alterar dezenas de ficheiros:
```
[Navegador ou Script] 
        │
        ▼
   [api/...] ou [Qualquer página]
        │  (requer)
        ▼
 [config/database.php] ──► 1. Carrega o 'vendor/autoload.php' (Composer)
        │              ──► 2. Carrega as variáveis do '.env' (phpdotenv)
        ▼
[Database / EmailSender] ──► Consomem as credenciais seguras a partir de $_ENV
```

Ao colocar a inicialização do autoloader e do `phpdotenv` diretamente no topo do `config/database.php`, garantimos que **todo o seu projeto** fica automaticamente dotado da capacidade de ler as variáveis do `.env` e carregar as classes do Composer sem requerer qualquer esforço adicional!
