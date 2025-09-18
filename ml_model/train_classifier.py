import pandas as pd
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.linear_model import LogisticRegression
import joblib
import os

# 1. Carregamento dos Dados
dataset_path = os.path.join(os.path.dirname(__file__), 'faturas_dataset.csv')
try:
    df = pd.read_csv(dataset_path)
except FileNotFoundError:
    print(f"Erro: Arquivo de dataset '{dataset_path}' não encontrado.")
    print("Verifique se o arquivo 'faturas_dataset.csv' existe no mesmo diretório.")
    exit()

df.dropna(subset=['text', 'category'], inplace=True)

if df.empty:
    print("O dataset está vazio. Verifique o arquivo CSV e o processo de extração.")
    exit()

# 2. Preparação e Vetorização dos Dados
print("Criando e treinando o vetorizador TF-IDF...")
vectorizer = TfidfVectorizer(stop_words=['de', 'a', 'o', 'que', 'e', 'do', 'da', 'em', 'um'])
X_train = vectorizer.fit_transform(df['text'])
y_train = df['category']
print("Vetorização concluída.")

# 3. Treinamento do Modelo de Classificação
print("Treinando o modelo de classificação (Logistic Regression)...")
classifier = LogisticRegression(random_state=42)
classifier.fit(X_train, y_train)
print("Treinamento concluído.")

# 4. Salvando o Vetorizador e o Modelo
base_dir = os.path.dirname(__file__)
vectorizer_path = os.path.join(base_dir, 'tfidf_vectorizer.pkl')
model_path = os.path.join(base_dir, 'fatura_classifier_model.pkl')

print(f"\nSalvando o vetorizador em: {vectorizer_path}")
joblib.dump(vectorizer, vectorizer_path)

print(f"Salvando o modelo em: {model_path}")
joblib.dump(classifier, model_path)

print("\nArquivos do modelo salvos com sucesso!")

# Teste rápido
if not df.empty:
    try:
        sample = df.sample(1).iloc[0]
        sample_text = sample['text']

        # Carrega os modelos salvos para testar
        loaded_vectorizer = joblib.load(vectorizer_path)
        loaded_model = joblib.load(model_path)

        # Faz a predição
        text_features = loaded_vectorizer.transform([sample_text])
        prediction = loaded_model.predict(text_features)

        print(f"\n--- Teste Rápido ---")
        print(f"Texto de exemplo (Categoria Real: {sample['category']}): '{sample['text'][:100]}...'")
        print(f"Predição do Modelo: '{prediction[0]}'")
    except Exception as e:
        print(f"Não foi possível realizar o teste rápido: {e}")