import os
import joblib
from flask import Flask, request, jsonify

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

try:
    print(f"Tentando carregar o modelo de: {MODEL_PATH}")
    model = joblib.load(MODEL_PATH)
    print("Modelo carregado com sucesso.")

    print(f"Tentando carregar o vetorizador de: {VECTORIZER_PATH}")
    vectorizer = joblib.load(VECTORIZER_PATH)
    print("Vetorizador carregado com sucesso.")

except FileNotFoundError as e:
    print(f"ERRO CRÍTICO: Arquivo não encontrado - {e}. Verifique se o modelo e o vetorizador existem nos caminhos especificados.")
except Exception as e:
    print(f"ERRO CRÍTICO: Ocorreu um erro ao carregar os arquivos do modelo: {e}")


@app.route('/predict', methods=['POST'])
def predict():
    # Verifica se o modelo foi carregado corretamente na inicialização
    if not model or not vectorizer:
        return jsonify({'error': 'Modelo não carregado. Verifique os logs do servidor Flask para mais detalhes.'}), 500

    try:
        data = request.get_json()
        text_features = vectorizer.transform([data['text']])
        prediction = model.predict(text_features)
        return jsonify({'category': prediction[0]})
    except Exception as e:
        return jsonify({'error': f'Erro durante a predição: {str(e)}'}), 500

if __name__ == '__main__':
    # Use debug=True apenas em desenvolvimento
    app.run(host='127.0.0.1', port=5000, debug=True)