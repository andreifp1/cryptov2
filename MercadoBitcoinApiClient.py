import requests
import json
import pymysql
from time import sleep
from datetime import datetime

EXCHANGE_RATE_API_URL = "https://api.exchangerate-api.com/v4/latest/USD"


def read_symbols_from_file(file_path):
    symbols_list = []
    with open(file_path, "r") as file:
        for line in file:
            if not line.strip().startswith("#"):
                symbols_list.append(line.strip())
    return symbols_list


def get_exchange_rate_brl_to_usd():
    try:
        response = requests.get(EXCHANGE_RATE_API_URL)
        if response.status_code == 200:
            data = response.json()
            return data["rates"]["BRL"]
        else:
            print("Error fetching exchange rate. Status code:", response.status_code)
    except Exception as e:
        print("Error fetching exchange rate:", str(e))
    return 0.0


def remove_old_prices_from_database(conn, coin):
    sql = "DELETE FROM `Mercado Bitcoin` WHERE `Moeda` = %s"
    try:
        with conn.cursor() as cursor:
            cursor.execute(sql, (coin,))
            conn.commit()
    except Exception as e:
        print("Error removing old prices from database:", str(e))


def save_data_to_database(conn, coin, type, data, exchange_rate_brl_to_usd):
    sql = "INSERT INTO `Mercado Bitcoin` (`Moeda`, `Tipo`, `PrecoBRL`, `Preco`, `Quantidade`, `Volume`) VALUES (%s, %s, %s, %s, %s, %s)"

    try:
        with conn.cursor() as cursor:
            for i in range(min(10, len(data))):
                try:
                    item = data[i]
                    price_brl = item[0]
                    quantity = item[1]

                    price_usd = price_brl / exchange_rate_brl_to_usd
                    volume = price_usd * quantity

                    if volume > 5.0:
                        cursor.execute(
                            sql, (coin, type, price_brl, price_usd, quantity, volume)
                        )
                except Exception as e:
                    print("Error processing data:", str(e))
            conn.commit()
    except Exception as e:
        print("Error saving data to database:", str(e))


def main():
    symbols_file_path = "config/listas/mercadobitcoin.txt"
    coins = read_symbols_from_file(symbols_file_path)

    method = "orderbook"

    db_host = "localhost"
    db_user = "root"
    db_password = ""
    db_name = "Crypto"

    try:
        conn = pymysql.connect(
            host=db_host, user=db_user, password=db_password, database=db_name
        )

        exchange_rate_brl_to_usd = get_exchange_rate_brl_to_usd()

        while True:  # Run continuously
            for idx, coin in enumerate(coins):
                coin_with_usdt = coin + "USDT"
                url = f"https://www.mercadobitcoin.net/api/{coin}/{method}/"

                try:
                    response = requests.get(url)
                    now = datetime.now()  # Move 'now' declaration here
                    formatted_datetime = now.strftime("%H:%M:%S")
                    if response.status_code == 200:
                        data = response.json()
                        bids = data["bids"]
                        asks = data["asks"]

                        if idx == 0:
                            print(f"[{formatted_datetime}] - [Mercado Bitcoin] - Iniciando... ")

                        remove_old_prices_from_database(conn, coin_with_usdt)
                        save_data_to_database(
                            conn, coin_with_usdt, "buy", bids, exchange_rate_brl_to_usd
                        )
                        save_data_to_database(
                            conn, coin_with_usdt, "sell", asks, exchange_rate_brl_to_usd
                        )

                        print(
                            f"[{formatted_datetime}] - [Mercado Bitcoin] - Dados Atualizados: {coin_with_usdt}"
                        )

                        if idx == len(coins) - 1:
                            print(f"[{formatted_datetime}] - [Mercado Bitcoin] - Aguardando 60 segundos para reiniciar...")

                    else:
                        print(
                            f"[{formatted_datetime}] - Error accessing API for {coin_with_usdt}. Status code: {response.status_code}"
                        )
                except json.JSONDecodeError as e:
                    print(f"[{formatted_datetime}] - Error processing data for {coin_with_usdt}. JSON Exception: {e}")
                except requests.exceptions.Timeout:
                    print(f"[{formatted_datetime}] - Connection timeout for {coin_with_usdt}. Please check your internet connection.")
                except requests.exceptions.ConnectionError:
                    print(f"[{formatted_datetime}] - Connection error for {coin_with_usdt}. Unable to connect to Mercado Bitcoin API.")
                except requests.exceptions.RequestException as e:
                    print(f"[{formatted_datetime}] - Request error for {coin_with_usdt}: {e}")
                except Exception as e:
                    print(f"[{formatted_datetime}] - Unexpected error for {coin_with_usdt}: {e}")

            sleep(60)  # Sleep for 60 seconds

    except pymysql.MySQLError as e:
        print(f"[{formatted_datetime}] - MySQL Error:", str(e))
    except Exception as e:
        print(f"[{formatted_datetime}] - Unexpected error:", str(e))
    finally:
        if conn:
            conn.close()

if __name__ == "__main__":
    main()

