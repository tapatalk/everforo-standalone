# SPDX-License-Identifier: MIT
# @author wangbingxuan wangbingxuan@tapatalk.com
# file for create import erc20token in blockchain
import json
import sys
import pymysql
import configparser
import time

from os.path import dirname, abspath
from web3 import Web3
from web3.auto import w3

# init config
config=configparser.ConfigParser()
config_path = dirname(abspath(__file__)) + '/../.env'
config.read(config_path)

leng = len(sys.argv)
if leng >= 1:
    group_token_id = sys.argv[1]
else:
    exit()

# init db
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

sql = "select id from group_erc20token where id = %s and is_import = 1"
cursor.execute( sql, (group_token_id) )
token = cursor.fetchone()
group_erc20token_id = token[0]

# create local account for user to import erc20token
acct = w3.eth.account.create()
sql = "UPDATE group_erc20token SET private_key = %s , public_key = %s, status = 4   WHERE id = %s ;"
cursor.execute( sql, (acct.privateKey.hex(), acct.address, group_token_id) )

connection.commit()
cursor.close()
connection.close()
