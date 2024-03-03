import requests
import json
import mysql.connector
from concurrent.futures import ThreadPoolExecutor
from queue import Queue
from datetime import datetime
import time

def read_crypto_list(file_path):
    try:
        with open(file_path, 'r') as file:
            return [line.strip() for line in file.readlines()]
    except IOError as e:
        print(f"Erro ao ler o arquivo de lista de criptomoedas: {e}")
        exit(1)

def save_error_to_txt(symbol):
    try:
        with open("config/logs/bybit.txt", "a") as file:
            file.write(f"Error processing data for {symbol}. JSON Exception\n")
    except IOError as e:
        print(f"Erro ao salvar o erro no arquivo: {e}")

def remove_old_prices_from_database(conn, symbol):
    sql = "DELETE FROM `Bybit` WHERE `Moeda` = %s"
    try:
        with conn.cursor() as cursor:
            cursor.execute(sql, (symbol,))
        conn.commit()
    except Exception as e:
        print(f"Erro ao remover preços antigos do banco de dados: {e}")

def save_data_to_database(conn, symbol, data_type, data):
    sql = "INSERT INTO `Bybit` (`Moeda`, `Tipo`, `Preco`, `Quantidade`, `Volume`) VALUES (%s, %s, %s, %s, %s)"
    try:
        with conn.cursor() as cursor:
            for item in data:
                try:
                    price = float(item[0])
                    quantity = float(item[1])
                    volume = price * quantity

                    if volume > 5.0:
                        cursor.execute(sql, (symbol, data_type, price, quantity, volume))
                except Exception as e:
                    print(f"Erro ao processar item: {e}")

        conn.commit()
    except Exception as e:
        print(f"Erro ao salvar dados no banco de dados: {e}")

def fetch_data(args):
    symbol, db_config = args
    url = f"https://api.bybit.com/v5/market/orderbook?category=linear&limit=10&symbol={symbol}"

    try:
        response = requests.get(url)
        response_data = response.json()

        if response.status_code == 200:
            result = response_data.get('result', {})
            bids = result.get('b', [])
            asks = result.get('a', [])

            with mysql.connector.connect(**db_config) as conn:
                remove_old_prices_from_database(conn, symbol)
                save_data_to_database(conn, symbol, "buy", bids)
                save_data_to_database(conn, symbol, "sell", asks)

            now = datetime.now()
            formatted_datetime = now.strftime("%H:%M:%S")
            print(f"[{formatted_datetime}] - [Bybit] - Dados Atualizados: {symbol}")
        else:
            print(f"Erro ao acessar a API para {symbol}. Código de resposta: {response.status_code}")

    except json.JSONDecodeError as e:
        print(f"Erro ao processar dados para {symbol}. JSON Exception: {e}")
        save_error_to_txt(symbol)
    except requests.exceptions.Timeout:
        print(f"Timeout de conexão para {symbol}. Verifique sua conexão com a internet.")
    except requests.exceptions.RequestException as e:
        print(f"Erro de conexão para {symbol}: {e}")
    except Exception as e:
        print(f"Erro desconhecido: {e}")

def main():
    crypto_list_file_path = "config/listas/bybit.txt"
    symbols = read_crypto_list(crypto_list_file_path)

    db_config = {
        'host': 'localhost',
        'user': 'root',
        'password': '',
        'database': 'Crypto',
        'port': 3306,
    }

    num_threads = 3

    while True:  # Loop infinito para execução contínua
        now = datetime.now().strftime("%H:%M:%S")
        print(f"[{now}] - [Bybit] - Iniciando...")

        with ThreadPoolExecutor(max_workers=num_threads) as executor:
            args = [(symbol, db_config) for symbol in symbols]
            executor.map(fetch_data, args)
            # No need to submit tasks individually

        # Print a message when finishing all cryptocurrencies
        now = datetime.now().strftime("%H:%M:%S")
        print(f"[{now}] - [Bybit] - Aguardando 60 segundos para reiniciar...")
        time.sleep(60)  # Aguarde 60 segundos antes de reiniciar

if __name__ == "__main__":
    main()