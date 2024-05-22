import logging
import csv
import re
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity
import mysql.connector

# Настройка логирования
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')

# Параметры подключения к базе данных
db_config = {
    'host': 'localhost',
    'user': 'root',
    'password': 'Buratinovova02',
    'database': 'bd',
    'charset': 'latin1'
}

# Путь к файлу CSV для сохранения результатов
csv_file_path = 'similar_definitions.csv'

def get_database_connection(config):
    """Создание подключения к базе данных."""
    try:
        return mysql.connector.connect(**config)
    except mysql.connector.Error as err:
        logging.error(f"Ошибка подключения к базе данных: {err}")
        return None

def fetch_words(cursor, limit=200):
    """Получение списка слов из базы данных с ограничением."""
    try:
        query = "SELECT page_title FROM page LIMIT %s;"
        cursor.execute(query, (limit,))
        return cursor.fetchall()
    except mysql.connector.Error as err:
        logging.error(f"Ошибка при выполнении запроса fetch_words: {err}")
        return []

def fetch_definitions(cursor, word):
    """Получение списка определений слова из базы данных."""
    try:
        query = """
            SELECT DISTINCT wiki_text.text 
            FROM page 
            JOIN lang_pos ON page.id = lang_pos.page_id 
            JOIN meaning ON lang_pos.id = meaning.lang_pos_id 
            JOIN wiki_text ON meaning.wiki_text_id = wiki_text.id 
            WHERE page.page_title = %s;
        """
        cursor.execute(query, (word,))
        return cursor.fetchall()
    except mysql.connector.Error as err:
        logging.error(f"Ошибка при выполнении запроса fetch_definitions: {err}")
        return []

def fetch_synonyms(cursor, word):
    """Получение списка синонимов для данного слова из базы данных."""
    try:
        query = """
            SELECT wiki_text.text 
            FROM meaning
            JOIN relation ON meaning.id = relation.meaning_id
            JOIN wiki_text ON relation.wiki_text_id = wiki_text.id
            JOIN lang_pos ON meaning.lang_pos_id = lang_pos.id
            JOIN page ON lang_pos.page_id = page.id
            WHERE relation.relation_type_id = 9 
            AND page.page_title = %s;
        """
        cursor.execute(query, (word,))
        return cursor.fetchall()
    except mysql.connector.Error as err:
        logging.error(f"Ошибка при выполнении запроса fetch_synonyms: {err}")
        return []

def preprocess_text(text):
    """Удаление знаков препинания и приведение текста к нижнему регистру."""
    text = re.sub(r'[^\w\s]', '', text)  # Удаление знаков препинания
    text = text.lower()  # Приведение к нижнему регистру
    return text

def find_similar_definitions_tfidf(definitions, threshold=0.8):
    """Нахождение пар определений с наиболее высокой схожестью на основе TF-IDF."""
    # Преобразуем определения в список строк и предварительно обрабатываем их
    definition_texts = [preprocess_text(definition[0]) for definition in definitions]
    
    # Создаем TF-IDF векторизатор и преобразуем определения в TF-IDF матрицу
    vectorizer = TfidfVectorizer()
    tfidf_matrix = vectorizer.fit_transform(definition_texts)
    
    # Вычисляем косинусное сходство между всеми парами определений
    cosine_similarities = cosine_similarity(tfidf_matrix, tfidf_matrix)
    
    similar_pairs = set()  # Используем множество для автоматического удаления дубликатов
    
    for i in range(len(definition_texts)):
        for j in range(i + 1, len(definition_texts)):  # j начинается с i + 1, чтобы избежать повторного сравнения и сравнения с самим собой
            similarity = cosine_similarities[i][j]
            if similarity > threshold and similarity < 1.0:  # Исключаем пары с идентичными определениями (схожесть 1.0)
                pair = tuple(sorted([definition_texts[i], definition_texts[j]]))  # Сортируем пары для удаления дубликатов
                similar_pairs.add((pair[0], pair[1], similarity))
    
    return similar_pairs

def main():
    # Подключение к базе данных
    conn = get_database_connection(db_config)
    if conn is None:
        return

    try:
        with conn.cursor() as cursor:
            # Получение списка слов с ограничением
            words = fetch_words(cursor, limit=200)

            if not words:
                logging.warning("Не удалось получить слова для обработки.")
                return
            
            # Список для хранения всех определений
            all_definitions = []
            
            # Получение определений и синонимов для каждого слова
            for word in words:
                word = word[0]  # Преобразуем кортеж в строку
                definitions = fetch_definitions(cursor, word)
                
                if not definitions:
                    logging.info(f"Нет определений для слова: {word}. Переход к следующему слову.")
                    continue
                
                synonyms = fetch_synonyms(cursor, word)
                
                if not synonyms:
                    logging.info(f"Нет синонимов для слова: {word}. Пропуск слова.")
                    continue
                
                # Добавление определений слова
                decoded_definitions = [(definition[0].encode('latin1').decode('utf-8'),) for definition in definitions]
                all_definitions.extend(decoded_definitions)
                
                valid_synonym_found = False
                
                for synonym in synonyms:
                    synonym_definitions = fetch_definitions(cursor, synonym[0])
                    if synonym_definitions:
                        valid_synonym_found = True
                        decoded_synonym_definitions = [(definition[0].encode('latin1').decode('utf-8'),) for definition in synonym_definitions]
                        all_definitions.extend(decoded_synonym_definitions)
                    else:
                        logging.info(f"Нет определений для синонима: {synonym[0]}. Пропуск синонима.")
                
                if not valid_synonym_found:
                    logging.info(f"Все синонимы для слова: {word} не имеют определений. Пропуск слова.")
                    continue
            
            if not all_definitions:
                logging.warning("Не удалось получить определения для обработки.")
                return

            # Нахождение пар определений с наиболее высокой схожестью
            similar_pairs = find_similar_definitions_tfidf(all_definitions, threshold=0.8)
            
            # Открытие CSV файла для записи
            with open(csv_file_path, 'w', newline='', encoding='utf-8') as csvfile:
                csv_writer = csv.writer(csvfile)
                csv_writer.writerow(['Definition1', 'Definition2', 'Similarity'])

                # Запись результатов в CSV файл
                for definition1, definition2, similarity in similar_pairs:
                    csv_writer.writerow([definition1, definition2, similarity])
                    logging.info(f"Записана пара определений с высокой схожестью: {definition1} и {definition2}")

        logging.info(f"Пары определений с высокой схожестью успешно записаны в файл: {csv_file_path}")

    except mysql.connector.Error as err:
        logging.error(f"Ошибка при работе с базой данных: {err}")

    finally:
        # Закрытие подключения к базе данных
        if conn.is_connected():
            conn.close()
            logging.info("Соединение с базой данных закрыто.")

if __name__ == "__main__":
    main()
