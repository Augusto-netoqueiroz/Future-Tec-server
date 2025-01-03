<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Events\WappConnectEvent;

class WppConnectController extends Controller
{
    /**
     * Exibe o formulário inicial.
     */
    public function index()
    {
        return view('wppconnect.index'); // Exibe a view
    }

    /**
     * Inicia uma nova sessão do WhatsApp Connect.
     */
    public function startSession(Request $request)
    {
        $sessionName = $request->input('session');
        $secretKey = 'THISISMYSECURETOKEN'; // Altere conforme necessário

        try {
            // Passo 1: Gerar o token
            $tokenResponse = $this->generateToken($sessionName, $secretKey);

            if ($tokenResponse['status'] === 'error') {
                return redirect()->back()->withErrors($tokenResponse['message']);
            }

            $token = $tokenResponse['token'];

            // Passo 2: Iniciar a sessão com o token
            $sessionResponse = $this->startWppConnectSession($sessionName, $token);

            // Se o status for "success", começamos a esperar pelo QR Code
            if ($sessionResponse['status'] === 'success') {
                // Espera ativa por 90 segundos (1 minuto e 30 segundos)
                $qrCodeGenerated = false;
                $startTime = time();

                while (time() - $startTime < 90) {
                    $sessionStatus = $this->checkSessionStatus($sessionName, $token);
                    if (isset($sessionStatus['status']) && $sessionStatus['status'] === 'QR_CODE_GENERATED') {
                        $qrCodeGenerated = true;
                        break; // QR Code gerado, sai do loop
                    }

                    sleep(5); // Espera 5 segundos antes de tentar novamente
                }

                // Emitir evento WebSocket se o QR Code for gerado
                event(new SessionStarted($sessionName, 'success', 'QR Code gerado!'));

                if ($qrCodeGenerated) {
                    return redirect()->back()->with('success', 'Sessão iniciada com sucesso! QR Code gerado.');
                }

                return redirect()->back()->withErrors('Erro: QR Code não gerado após 1 minuto e 30 segundos.');
            }

            return redirect()->back()->withErrors('Erro ao iniciar a sessão: ' . $sessionResponse['message']);
        } catch (\Exception $e) {
            Log::error('Erro geral no startSession: ' . $e->getMessage());
            return redirect()->back()->withErrors('Erro ao processar a solicitação.');
        }
    }

    // Funções auxiliares para gerar token e verificar status permanecem inalteradas.


    /**
     * Gera o token para a sessão.
     */
    private function generateToken($sessionName, $secretKey)
    {
        try {
            $url = "http://93.127.212.237:21465/api/{$sessionName}/{$secretKey}/generate-token";
            Log::info("Requisição para gerar token: {$url}");

            $response = Http::post($url);
            $data = $response->json();

            Log::info("Resposta da API (gerar token): " . json_encode($data));

            if ($response->successful() && isset($data['token'])) {
                Log::info("Token gerado com sucesso: {$data['token']}");
                return ['status' => 'success', 'token' => $data['token']];
            }

            return ['status' => 'error', 'message' => $data['message'] ?? 'Erro desconhecido ao gerar o token.'];
        } catch (\Exception $e) {
            Log::error('Erro ao gerar token: ' . $e->getMessage());
            return ['status' => 'error', 'message' => 'Erro ao conectar com o servidor para gerar o token.'];
        }
    }

    /**
     * Inicia a sessão do WhatsApp Connect usando Bearer Token.
     */
    private function startWppConnectSession($sessionName, $token)
    {
        try {
            $url = "http://93.127.212.237:21465/api/{$sessionName}/start-session";
            Log::info("Requisição para iniciar sessão: {$url}");

            // Envia a requisição com o Bearer Token no cabeçalho
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$token}"
            ])->post($url);

            $data = $response->json();
            Log::info("Resposta da API (iniciar sessão): " . json_encode($data));

            return $data;
        } catch (\Exception $e) {
            Log::error('Erro ao iniciar sessão: ' . $e->getMessage());
            return ['status' => 'error', 'message' => 'Erro ao iniciar a sessão.'];
        }
    }

    /**
     * Verifica o status da sessão (se o QR Code foi gerado).
     */
    private function checkSessionStatus($sessionName, $token)
    {
        try {
            $url = "http://93.127.212.237:21465/api/{$sessionName}/check-status";
            Log::info("Verificando status da sessão: {$url}");

            // Envia a requisição com o Bearer Token no cabeçalho
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$token}"
            ])->post($url);

            $data = $response->json();
            Log::info("Resposta da API (verificar status): " . json_encode($data));

            return $data;
        } catch (\Exception $e) {
            Log::error('Erro ao verificar status da sessão: ' . $e->getMessage());
            return ['status' => 'error', 'message' => 'Erro ao verificar o status da sessão.'];
        }
    }

    public function handleWebhook(Request $request)
    {
        // Verifique os dados recebidos do webhook
        $data = $request->all();

        // Você pode registrar os dados ou processá-los conforme necessário
        Log::info('Recebido evento do WappConnect:', $data);

        // Disparar o evento para o Laravel Echo, se necessário
        // Exemplo:
        // broadcast(new WappConnectEvent($data));

        // Retorne uma resposta de sucesso
        return response()->json(['status' => 'ok']);
    }


    /**
 * Exibe todas as sessões ativas do WAPP Connect Server.
 */
public function showAllSessions()
    {
        $secretKey = 'THISISMYSECURETOKEN'; // Substitua pela sua chave secreta
        $url = "http://93.127.212.237:21465/api/{$secretKey}/show-all-sessions";

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$secretKey}",
            ])->get($url);

            if ($response->successful()) {
                $data = $response->json();
                return view('wppconnect.sessions', ['sessions' => $data['response']]);
            }

            return redirect()->back()->withErrors('Erro ao buscar as sessões ativas.');
        } catch (\Exception $e) {
            Log::error('Erro ao buscar sessões: ' . $e->getMessage());
            return redirect()->back()->withErrors('Erro ao processar a solicitação.');
        }
    }


}

