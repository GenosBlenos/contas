import os
import joblib
import logging
from flask import Flask, request, jsonify

# Configuração de logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

# --- Configuração de Caminhos ---
# Garante que os caminhos para o modelo e vetorizador estão corretos.
# Estes nomes devem ser os mesmos que você usou ao salvar no seu script de treinamento.
try:
    BASE_DIR = os.path.dirname(os.path.abspath(__file__))
    MODEL_PATH = os.path.join(BASE_DIR, 'fatura_classifier_model.pkl')
    VECTORIZER_PATH = os.path.join(BASE_DIR, 'tfidf_vectorizer.pkl')
except NameError:
    # Fallback para ambientes onde __file__ não está definido (ex: notebooks interativos)
    BASE_DIR = '.'
    MODEL_PATH = 'fatura_classifier_model.pkl'
    VECTORIZER_PATH = 'tfidf_vectorizer.pkl'

app = Flask(__name__)

# --- Carregamento do Modelo ---
# O modelo é carregado uma vez quando a aplicação inicia, não a cada requisição.
model = None
vectorizer = None

def load_model_and_vectorizer():
    """Carrega o modelo e vetorizador com tratamento de erro robusto."""
    global model, vectorizer
    
    try:
        logger.info(f"Tentando carregar o modelo de: {MODEL_PATH}")
        if not os.path.exists(MODEL_PATH):
            raise FileNotFoundError(f"Arquivo do modelo não encontrado: {MODEL_PATH}")
        
        model = joblib.load(MODEL_PATH)
        logger.info("Modelo carregado com sucesso.")

        logger.info(f"Tentando carregar o vetorizador de: {VECTORIZER_PATH}")
        if not os.path.exists(VECTORIZER_PATH):
            raise FileNotFoundError(f"Arquivo do vetorizador não encontrado: {VECTORIZER_PATH}")
        
        vectorizer = joblib.load(VECTORIZER_PATH)
        logger.info("Vetorizador carregado com sucesso.")
        
        return True

    except FileNotFoundError as e:
        logger.error(f"ERRO CRÍTICO: {e}")
        logger.error("Verifique se os arquivos do modelo existem e foram treinados corretamente.")
        return False
    except Exception as e:
        logger.error(f"ERRO CRÍTICO: Ocorreu um erro ao carregar os arquivos do modelo: {e}")
        return False

# Carrega o modelo na inicialização
model_loaded = load_model_and_vectorizer()

@app.route('/health', methods=['GET'])
def health_check():
    """Endpoint para verificar se a API está funcionando."""
    status = 'healthy' if model_loaded and model and vectorizer else 'unhealthy'
    return jsonify({
        'status': status,
        'model_loaded': model is not None,
        'vectorizer_loaded': vectorizer is not None
    }), 200 if status == 'healthy' else 500

@app.route('/predict', methods=['POST'])
def predict():
    """Endpoint principal para classificação de faturas."""
    # Verifica se o modelo foi carregado corretamente na inicialização
    if not model_loaded or not model or not vectorizer:
        logger.error("Tentativa de predição com modelo não carregado")
        return jsonify({
            'error': 'Modelo não carregado. Verifique os logs do servidor Flask para mais detalhes.',
            'status': 'model_not_loaded'
        }), 500

    try:
        # Validação da requisição JSON
        if not request.is_json:
            return jsonify({
                'error': 'Content-Type deve ser application/json',
                'status': 'invalid_content_type'
            }), 400

        data = request.get_json()
        
        # Validação dos dados de entrada
        if not data:
            return jsonify({
                'error': 'Body da requisição vazio ou JSON inválido',
                'status': 'empty_body'
            }), 400

        if 'text' not in data:
            return jsonify({
                'error': 'Campo "text" é obrigatório no JSON',
                'status': 'missing_text_field'
            }), 400

        text_input = data['text']
        
        # Validação do conteúdo do texto
        if not isinstance(text_input, str):
            return jsonify({
                'error': 'Campo "text" deve ser uma string',
                'status': 'invalid_text_type'
            }), 400

        if not text_input.strip():
            return jsonify({
                'error': 'Campo "text" não pode estar vazio',
                'status': 'empty_text'
            }), 400

        # Validação do tamanho do texto
        if len(text_input) > 100000:  # 100KB de texto
            return jsonify({
                'error': 'Texto muito longo. Máximo de 100.000 caracteres',
                'status': 'text_too_long'
            }), 400

        logger.info(f"Processando predição para texto com {len(text_input)} caracteres")

        # Processamento da predição
        text_features = vectorizer.transform([text_input])
        prediction = model.predict(text_features)
        prediction_proba = model.predict_proba(text_features)
        
        # Obter a confiança da predição
        max_proba = float(max(prediction_proba[0]))
        
        logger.info(f"Predição realizada: {prediction[0]} (confiança: {max_proba:.3f})")

        return jsonify({
            'category': prediction[0],
            'confidence': max_proba,
            'status': 'success'
        })

    except Exception as e:
        logger.error(f"Erro durante a predição: {str(e)}")
        return jsonify({
            'error': f'Erro interno durante a predição: {str(e)}',
            'status': 'prediction_error'
        }), 500

@app.errorhandler(404)
def not_found(error):
    return jsonify({
        'error': 'Endpoint não encontrado',
        'status': 'not_found'
    }), 404

@app.errorhandler(405)
def method_not_allowed(error):
    return jsonify({
        'error': 'Método não permitido para este endpoint',
        'status': 'method_not_allowed'
    }), 405

@app.errorhandler(500)
def internal_error(error):
    return jsonify({
        'error': 'Erro interno do servidor',
        'status': 'internal_error'
    }), 500

if __name__ == '__main__':
    # Configurações de ambiente
    debug_mode = os.getenv('FLASK_DEBUG', 'False').lower() == 'true'
    host = os.getenv('FLASK_HOST', '127.0.0.1')
    port = int(os.getenv('FLASK_PORT', 5000))
    
    logger.info(f"Iniciando aplicação Flask em {host}:{port} (debug={debug_mode})")
    
    if not model_loaded:
        logger.warning("ATENÇÃO: Aplicação iniciando sem modelo carregado!")
    
    app.run(host=host, port=port, debug=debug_mode)