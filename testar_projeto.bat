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
echo Selecione uma das opcoes abaixo para abrir no navegador:
echo.
echo  [1] Abrir a Pagina Principal / Dashboard
echo  [2] Verificar Configuracoes do Sistema (database/tabelas)
echo  [3] Testar Envio de E-mail SMTP (.env + PHPMailer)
echo  [4] Testar Pagina de Reservas (Interface do Utilizador)
echo  [5] Executar Teste Automatizado de Reserva (Back-end)
echo  [6] Testar Seguranca do .env (Verificar bloqueio .htaccess)
echo  [7] Sair
echo.
echo ============================================================
set /p opcao="Escolha uma opcao (1-7): "

if "%opcao%"=="1" goto principal
if "%opcao%"=="2" goto checksetup
if "%opcao%"=="3" goto testmail
if "%opcao%"=="4" goto reservas_ui
if "%opcao%"=="5" goto reservas_backend
if "%opcao%"=="6" goto security
if "%opcao%"=="7" goto sair

echo.
echo [Erro] Opcao invalida! Pressione qualquer tecla para voltar ao menu...
pause > nul
goto menu

:principal
echo.
echo A abrir a Pagina Principal...
start http://localhost/PAP-100_Sabores/
goto fim

:checksetup
echo.
echo A abrir a Verificacao de Configuracao do Sistema...
start http://localhost/PAP-100_Sabores/check-setup.php
goto fim

:testmail
echo.
echo A abrir o Teste de Envio de E-mail SMTP...
echo Isto ira enviar um e-mail de teste para a conta configurada no seu .env.
start http://localhost/PAP-100_Sabores/api/teste_env_email.php
goto fim

:reservas_ui
echo.
echo A abrir a pagina de Reservas no navegador...
start http://localhost/PAP-100_Sabores/pages/Reservas.html
goto fim

:reservas_backend
echo.
echo A abrir o Teste Automatizado de Reserva (Back-end)...
echo Isto ira executar o script que valida e simula uma criacao de reserva na base de dados.
start http://localhost/PAP-100_Sabores/api/test_reserva.php
goto fim

:security
echo.
echo A testar a seguranca do ficheiro .env...
echo.
echo NOTA DE SEGURANCA:
echo Se o Apache/XAMPP estiver a processar corretamente as regras do seu .htaccess,
echo o seu navegador devera exibir uma pagina de erro "403 Forbidden".
echo Se vir os dados do ficheiro .env expostos, reveja o seu Apache.
echo.
echo Pressione qualquer tecla para abrir o teste de seguranca...
pause > nul
start http://localhost/PAP-100_Sabores/.env
goto fim

:fim
echo.
echo Operacao concluida com sucesso!
echo Pressione qualquer tecla para regressar ao menu...
pause > nul
goto menu

:sair
echo.
echo Obrigado por usar o assistente de testes da 100 Sabores! Boa sorte com a sua PAP!
timeout /t 3 > nul
exit
