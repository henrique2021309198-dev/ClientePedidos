<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; background: #f7f7f7; }
        .container { max-width: 980px; margin: 0 auto; padding: 24px 20px 80px; }
        .box { background: #fff; border-radius: 16px; padding: 18px; box-shadow: 0 8px 18px rgba(0,0,0,.05); }
        .item { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid #eee; }
        .qty-controls { display: flex; align-items: center; gap: 8px; }
        .qty-controls button { border: 0; width: 32px; height: 32px; border-radius: 8px; background: #eee; cursor: pointer; }
        .actions { display: flex; justify-content: space-between; align-items: center; margin-top: 16px; }
        .btn { background: #ff6b35; color: #fff; border: 0; padding: 12px 16px; border-radius: 10px; cursor: pointer; }
        .btn-secondary { background: #f0f0f0; color: #222; }
    </style>
</head>
<body>
    <div class="container">
        <div class="box">
            <h2>Seu carrinho</h2>
            <div id="cartItems"></div>
            <div class="actions">
                <button class="btn btn-secondary" onclick="window.location.href='<?= site_url('produtos') ?>'">Voltar</button>
                <button class="btn" onclick="window.location.href='<?= site_url('checkout') ?>'">Continuar</button>
            </div>
        </div>
    </div>

    <script>
        const DEBUG_FLOW = true;
        function logFlow(event, payload = {}) {
            if (!DEBUG_FLOW) return;
            console.log(`[ClientePedidos][Carrinho] ${event}`, payload);
        }

        const totem = JSON.parse(localStorage.getItem('pedidoTotem') || 'null');
        if (!totem || !totem.id) {
            logFlow('Totem ausente, redirecionando', { totem });
            window.location.href = '<?= site_url('/?totem=obrigatorio') ?>';
        }

        const cart = JSON.parse(localStorage.getItem('pedidoCart') || '[]');

        function formatCurrency(value) {
            return 'R$ ' + Number(value).toFixed(2).replace('.', ',');
        }

        function saveCart() {
            localStorage.setItem('pedidoCart', JSON.stringify(cart));
            logFlow('Carrinho salvo no localStorage', { carrinho: cart });
        }

        function updateQuantity(id, delta) {
            const item = cart.find(i => i.id === id);
            if (!item) return;
            item.quantidade += delta;
            logFlow('Quantidade alterada', { id, delta, quantidadeAtual: item.quantidade });
            if (item.quantidade <= 0) {
                const index = cart.findIndex(i => i.id === id);
                logFlow('Item removido do carrinho', { itemRemovido: cart[index] });
                cart.splice(index, 1);
            }
            saveCart();
            renderCart();
        }

        function renderCart() {
            const container = document.getElementById('cartItems');
            if (!cart.length) {
                container.innerHTML = '<p>Seu carrinho está vazio.</p>';
                return;
            }

            let total = 0;
            container.innerHTML = '';
            cart.forEach(item => {
                const subtotal = item.preco * item.quantidade;
                total += subtotal;

                const row = document.createElement('div');
                row.className = 'item';
                row.innerHTML = `
                    <div>
                        <strong>${item.nome}</strong>
                        <div>${formatCurrency(item.preco)} cada</div>
                    </div>
                    <div class="qty-controls">
                        <button onclick="updateQuantity(${item.id}, -1)">-</button>
                        <span>${item.quantidade}</span>
                        <button onclick="updateQuantity(${item.id}, 1)">+</button>
                        <strong>${formatCurrency(subtotal)}</strong>
                    </div>
                `;
                container.appendChild(row);
            });

            const totalRow = document.createElement('div');
            totalRow.style.textAlign = 'right';
            totalRow.style.marginTop = '12px';
            totalRow.innerHTML = `<strong>Total: ${formatCurrency(total)}</strong>`;
            container.appendChild(totalRow);
        }

        renderCart();
        logFlow('Página de carrinho carregada', { totem, carrinho: cart });
    </script>
</body>
</html>