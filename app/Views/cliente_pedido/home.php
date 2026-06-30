<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Pedido</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Georgia, serif;
            background:
                radial-gradient(circle at top left, rgba(255, 193, 117, 0.35), transparent 30%),
                linear-gradient(160deg, #f8efe5 0%, #fff8f0 45%, #f3ede8 100%);
            color: #2e2018;
        }
        .container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .card {
            text-align: center;
            max-width: 560px;
            width: 100%;
            background: rgba(255, 252, 248, 0.92);
            border-radius: 28px;
            padding: 44px 30px;
            box-shadow: 0 20px 50px rgba(76, 48, 28, 0.12);
            border: 1px solid rgba(117, 77, 50, 0.08);
        }
        .logo {
            width: 132px;
            height: 132px;
            object-fit: cover;
            border-radius: 999px;
            margin: 0 auto 22px;
            background: #f3f3f3;
            border: 6px solid #fff;
            box-shadow: 0 10px 28px rgba(76, 48, 28, 0.12);
        }
        h1 {
            margin: 0 0 10px;
            font-size: 2.4rem;
            letter-spacing: 0.03em;
        }
        p {
            color: #6d5a4d;
            margin: 0 0 28px;
            font-family: "Trebuchet MS", sans-serif;
            line-height: 1.6;
        }
        .totem-chip {
            display: inline-flex;
            gap: 8px;
            align-items: center;
            padding: 10px 16px;
            background: #2e2018;
            color: #fff;
            border-radius: 999px;
            font-size: .95rem;
            margin-bottom: 18px;
            font-family: "Trebuchet MS", sans-serif;
        }
        .hero {
            margin: 26px 0 30px;
            padding: 20px;
            border-radius: 22px;
            background: linear-gradient(135deg, #fff3dd 0%, #fffaf3 100%);
            border: 1px solid #f0dcc5;
            text-align: left;
        }
        .hero-title {
            margin: 0 0 10px;
            font-size: 1.15rem;
            font-weight: 700;
            font-family: "Trebuchet MS", sans-serif;
        }
        .hero-copy {
            margin: 0;
            font-size: .98rem;
        }
        .actions {
            display: grid;
            gap: 14px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            border: 0;
            padding: 18px 22px;
            border-radius: 999px;
            font-size: 1.05rem;
            font-weight: 700;
            cursor: pointer;
            font-family: "Trebuchet MS", sans-serif;
            transition: transform .15s ease, box-shadow .15s ease, background .15s ease;
        }
        .btn:hover {
            transform: translateY(-1px);
        }
        .btn-primary {
            background: linear-gradient(135deg, #d96a32 0%, #ff8c42 100%);
            color: #fff;
            box-shadow: 0 16px 28px rgba(217, 106, 50, 0.28);
        }
        .btn-secondary {
            background: #fff;
            color: #7a4d2a;
            border: 1px solid #e9d5c2;
        }
        .error-msg {
            color: #b42318;
            font-size: .95rem;
            min-height: 22px;
            margin-top: 16px;
            font-family: "Trebuchet MS", sans-serif;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <img class="logo" src="https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=600&q=80" alt="Logo do restaurante">
            <h1>Pedido Facil</h1>
            <p>Seu totem ja esta preparado. Toque para abrir o cardapio e iniciar um novo atendimento.</p>
            <div id="selectedTotem" class="totem-chip" style="display:none;"></div>

            <div class="hero">
                <div class="hero-title">Pronto para o proximo pedido</div>
                <p class="hero-copy">O cliente so precisa tocar em iniciar. A configuracao do local permanece salva neste dispositivo.</p>
            </div>

            <div class="actions">
                <button id="startBtn" class="btn btn-primary">Iniciar pedido</button>
            </div>

            <div class="error-msg" id="homeMessage"></div>
        </div>
    </div>

    <script>
        const storageKey = 'pedidoTotem';

        function getSelectedTotem() {
            return JSON.parse(localStorage.getItem(storageKey) || 'null');
        }

        function setMessage(text, isError = true) {
            const el = document.getElementById('homeMessage');
            el.textContent = text;
            el.style.color = isError ? '#b42318' : '#166534';
        }

        function updateSelectedTotemChip() {
            const chip = document.getElementById('selectedTotem');
            const selected = getSelectedTotem();
            if (!selected) {
                chip.style.display = 'none';
                chip.textContent = '';
                return;
            }

            chip.style.display = 'inline-flex';
            chip.innerHTML = `Totem ativo: <strong>${selected.nome}</strong>`;
        }

        document.getElementById('startBtn').addEventListener('click', function () {
            const selected = getSelectedTotem();
            if (!selected || !selected.id) {
                window.location.href = '<?= site_url('totem?totem=obrigatorio') ?>';
                return;
            }

            localStorage.removeItem('pedidoCart');
            window.location.href = '<?= site_url('produtos') ?>';
        });

        const searchParams = new URLSearchParams(window.location.search);
        if (searchParams.get('pedidoFinalizado') === '1') {
            localStorage.removeItem('pedidoCart');
            setMessage('Pedido finalizado. Este totem esta pronto para um novo atendimento.', false);
        }

        updateSelectedTotemChip();

        const selected = getSelectedTotem();
        if (!selected || !selected.id) {
            window.location.href = '<?= site_url('totem?totem=obrigatorio') ?>';
        }
    </script>
</body>
</html>