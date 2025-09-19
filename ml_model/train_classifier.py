import pandas as pd
import numpy as np
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.linear_model import LogisticRegression
from sklearn.model_selection import train_test_split, cross_val_score
from sklearn.metrics import classification_report, accuracy_score, confusion_matrix
import joblib
import os
import logging

# Configuração de logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

def validate_dataset(df):
    """Valida o dataset e fornece estatísticas úteis."""
    logger.info("=== Validação do Dataset ===")
    
    # Estatísticas básicas
    logger.info(f"Total de amostras: {len(df)}")
    logger.info(f"Colunas presentes: {list(df.columns)}")
    
    # Verificar valores nulos
    null_counts = df.isnull().sum()
    if null_counts.any():
        logger.warning(f"Valores nulos encontrados:\n{null_counts}")
    
    # Distribuição das classes
    class_counts = df['category'].value_counts()
    logger.info("Distribuição das classes:")
    for category, count in class_counts.items():
        percentage = (count / len(df)) * 100
        logger.info(f"  {category}: {count} amostras ({percentage:.1f}%)")
    
    # Alertas sobre desbalanceamento
    min_samples = class_counts.min()
    max_samples = class_counts.max()
    
    if min_samples < 10:
        logger.warning(f"ATENÇÃO: Classe com poucas amostras ({min_samples}). Considere coletar mais dados.")
    
    if max_samples / min_samples > 5:
        logger.warning(f"ATENÇÃO: Dataset desbalanceado. Razão max/min: {max_samples/min_samples:.1f}")
    
    # Estatísticas de texto
    text_lengths = df['text'].str.len()
    logger.info(f"Comprimento do texto - Média: {text_lengths.mean():.1f}, Min: {text_lengths.min()}, Max: {text_lengths.max()}")
    
    return True

def create_improved_vectorizer():
    """Cria um vetorizador TF-IDF com configurações otimizadas."""
    
    # Lista expandida de stopwords em português
    portuguese_stopwords = [
        'de', 'a', 'o', 'que', 'e', 'do', 'da', 'em', 'um', 'uma', 'para', 'com', 'por',
        'ao', 'dos', 'das', 'no', 'na', 'nos', 'nas', 'se', 'ou', 'mas', 'como', 'foi',
        'este', 'esta', 'essa', 'esse', 'seu', 'sua', 'seus', 'suas', 'nosso', 'nossa',
        'ser', 'ter', 'fazer', 'dizer', 'ir', 'ver', 'dar', 'saber', 'ficar', 'poder',
        'ela', 'ele', 'eles', 'elas', 'você', 'vocês', 'nós', 'eu', 'tu', 'meu', 'minha'
    ]
    
    vectorizer = TfidfVectorizer(
        stop_words=portuguese_stopwords,
        max_features=5000,          # Limita número de features para evitar overfitting
        min_df=2,                   # Ignora termos que aparecem em menos de 2 documentos
        max_df=0.8,                 # Ignora termos muito comuns (mais de 80% dos docs)
        ngram_range=(1, 2),         # Inclui unigramas e bigramas
        lowercase=True,             # Converte para minúsculas
        strip_accents='unicode',    # Remove acentos
        token_pattern=r'\b[a-zA-Z]{2,}\b'  # Apenas palavras com 2+ caracteres
    )
    
    return vectorizer

def train_and_evaluate_model(X_train, X_test, y_train, y_test):
    """Treina o modelo e avalia sua performance."""
    
    logger.info("=== Treinamento do Modelo ===")
    
    # Configurar modelo com parâmetros otimizados
    classifier = LogisticRegression(
        random_state=42,
        max_iter=1000,              # Aumenta iterações para convergência
        C=1.0,                      # Regularização (pode ser ajustada)
        class_weight='balanced'     # Lida com classes desbalanceadas
    )
    
    # Treinar modelo
    classifier.fit(X_train, y_train)
    logger.info("Treinamento concluído.")
    
    # Validação cruzada no conjunto de treino
    cv_scores = cross_val_score(classifier, X_train, y_train, cv=5, scoring='accuracy')
    logger.info(f"Validação cruzada (5-fold) - Acurácia média: {cv_scores.mean():.3f} (±{cv_scores.std()*2:.3f})")
    
    # Avaliação no conjunto de teste
    y_pred = classifier.predict(X_test)
    test_accuracy = accuracy_score(y_test, y_pred)
    
    logger.info("=== Resultados no Conjunto de Teste ===")
    logger.info(f"Acurácia: {test_accuracy:.3f}")
    logger.info("\nRelatório de Classificação:")
    logger.info(f"\n{classification_report(y_test, y_pred)}")
    
    # Matriz de confusão
    cm = confusion_matrix(y_test, y_pred)
    logger.info(f"Matriz de Confusão:\n{cm}")
    
    return classifier

def save_models(vectorizer, classifier, base_dir):
    """Salva o vetorizador e modelo de forma segura."""
    
    vectorizer_path = os.path.join(base_dir, 'tfidf_vectorizer.pkl')
    model_path = os.path.join(base_dir, 'fatura_classifier_model.pkl')
    
    try:
        logger.info(f"Salvando o vetorizador em: {vectorizer_path}")
        joblib.dump(vectorizer, vectorizer_path)
        
        logger.info(f"Salvando o modelo em: {model_path}")
        joblib.dump(classifier, model_path)
        
        logger.info("Arquivos do modelo salvos com sucesso!")
        return True
        
    except Exception as e:
        logger.error(f"Erro ao salvar os modelos: {e}")
        return False

def test_saved_model(df, base_dir):
    """Testa o modelo salvo com uma amostra do dataset."""
    
    try:
        # Carregar modelos salvos
        vectorizer_path = os.path.join(base_dir, 'tfidf_vectorizer.pkl')
        model_path = os.path.join(base_dir, 'fatura_classifier_model.pkl')
        
        loaded_vectorizer = joblib.load(vectorizer_path)
        loaded_model = joblib.load(model_path)
        
        # Testar com algumas amostras
        logger.info("\n=== Teste do Modelo Salvo ===")
        for category in df['category'].unique():
            category_samples = df[df['category'] == category]
            if len(category_samples) > 0:
                sample = category_samples.sample(1).iloc[0]
                sample_text = sample['text']
                
                # Fazer predição
                text_features = loaded_vectorizer.transform([sample_text])
                prediction = loaded_model.predict(text_features)
                prediction_proba = loaded_model.predict_proba(text_features)
                confidence = max(prediction_proba[0])
                
                # Mostrar resultado
                text_preview = sample_text[:100].replace('\n', ' ')
                logger.info(f"Categoria Real: {sample['category']}")
                logger.info(f"Predição: {prediction[0]} (confiança: {confidence:.3f})")
                logger.info(f"Texto: '{text_preview}...'")
                logger.info("-" * 50)
        
        return True
        
    except Exception as e:
        logger.error(f"Erro no teste do modelo salvo: {e}")
        return False

def main():
    """Função principal do script de treinamento."""
    
    # 1. Carregamento dos Dados
    dataset_path = os.path.join(os.path.dirname(__file__), 'faturas_dataset.csv')
    
    try:
        logger.info(f"Carregando dataset de: {dataset_path}")
        df = pd.read_csv(dataset_path, encoding='utf-8')
    except FileNotFoundError:
        logger.error(f"Erro: Arquivo de dataset '{dataset_path}' não encontrado.")
        logger.error("Verifique se o arquivo 'faturas_dataset.csv' existe no mesmo diretório.")
        return False
    except Exception as e:
        logger.error(f"Erro ao carregar dataset: {e}")
        return False

    # Limpeza inicial dos dados
    original_size = len(df)
    df.dropna(subset=['text', 'category'], inplace=True)
    
    if len(df) < original_size:
        logger.warning(f"Removidas {original_size - len(df)} linhas com valores nulos")

    if df.empty:
        logger.error("O dataset está vazio após limpeza. Verifique o arquivo CSV e o processo de extração.")
        return False

    # Validar dataset
    validate_dataset(df)
    
    # Verificar se há amostras suficientes para divisão train/test
    if len(df) < 10:
        logger.error("Dataset muito pequeno (< 10 amostras). Colete mais dados antes de treinar.")
        return False

    # 2. Divisão dos dados
    logger.info("\n=== Divisão dos Dados ===")
    try:
        # Usar estratificação para manter proporção das classes
        X_train, X_test, y_train, y_test = train_test_split(
            df['text'], 
            df['category'], 
            test_size=0.2, 
            random_state=42, 
            stratify=df['category']
        )
        logger.info(f"Treino: {len(X_train)} amostras")
        logger.info(f"Teste: {len(X_test)} amostras")
        
    except ValueError as e:
        # Fallback caso estratificação falhe (ex: classe com apenas 1 amostra)
        logger.warning(f"Não foi possível usar estratificação: {e}")
        X_train, X_test, y_train, y_test = train_test_split(
            df['text'], 
            df['category'], 
            test_size=0.2, 
            random_state=42
        )

    # 3. Preparação e Vetorização dos Dados
    logger.info("\n=== Vetorização dos Dados ===")
    vectorizer = create_improved_vectorizer()
    
    try:
        X_train_vec = vectorizer.fit_transform(X_train)
        X_test_vec = vectorizer.transform(X_test)
        logger.info(f"Vetorização concluída. Shape treino: {X_train_vec.shape}")
        
    except Exception as e:
        logger.error(f"Erro na vetorização: {e}")
        return False

    # 4. Treinamento e Avaliação do Modelo
    classifier = train_and_evaluate_model(X_train_vec, X_test_vec, y_train, y_test)

    # 5. Salvamento dos Modelos
    base_dir = os.path.dirname(__file__)
    if not save_models(vectorizer, classifier, base_dir):
        return False

    # 6. Teste do Modelo Salvo
    test_saved_model(df, base_dir)
    
    logger.info("\n=== Treinamento Concluído com Sucesso! ===")
    return True

if __name__ == '__main__':
    success = main()
    if not success:
        exit(1)