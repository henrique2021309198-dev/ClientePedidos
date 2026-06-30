<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Trebuchet MS", sans-serif;
            background: linear-gradient(180deg, #fff8f0 0%, #f6f1eb 100%);
            color: #2e2018;
        }
        .topbar {
            background: rgba(255, 252, 248, 0.92);
            padding: 18px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #f0dfcf;
            position: sticky;
            top: 0;
            backdrop-filter: blur(8px);
            z-index: 10;
        }
        .title-block h3 { margin: 0 0 6px; font-size: 1.45rem; }
        .title-block p { margin: 0; color: #7a6759; font-size: .95rem; }
        .filters { display: flex; gap: 10px; flex-wrap: wrap; }
        .filters button {
            border: 1px solid #ead9ca;
            background: #fffaf4;
            color: #7a5b41;
            padding: 10px 14px;
            border-radius: 999px;
            cursor: pointer;
            font-weight: 700;
        }
        .filters button.active { background: #2e2018; color: #fff; border-color: #2e2018; }
        .container { max-width: 1180px; margin: 0 auto; padding: 28px 20px 130px; }
        .hero-pill {
            padding: 12px 16px;
            border-radius: 999px;
            background: #2e2018;
            color: #fff;
            font-weight: 700;
            white-space: nowrap;
        }
        .products { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 22px; }
        .product-card {
            background: rgba(255, 252, 248, 0.95);
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 14px 28px rgba(76, 48, 28, 0.08);
            border: 1px solid #f0e1d4;
        }
        .product-media {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 210px;
            padding: 16px;
            background: linear-gradient(180deg, #fff 0%, #faf3eb 100%);
        }
        .product-card img {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
        }
        .product-info { padding: 18px; }
        .product-info h3 { margin: 0 0 8px; font-size: 1.15rem; }
        .product-info p { margin: 0 0 14px; color: #7a6759; min-height: 42px; }
        .product-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
        }
        .price {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }
        .price small { color: #907b6c; }
        .product-info button {
            min-width: 142px;
            background: linear-gradient(135deg, #d96a32 0%, #ff8c42 100%);
            color: #fff;
            border: 0;
            padding: 14px 16px;
            border-radius: 14px;
            cursor: pointer;
            font-weight: 700;
            box-shadow: 0 12px 22px rgba(217, 106, 50, 0.22);
        }
        .cart-floating {
            position: fixed;
            right: 24px;
            bottom: 24px;
            background: linear-gradient(135deg, #2e2018 0%, #4b3122 100%);
            color: #fff;
            border: 0;
            padding: 18px 22px;
            border-radius: 24px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 16px;
            min-width: 280px;
            box-shadow: 0 18px 32px rgba(46, 32, 24, 0.28);
        }
        .cart-floating strong,
        .cart-floating span {
            display: block;
            text-align: left;
        }
        .cart-floating strong { font-size: 1rem; }
        .cart-floating span { color: #e6d8cd; font-size: .92rem; }
        .cart-floating .cart-count {
            width: 54px;
            height: 54px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: 700;
            flex-shrink: 0;
        }
        @media (max-width: 768px) {
            .topbar {
                align-items: flex-start;
                flex-direction: column;
                gap: 14px;
            }
            .cart-floating {
                left: 16px;
                right: 16px;
                bottom: 16px;
                min-width: 0;
            }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="title-block">
            <h3>Cardapio do totem</h3>
            <p>Monte seu pedido, filtre por categoria e acompanhe o carrinho em tempo real.</p>
        </div>
        <div class="filters" id="filters"></div>
    </header>

    <main class="container">
        <div id="totemBadge" class="hero-pill" style="margin-bottom: 22px; display: inline-flex;"></div>
        <section class="products" id="products"></section>
    </main>

    <button class="cart-floating" onclick="window.location.href='<?= site_url('carrinho') ?>'">
        <div>
            <strong>Ver carrinho</strong>
            <span id="cartSummary">Nenhum item selecionado</span>
        </div>
        <div class="cart-count" id="cartCount">0</div>
    </button>

    <script>
        const DEBUG_FLOW = true;
        function logFlow(event, payload = {}) {
            if (!DEBUG_FLOW) return;
            console.log(`[ClientePedidos][Produtos] ${event}`, payload);
        }

        const totem = JSON.parse(localStorage.getItem('pedidoTotem') || 'null');
        if (!totem || !totem.id) {
            logFlow('Totem ausente, redirecionando para configuração', { totem });
            window.location.href = '<?= site_url('totem?totem=obrigatorio') ?>';
        }

        let allProducts = [];
        let selectedCategory = 'Todas';

        const cart = JSON.parse(localStorage.getItem('pedidoCart') || '[]');

        function formatCurrency(value) {
            return 'R$ ' + Number(value).toFixed(2).replace('.', ',');
        }

        function updateCartCount() {
            const count = cart.reduce((sum, item) => sum + item.quantidade, 0);
            const total = cart.reduce((sum, item) => sum + (item.preco * item.quantidade), 0);
            document.getElementById('cartCount').textContent = count;
            document.getElementById('cartSummary').textContent = count > 0
                ? `${count} item(ns) • ${formatCurrency(total)}`
                : 'Nenhum item selecionado';
        }

        function renderTotemBadge() {
            const badge = document.getElementById('totemBadge');
            badge.textContent = totem ? `Totem ativo: ${totem.nome}` : 'Totem não selecionado';
        }

        function addToCart(product) {
            const existing = cart.find(item => item.id === product.id);
            if (existing) {
                existing.quantidade += 1;
            } else {
                cart.push({ ...product, quantidade: 1 });
            }
            localStorage.setItem('pedidoCart', JSON.stringify(cart));
            updateCartCount();
            logFlow('Item adicionado no carrinho', {
                produto: { id: product.id, nome: product.nome },
                carrinho: cart,
            });
        }

        async function loadProducts() {
            try {
                logFlow('Buscando produtos na API');
                const response = await fetch('<?= site_url('api/produtos') ?>');
                const data = await response.json();
                logFlow('Resposta da API de produtos', { status: response.status, total: (data.produtos || []).length });

                if (data.error) {
                    throw new Error(data.error);
                }

                allProducts = data.produtos || [];
                const categories = ['Todas', ...(data.categorias || [])];

                const filters = document.getElementById('filters');
                filters.innerHTML = '';
                categories.forEach(category => {
                    const button = document.createElement('button');
                    button.textContent = category;
                    button.className = category === selectedCategory ? 'active' : '';
                    button.addEventListener('click', () => {
                        selectedCategory = category;
                        logFlow('Filtro de categoria alterado', { categoria: category });
                        renderProducts();
                        document.querySelectorAll('#filters button').forEach(btn => btn.classList.remove('active'));
                        button.classList.add('active');
                    });
                    filters.appendChild(button);
                });

                renderProducts();
            } catch (error) {
                document.getElementById('products').innerHTML = '<p>Erro ao carregar produtos.</p>';
            }
        }

        function renderProducts() {
            const productsContainer = document.getElementById('products');
            const filtered = selectedCategory === 'Todas'
                ? allProducts
                : allProducts.filter(item => item.categoria === selectedCategory);

            productsContainer.innerHTML = '';
            filtered.forEach(product => {
                const card = document.createElement('div');
                card.className = 'product-card';
                card.innerHTML = `
                    <div class="product-media">
                        <img src="${product.imagem}" alt="${product.nome}">
                    </div>
                    <div class="product-info">
                        <h3>${product.nome}</h3>
                        <p>${product.descricao}</p>
                        <div class="product-footer">
                            <div class="price">
                                <small>Preco</small>
                                <strong>${formatCurrency(product.preco)}</strong>
                            </div>
                            <button onclick='addToCart(${JSON.stringify(product)})'>Adicionar</button>
                        </div>
                    </div>
                `;
                productsContainer.appendChild(card);
            });
        }

        updateCartCount();
        renderTotemBadge();
        logFlow('Página de produtos carregada', { totem, carrinho: cart });
        loadProducts();
    </script>
</body>
</html>