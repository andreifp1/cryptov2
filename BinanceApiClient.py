import requests
import json
import time
from concurrent.futures import ThreadPoolExecutor
from datetime import datetime
import mysql.connector
from mysql.connector import errorcode

def save_error_to_txt(symbol, error_message):
    try:
        error_file_path = "config/logs/binance.txt"
        with open(error_file_path, "a") as error_file:
            error_file.write(f"[{datetime.now()}] Symbol: {symbol} - Error: {error_message}\n")
    except Exception as e:
        print(f"Error saving error to file: {e}")

def get_file_path(file_name):
    try:
        with open(file_name, "rb") as stream:
            temp_file = "temp.txt"
            with open(temp_file, "wb") as temp:
                temp.write(stream.read())
            return temp_file
    except Exception as e:
        raise RuntimeError(f"Error reading file: {e}")

def read_symbols_from_file(file_path):
    try:
        with open(file_path, "r") as file:
            return file.read().splitlines()
    except Exception as e:
        raise RuntimeError("Error reading symbols file")

def save_data_to_database(db_url, db_user, db_password, symbol, type, data):
    delete_sql = "DELETE FROM `Binance` WHERE `Moeda` = %s AND `Tipo` = %s"
    insert_sql = "INSERT INTO `Binance` (`Moeda`, `Tipo`, `Preco`, `Quantidade`, `Volume`) VALUES (%s, %s, %s, %s, %s)"

    try:
        conn = mysql.connector.connect(user=db_user, password=db_password, host='localhost', database='Crypto')
        with conn.cursor() as cursor:
            cursor.execute(delete_sql, (symbol, type))
            for item in data:
                try:
                    price = float(item[0])
                    quantity = float(item[1])
                    volume = price * quantity

                    if volume > 5.0:
                        cursor.execute(insert_sql, (symbol, type, price, quantity, volume))
                except Exception as e:
                    print(e)

        conn.commit()
    except mysql.connector.Error as err:
        print(f"Error: {err}")
    finally:
        conn.close()

def fetch_data(symbol):
    url = f"https://api.binance.com/api/v3/depth?limit=10&symbol={symbol}"

    try:
        response = requests.get(url)
        if response.status_code == 200:
            data = response.json()

            bids = data["bids"]
            asks = data["asks"]

            save_data_to_database(db_url, db_user, db_password, symbol, "buy", bids)
            save_data_to_database(db_url, db_user, db_password, symbol, "sell", asks)

            now = datetime.now().strftime("%H:%M:%S")
            print(f"[{now}] - [Binance] - Dados Atualizados: {symbol}")
        else:
            print(f"Error accessing API for {symbol}. Response code: {response.status_code}")
            save_error_to_txt(symbol, f"API Error - Response code: {response.status_code}")
    except json.JSONDecodeError as e:
        print(f"Error processing data for {symbol}. JSON Exception: {e}")
        save_error_to_txt(symbol, f"JSON Exception: {e}")
    except requests.exceptions.Timeout as e:
        print(f"Connection timeout for {symbol}. Please check your internet connection.")
        save_error_to_txt(symbol, f"Connection timeout: {e}")
    except requests.exceptions.RequestException as e:
        print(f"Connection error for {symbol}. Unable to connect to Binance API.")
        save_error_to_txt(symbol, f"Connection error: {e}")
    except Exception as e:
        print(f"Unexpected error: {e}")
        save_error_to_txt(symbol, f"Unexpected error: {e}")

if __name__ == "__main__":
    symbols_file_path = get_file_path("config/listas/binance.txt")
    symbols = read_symbols_from_file(symbols_file_path)

    num_threads = 3

    db_url = "localhost"
    db_user = "root"
    db_password = ""

    while True:
        with ThreadPoolExecutor(max_workers=num_threads) as executor:
            now = datetime.now().strftime("%H:%M:%S")
            print(f"[{now}] - [Binance] - Iniciando...")

            # Process each symbol individually
            for symbol in symbols:
                executor.submit(fetch_data, symbol)

            # Wait for all threads to finish
            executor.shutdown(wait=True)

            # Print a message when finishing all cryptocurrencies
            now = datetime.now().strftime("%H:%M:%S")
            print(f"[{now}] - [Binance] - Aguardando 60 segundos para reiniciar...")

            time.sleep(60)