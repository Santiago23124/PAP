@echo off
:: ============================================================
:: Script de Teste Interativo para PAP "100 Sabores"
:: Windows Batch Script (.bat)
:: ============================================================
chcp 65001 > nul
title Testes e Utilidades - 100 Sabores
color 0B

:menu
cls
echo ============================================================
echo        🍽️  TESTES E UTILIDADES - PAP "100 SABORES"  🍽️
echo ============================================================
echo.
echo Selecione uma das opções abaixo para abrir no navegador:
echo.
echo  [1] Abrir a Página Principal / Dashboard
echo  [2] Verificar Configurações do Sistema (database/tabelas)
echo  [3] Testar Envio de E-mail SMTP (.env + PHPMailer)
echo  [4] Testar Segurança do .env (Verificar bloqueio .htaccess)
echo  [5] Sair
echo.
echo ============================================================
set /p opcao="Escolha uma opção (1-5): "

if "%opcao%"=="1" goto principal
if "%opcao%"=="2" goto checksetup
if "%opcao%"=="3" goto testmail
if "%opcao%"=="4" goto security
if "%opcao%"=="5" goto sair

echo.
echo [Erro] Opção inválida! Pressione qualquer tecla para voltar ao menu...
pause > nul
goto menu

:principal
echo.
echo A abrir a Página Principal...
start http://localhost/PAP-100_Sabores/
goto fim

:checksetup
echo.
echo A abrir a Verificação de Configuração do Sistema...
start http://localhost/PAP-100_Sabores/check-setup.php
goto fim

:testmail
echo.
echo A abrir o Teste de Envio de E-mail SMTP...
echo Isto irá enviar um e-mail de teste para a conta configurada no seu .env.
start http://localhost/PAP-100_Sabores/api/teste_env_email.php
goto fim

:security
echo.
echo A testar a segurança do ficheiro .env...
echo.
echo NOTA DE SEGURANÇA:
echo Se o Apache/XAMPP estiver a processar corretamente as regras do seu .htaccess,
echo o seu navegador deverá exibir uma página de erro "403 Forbidden".
echo Se vir os dados do ficheiro .env expostos, reveja o seu Apache.
echo.
echo Pressione qualquer tecla para abrir o teste de segurança...
pause > nul
start http://localhost/PAP-100_Sabores/.env
goto fim

:fim
echo.
echo Operação concluída com sucesso!
echo Pressione qualquer tecla para regressar ao menu...
pause > nul
goto menu

:sair
echo.
echo Obrigado por usar o assistente de testes da 100 Sabores! Boa sorte com a sua PAP!
timeout /t 3 > nul
exit
