import json
import sys
import pymysql
import configparser
import time
import datetime

from os.path import dirname, abspath

while True:
    print(datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S"))
    time.sleep(3)


config=configparser.ConfigParser()
config_path = dirname(abspath(__file__)) + '/../.env'
config.read(config_path)

DB_HOST=config.get("DEFAULT","DB_HOST")
DB_PORT=int(config.get("DEFAULT","DB_PORT"))
DB_DATABASE=config.get("DEFAULT","DB_DATABASE")
DB_USERNAME=config.get("DEFAULT","DB_USERNAME")
DB_PASSWORD=config.get("DEFAULT","DB_PASSWORD")

connection = pymysql.connect(host=DB_HOST,
                             port=DB_PORT,
                             user=DB_USERNAME,
                             password=DB_PASSWORD,
                             db=DB_DATABASE,
                             charset='utf8')

cursor = connection.cursor()


sql = "SELECT balance FROM token_wallet WHERE user_id = 0 and token_id = %s ;"
cursor.execute( sql, (token_id) )
token = cursor.fetchone()
count = token[0]



connection.commit()
cursor.close()
connection.close()

