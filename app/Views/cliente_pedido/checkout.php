<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; background: #f7f7f7; }
        .container { max-width: 900px; margin: 0 auto; padding: 24px 20px; }
        .grid { display: grid; grid-template-columns: 1.4fr 1fr; gap: 18px; }
        .box { background: #fff; border-radius: 16px; padding: 18px; box-shadow: 0 8px 18px rgba(0,0,0,.05); }
        input, textarea { width: 100%; padding: 12px; margin-bottom: 10px; border-radius: 8px; border: 1px solid #ddd; }
        .btn { background: #ff6b35; color: #fff; border: 0; padding: 12px 16px; border-radius: 10px; width: 100%; cursor: pointer; }
        .btn-secondary { background: #f0f0f0; color: #222; }
        .item { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
        @media (max-width: 768px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="grid">
            <div class="box">
                <h2>Resumo do pedido</h2>
                <div id="summaryItems"></div>
                <div style="text-align:right; margin-top: 12px;"><strong id="summaryTotal"></strong></div>
            </div>
            <div class="box">
                <h2>Identificacao do cliente</h2>
                <form id="checkoutForm">
                    <input type="text" id="nome" placeholder="Seu nome" required>
                    <button class="btn" type="submit">Finalizar pedido</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const totem = JSON.parse(localStorage.getItem('pedidoTotem') || 'null');
        if (!totem || !totem.id) {
            window.location.href = '<?= site_url('/?totem=obrigatorio') ?>';
        }

        const cart = JSON.parse(localStorage.getItem('pedidoCart') || '[]');

        function formatCurrency(value) {
            return 'R$ ' + Number(value).toFixed(2).replace('.', ',');
        }

        function renderSummary() {
            const items = document.getElementById('summaryItems');
            const totalEl = document.getElementById('summaryTotal');
            let total = 0;
            items.innerHTML = '';

            cart.forEach(item => {
                const subtotal = item.preco * item.quantidade;
                total += subtotal;
                const row = document.createElement('div');
                row.className = 'item';
                row.innerHTML = `<span>${item.quantidade}x ${item.nome}</span><span>${formatCurrency(subtotal)}</span>`;
                items.appendChild(row);
            });

            totalEl.textContent = 'Total: ' + formatCurrency(total);
        }

        document.getElementById('checkoutForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            if (!cart.length) {
                alert('Seu carrinho está vazio.');
                return;
            }

            const payload = {
                itens: cart,
                totem_id: Number((JSON.parse(localStorage.getItem('pedidoTotem') || 'null') || {}).id || 0),
                totem_nome: (JSON.parse(localStorage.getItem('pedidoTotem') || 'null') || {}).nome || ''
            };

            try {
                const response = await fetch('<?= site_url('api/checkout') ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await response.json();

                if (data.success) {
                    localStorage.removeItem('pedidoCart');
                    window.location.href = data.redirect;
                } else {
                    alert(data.error || 'Erro ao finalizar pedido.');
                }
            } catch (error) {
                alert('Erro de conexão com a API.');
            }
        });

        renderSummary();
    </script>
</body>
</html>