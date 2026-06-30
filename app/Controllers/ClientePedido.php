<?php

namespace App\Controllers;

class ClientePedido extends BaseController
{
    private const API_BASE_URL = 'http://localhost/paginacao/public';
    private const API_KEY = 'AkwgG04wEQYb0nroYIR3MwX7DXcyqyDq';

    public function index(): string
    {
        return view('cliente_pedido/totem');
    }

    public function inicio(): string
    {
        return view('cliente_pedido/home');
    }

    public function totem(): string
    {
        return view('cliente_pedido/totem');
    }

    public function produtos(): string
    {
        return view('cliente_pedido/produtos');
    }

    public function carrinho(): string
    {
        return view('cliente_pedido/carrinho');
    }

    public function checkout(): string
    {
        return view('cliente_pedido/checkout');
    }

    public function nota(string $codigo): string
    {
        $data = [
            'codigo' => $codigo,
            'itens' => session('pedido_itens') ?? [],
            'total' => session('pedido_total') ?? 0,
            'totem' => session('pedido_totem') ?? null,
        ];

        return view('cliente_pedido/nota', $data);
    }

    public function apiTotens()
    {
        $response = $this->requestToApi('/api/totens', 'GET', [], true);

        if ($response['status'] !== 200) {
            return $this->response->setStatusCode($response['status'])->setJSON([
                'error' => 'Não foi possível carregar os totems.'
            ]);
        }

        return $this->response->setJSON(json_decode($response['body'], true) ?: ['totens' => []]);
    }

    public function apiCriarTotem()
    {
        $json = $this->request->getJSON(true);
        $nome = trim((string) ($json['nome'] ?? ''));

        if ($nome === '') {
            return $this->response->setStatusCode(422)->setJSON([
                'error' => 'Informe o nome do totem.'
            ]);
        }

        $response = $this->requestToApi('/api/totens', 'POST', ['nome' => $nome], true);

        return $this->response
            ->setStatusCode($response['status'])
            ->setJSON(json_decode($response['body'], true) ?: ['error' => 'Erro ao criar totem.']);
    }

    public function apiProdutos()
    {
        $response = $this->requestToApi('/api/produtos');

        if ($response['status'] !== 200) {
            return $this->response->setStatusCode($response['status'])->setJSON([
                'error' => 'Não foi possível carregar os produtos.'
            ]);
        }

        $data = json_decode($response['body'], true);
        if (!is_array($data)) {
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Resposta inválida da API.'
            ]);
        }

        $produtos = [];
        foreach ($data as $item) {
            $produtos[] = [
                'id' => (int) ($item['id'] ?? 0),
                'nome' => $item['nome'] ?? 'Produto',
                'descricao' => $item['descricao'] ?? 'Produto disponível',
                'preco' => (float) ($item['preco'] ?? 0),
                'categoria' => $item['categoria'] ?? 'Sem categoria',
                'imagem' => $this->buildImageUrl($item['foto'] ?? null),
            ];
        }

        $categorias = array_values(array_unique(array_column($produtos, 'categoria')));

        return $this->response->setJSON([
            'produtos' => $produtos,
            'categorias' => $categorias,
        ]);
    }

    public function apiCheckout()
    {
        $json = $this->request->getJSON(true);
        $itens = $json['itens'] ?? [];
        $totemId = (int) ($json['totem_id'] ?? 0);
        $totemNome = trim((string) ($json['totem_nome'] ?? ''));

        if (empty($itens)) {
            return $this->response->setStatusCode(422)->setJSON([
                'error' => 'Adicione pelo menos um item ao carrinho.'
            ]);
        }

        if ($totemId <= 0) {
            return $this->response->setStatusCode(422)->setJSON([
                'error' => 'Selecione um totem antes de iniciar o pedido.'
            ]);
        }

        $payload = [
            'status' => 'novo',
            'totem_id' => $totemId,
            'produtos' => array_map(function ($item) {
                return [
                    'id_produto' => (int) ($item['id'] ?? 0),
                    'quantidade' => (int) ($item['quantidade'] ?? 0),
                    'preco_unitario' => (float) ($item['preco'] ?? 0),
                ];
            }, $itens)
        ];

        $response = $this->requestToApi('/api/checkout', 'POST', $payload, true);

        if ($response['status'] !== 201 && $response['status'] !== 200) {
            $body = json_decode($response['body'], true);
            $message = $body['messages']['error'] ?? $body['message'] ?? $body['error'] ?? 'Não foi possível finalizar o pedido.';

            return $this->response->setStatusCode($response['status'])->setJSON([
                'error' => $message
            ]);
        }

        $body = json_decode($response['body'], true);
        $pedidoId = (int) ($body['id_pedido'] ?? 0);
        $codigo = 'PED-' . $pedidoId;

        $total = 0;
        foreach ($itens as $item) {
            $total += (float) ($item['preco'] ?? 0) * (int) ($item['quantidade'] ?? 0);
        }

        session()->set([
            'pedido_itens' => $itens,
            'pedido_total' => number_format($total, 2, '.', ''),
            'pedido_codigo' => $codigo,
            'pedido_id' => $pedidoId,
            'pedido_totem' => [
                'id' => $totemId,
                'nome' => $totemNome,
            ],
        ]);

        return $this->response->setJSON([
            'success' => true,
            'codigo' => $codigo,
            'total' => number_format($total, 2, '.', ''),
            'redirect' => site_url('nota/' . $codigo)
        ]);
    }

    private function requestToApi(string $endpoint, string $method = 'GET', array $data = [], bool $withApiKey = false): array
    {
        $url = self::API_BASE_URL . $endpoint;
        $headers = [];

        if ($method === 'POST') {
            $headers[] = 'Content-Type: application/json';
        }

        if ($withApiKey) {
            $headers[] = 'apiKey: ' . self::API_KEY;
        }

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        if ($method === 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $body = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);

        curl_close($curl);

        return [
            'status' => $status ?: 500,
            'body' => $body ?: '',
            'error' => $error,
        ];
    }

    private function buildImageUrl(?string $foto): string
    {
        if (empty($foto)) {
            return 'https://via.placeholder.com/600x400?text=Produto';
        }

        return self::API_BASE_URL . '/uploads/produtos/' . rawurlencode($foto);
    }
}