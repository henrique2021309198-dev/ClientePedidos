<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota do Pedido</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; background: #f7f7f7; }
        .container { max-width: 800px; margin: 0 auto; padding: 40px 20px; }
        .ticket { background: #fff; border-radius: 16px; padding: 24px; box-shadow: 0 8px 18px rgba(0,0,0,.05); }
        .header { text-align: center; margin-bottom: 16px; }
        .notice { text-align: center; color: #666; margin-top: 12px; }
        .item { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px dashed #ddd; }
        .btn { display: inline-block; margin-top: 18px; background: #ff6b35; color: #fff; border: 0; padding: 12px 16px; border-radius: 10px; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="ticket">
            <div class="header">
                <h2>Pedido confirmado</h2>
                <p>Número do pedido: <strong><?= esc($codigo) ?></strong></p>
                <?php if (!empty($totem['nome'])): ?>
                    <p>Totem: <strong><?= esc($totem['nome']) ?></strong></p>
                <?php endif; ?>
            </div>

            <?php foreach ($itens as $item): ?>
                <div class="item">
                    <span><?= esc($item['quantidade']) ?>x <?= esc($item['nome']) ?></span>
                    <span>R$ <?= number_format((float)($item['preco'] * $item['quantidade']), 2, ',', '.') ?></span>
                </div>
            <?php endforeach; ?>

            <div class="item" style="border-bottom: 0; font-weight: 700;">
                <span>Total</span>
                <span>R$ <?= number_format((float)$total, 2, ',', '.') ?></span>
            </div>

            <a class="btn" href="<?= site_url('inicio?pedidoFinalizado=1') ?>">Voltar para o inicio</a>
            <p class="notice" id="redirectNotice">Voce sera redirecionado para o inicio em 8 segundos.</p>
            <p class="notice">O totem deste dispositivo sera mantido para o proximo atendimento.</p>
        </div>
    </div>

    <script>
        localStorage.removeItem('pedidoCart');

        let seconds = 8;
        const redirectUrl = '<?= site_url('inicio?pedidoFinalizado=1') ?>';
        const notice = document.getElementById('redirectNotice');

        const timer = setInterval(() => {
            seconds -= 1;
            if (seconds <= 0) {
                clearInterval(timer);
                window.location.href = redirectUrl;
                return;
            }

            notice.textContent = `Voce sera redirecionado para o inicio em ${seconds} segundos.`;
        }, 1000);
    </script>
</body>
</html>