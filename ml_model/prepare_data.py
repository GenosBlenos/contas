import os
import pandas as pd
import fitz  # PyMuPDF

def extract_text_from_pdf(pdf_path):
    """Extrai todo o texto de um arquivo PDF."""
    try:
        doc = fitz.open(pdf_path)
        text = ""
        for page in doc:
            text += page.get_text("text")
        doc.close()
        # Limpeza básica: remove linhas em branco excessivas
        cleaned_text = "\n".join([line for line in text.split('\n') if line.strip()])
        return cleaned_text
    except Exception as e:
        print(f"Erro ao ler o PDF {pdf_path}: {e}")
        return None

def create_training_dataset(base_dir, output_csv):
    """Varre as pastas de categorias, extrai texto dos PDFs e cria um dataset em CSV."""
    data = []
    # As categorias são os nomes das subpastas
    categories = [d for d in os.listdir(base_dir) if os.path.isdir(os.path.join(base_dir, d))]
    
    for category in categories:
        category_path = os.path.join(base_dir, category)
        print(f"Processando categoria: {category}...")
        for filename in os.listdir(category_path):
            if filename.lower().endswith('.pdf'):
                pdf_path = os.path.join(category_path, filename)
                text = extract_text_from_pdf(pdf_path)
                if text:
                    data.append({'text': text, 'category': category})
    
    if not data:
        print("Nenhum dado foi extraído. Verifique a estrutura de pastas e os arquivos PDF.")
        return

    df = pd.DataFrame(data)
    df.to_csv(output_csv, index=False, encoding='utf-8')
    print(f"\nDataset criado com sucesso e salvo em: {output_csv}")
    print(f"Total de amostras: {len(df)}")
    print("Distribuição por categoria:")
    print(df['category'].value_counts())

if __name__ == '__main__':
    training_data_dir = os.path.join(os.path.dirname(__file__), 'training_data')
    output_csv_path = os.path.join(os.path.dirname(__file__), 'faturas_dataset.csv')
    
    if not os.path.exists(training_data_dir):
        os.makedirs(training_data_dir)
        print(f"Diretório '{training_data_dir}' criado. Adicione subpastas (ex: 'agua', 'energia') com seus PDFs e execute o script novamente.")
    else:
        create_training_dataset(training_data_dir, output_csv_path)