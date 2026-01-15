<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Cloudstudio\Ollama\Facades\Ollama;
use Illuminate\Http\Request;

class OllamaController extends Controller
{
    public function index(Request $request)
    {
        try {
            $response = Ollama::agent('Asistente del Gremio Linhir.')
                ->prompt($request->msg)
                ->model('llama3.1')
                ->options([
                    'temperature' => 0.8,  // More creative
                    'top_p' => 0.9,
                    'max_tokens' => 500
                ])
                ->ask();
            return $response['response'];
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Autenticación fallida'], 401);
        }        

    }
}
