<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurar Totem</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Trebuchet MS", sans-serif;
            background:
                radial-gradient(circle at top right, rgba(255, 179, 102, 0.28), transparent 28%),
                linear-gradient(160deg, #f8efe5 0%, #fff8f0 48%, #f2ebe5 100%);
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
            width: 100%;
            max-width: 620px;
            background: rgba(255, 252, 248, 0.94);
            border-radius: 28px;
            padding: 34px 30px;
            box-shadow: 0 24px 54px rgba(76, 48, 28, 0.12);
            border: 1px solid rgba(117, 77, 50, 0.1);
        }
        .eyebrow {
            display: inline-flex;
            padding: 8px 12px;
            border-radius: 999px;
            background: #fff1df;
            color: #9a5d2f;
            font-size: .85rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        h1 {
            margin: 16px 0 12px;
            font-family: Georgia, serif;
            font-size: 2.3rem;
        }
        .lead {
            margin: 0 0 24px;
            color: #6d5a4d;
            line-height: 1.6;
        }
        .status-card {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            align-items: center;
            background: linear-gradient(135deg, #fff3dd 0%, #fffdf9 100%);
            border: 1px solid #efdecd;
            border-radius: 22px;
            padding: 18px 20px;
            margin-bottom: 22px;
        }
        .status-card strong {
            display: block;
            margin-bottom: 4px;
            font-size: 1rem;
        }
        .status-card span { color: #7a6759; }
        .status-pill {
            background: #2e2018;
            color: #fff;
            border-radius: 999px;
            padding: 10px 14px;
            font-weight: 700;
            white-space: nowrap;
        }
        .field, .create-row { margin-bottom: 14px; }
        label {
            display: block;
            margin-bottom: 8px;
            font-size: .92rem;
            font-weight: 700;
        }
        select, input {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #e5d6c7;
            border-radius: 14px;
            font-size: 1rem;
            background: #fff;
        }
        .create-row {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 12px;
        }
        .actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 24px;
        }
        .btn {
            border: 0;
            padding: 16px 18px;
            border-radius: 16px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
        }
        .btn-primary {
            background: linear-gradient(135deg, #d96a32 0%, #ff8c42 100%);
            color: #fff;
            box-shadow: 0 14px 24px rgba(217, 106, 50, 0.22);
        }
        .btn-secondary {
            background: #fff;
            color: #7a4d2a;
            border: 1px solid #e8d6c5;
        }
        .message {
            min-height: 22px;
            margin-top: 16px;
            color: #b42318;
        }
        @media (max-width: 680px) {
            .create-row,
            .actions {
                grid-template-columns: 1fr;
            }
            .status-card {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <span class="eyebrow">Configuracao do local</span>
            <h1>Selecionar totem</h1>
            <p class="lead">Selecione o totem deste dispositivo e depois toque em iniciar sistema para abrir a tela de inicio do cliente.</p>

            <div class="status-card">
                <div>
                    <strong>Totem atualmente vinculado</strong>
                    <span id="totemStatusText">Nenhum totem configurado neste dispositivo.</span>
                </div>
                <div class="status-pill" id="totemStatusPill">Pendente</div>
            </div>

            <div class="field">
                <label for="totemSelect">Escolha um totem cadastrado</label>
                <select id="totemSelect">
                    <option value="">Carregando totems...</option>
                </select>
            </div>

            <div class="create-row">
                <input type="text" id="novoTotemNome" placeholder="Criar novo totem, ex: Patio 01">
                <button type="button" class="btn btn-secondary" id="criarTotemBtn">Criar totem</button>
            </div>

            <div class="actions">
                <button type="button" class="btn btn-primary" id="salvarTotemBtn">Iniciar sistema</button>
                <button type="button" class="btn btn-secondary" id="cancelarBtn">Voltar</button>
            </div>

            <div class="message" id="pageMessage"></div>
        </div>
    </div>

    <script>
        const DEBUG_FLOW = true;
        function logFlow(event, payload = {}) {
            if (!DEBUG_FLOW) return;
            console.log(`[ClientePedidos][Totem] ${event}`, payload);
        }

        const storageKey = 'pedidoTotem';

        function getSelectedTotem() {
            return JSON.parse(localStorage.getItem(storageKey) || 'null');
        }

        function setMessage(text, isError = true) {
            const el = document.getElementById('pageMessage');
            el.textContent = text;
            el.style.color = isError ? '#b42318' : '#166534';
        }

        function renderCurrentTotem() {
            const current = getSelectedTotem();
            const text = document.getElementById('totemStatusText');
            const pill = document.getElementById('totemStatusPill');

            if (!current || !current.id) {
                text.textContent = 'Nenhum totem configurado neste dispositivo.';
                pill.textContent = 'Pendente';
                return;
            }

            text.textContent = `${current.nome}${current.codigo ? ` (${current.codigo})` : ''}`;
            pill.textContent = 'Configurado';
            logFlow('Totem identificado no dispositivo', current);
        }

        async function loadTotens() {
            const select = document.getElementById('totemSelect');
            const selected = getSelectedTotem();

            try {
                const response = await fetch('<?= site_url('api/totens') ?>');
                const data = await response.json();
                const totems = data.totens || [];
                logFlow('Totens carregados', { total: totems.length, totems });

                select.innerHTML = '<option value="">Selecione o totem</option>';
                totems.forEach(totem => {
                    const option = document.createElement('option');
                    option.value = String(totem.id);
                    option.textContent = `${totem.nome} (${totem.codigo})`;
                    option.dataset.nome = totem.nome;
                    option.dataset.codigo = totem.codigo;
                    if (selected && Number(selected.id) === Number(totem.id)) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                });
            } catch (error) {
                select.innerHTML = '<option value="">Erro ao carregar totems</option>';
                setMessage('Nao foi possivel carregar os totems.');
            }
        }

        document.getElementById('criarTotemBtn').addEventListener('click', async function () {
            const nome = document.getElementById('novoTotemNome').value.trim();
            if (!nome) {
                setMessage('Informe o nome do totem para criar.');
                return;
            }

            this.disabled = true;
            try {
                const response = await fetch('<?= site_url('api/totens') ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ nome })
                });
                const data = await response.json();

                if (!response.ok || !data.totem) {
                    throw new Error(data.error || 'Nao foi possivel criar o totem.');
                }

                localStorage.setItem(storageKey, JSON.stringify({
                    id: Number(data.totem.id),
                    nome: data.totem.nome,
                    codigo: data.totem.codigo || ''
                }));
                logFlow('Totem criado e salvo localmente', data.totem);
                document.getElementById('novoTotemNome').value = '';
                await loadTotens();
                renderCurrentTotem();
                setMessage('Totem criado e configurado com sucesso.', false);
            } catch (error) {
                setMessage(error.message || 'Erro ao criar totem.');
            } finally {
                this.disabled = false;
            }
        });

        document.getElementById('salvarTotemBtn').addEventListener('click', function () {
            const select = document.getElementById('totemSelect');
            const option = select.options[select.selectedIndex];

            if (!select.value) {
                setMessage('Selecione um totem para continuar.');
                return;
            }

            localStorage.setItem(storageKey, JSON.stringify({
                id: Number(select.value),
                nome: option.dataset.nome || option.textContent,
                codigo: option.dataset.codigo || ''
            }));
            logFlow('Totem selecionado e salvo', {
                id: Number(select.value),
                nome: option.dataset.nome || option.textContent,
                codigo: option.dataset.codigo || ''
            });

            window.location.href = '<?= site_url('inicio') ?>';
        });

        document.getElementById('cancelarBtn').addEventListener('click', function () {
            const current = getSelectedTotem();
            if (current && current.id) {
                window.location.href = '<?= site_url('inicio') ?>';
                return;
            }

            setMessage('Configure um totem antes de liberar este dispositivo para pedidos.');
        });

        const searchParams = new URLSearchParams(window.location.search);
        if (searchParams.get('totem') === 'obrigatorio') {
            setMessage('Configure o totem deste dispositivo para liberar os pedidos.');
        }

        if (searchParams.get('configurarTotem') === '1') {
            setMessage('Selecione ou troque o totem deste dispositivo.', false);
        }

        renderCurrentTotem();
        loadTotens();
        logFlow('Página de configuração de totem carregada');
    </script>
</body>
</html>