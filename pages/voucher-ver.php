<?php
/**
 * Página imprimível do voucher preenchido.
 * Recebe os dados por query string e desenha o texto por cima da imagem base.
 * Ex: voucher-ver.php?para=Ana&de=Bruno&valor=50&codigo=VC-AB12CD34
 */
$para   = htmlspecialchars($_GET['para']   ?? '', ENT_QUOTES, 'UTF-8');
$de     = htmlspecialchars($_GET['de']     ?? '', ENT_QUOTES, 'UTF-8');
$valor  = (int)($_GET['valor'] ?? 0);
$codigo = htmlspecialchars($_GET['codigo'] ?? '', ENT_QUOTES, 'UTF-8');

// Formatar a data da reserva (Y-m-d -> d/m/Y)
$data_raw = $_GET['data'] ?? '';
$data_fmt = '';
$dt = DateTime::createFromFormat('Y-m-d', $data_raw);
if ($dt && $dt->format('Y-m-d') === $data_raw) {
    $data_fmt = $dt->format('d/m/Y');
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voucher 100 Sabores<?= $codigo ? ' - ' . $codigo : '' ?></title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #2b2b2b;
            font-family: 'Cormorant Garamond', Georgia, 'Times New Roman', serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 30px 15px;
        }
        .voucher-wrapper {
            position: relative;
            width: 100%;
            max-width: 620px;
            box-shadow: 0 12px 40px rgba(0,0,0,0.4);
        }
        .voucher-wrapper img {
            display: block;
            width: 100%;
            height: auto;
        }
        /* Texto sobreposto — posições em % relativas à imagem (1526x2000) */
        .campo {
            position: absolute;
            color: #f3e4c4;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .campo-para   { left: 44%; top: 34.6%; width: 36%; font-size: 2.0vw; }
        .campo-de     { left: 46%; top: 38.7%; width: 31%; font-size: 1.8vw; }
        .campo-extra  { left: 18%; top: 44.5%; width: 64%; font-size: 1.6vw; color: #e9d4a8; }
        .campo-data   { left: 13%; top: 51.8%; width: 30%; font-size: 1.7vw; }

        /* Limitar o tamanho da fonte em ecrãs grandes (vw deixa de crescer) */
        @media (min-width: 620px) {
            .campo-para  { font-size: 13px; }
            .campo-de    { font-size: 12px; }
            .campo-extra { font-size: 11px; }
            .campo-data  { font-size: 12px; }
        }

        .barra {
            margin-top: 22px;
            display: flex;
            gap: 12px;
        }
        .btn {
            background: #c5a880;
            color: #1a1a1a;
            border: none;
            padding: 12px 26px;
            border-radius: 30px;
            font-size: 15px;
            font-weight: bold;
            letter-spacing: 1px;
            cursor: pointer;
            text-decoration: none;
            font-family: Arial, sans-serif;
        }
        .btn.secundario { background: transparent; color: #c5a880; border: 1px solid #c5a880; }

        @media print {
            body { background: #fff; padding: 0; }
            .barra { display: none; }
            .voucher-wrapper { box-shadow: none; max-width: 100%; }
        }
    </style>
</head>
<body>
    <div class="voucher-wrapper">
        <img src="imagens/logo/100 sabores.png" alt="Voucher 100 Sabores">
        <div class="campo campo-para"><?= $para ?></div>
        <div class="campo campo-de"><?= $de ?></div>
        <?php if ($data_fmt): ?><div class="campo campo-data"><?= $data_fmt ?></div><?php endif; ?>
        <div class="campo campo-extra">
            <?php if ($valor): ?>Valor: <?= $valor ?>&euro;<?php endif; ?>
            <?php if ($valor && $codigo): ?> &nbsp;&bull;&nbsp; <?php endif; ?>
            <?php if ($codigo): ?>Código: <?= $codigo ?><?php endif; ?>
        </div>
    </div>

    <div class="barra">
        <button class="btn" onclick="window.print()">Imprimir / Guardar PDF</button>
        <a class="btn secundario" href="voucher.html">Voltar</a>
    </div>
</body>
</html>
