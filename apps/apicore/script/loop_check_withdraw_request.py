# SPDX-License-Identifier: MIT
# @author wangbingxuan wangbingxuan@tapatalk.com

import json
import sys
import pymysql
import configparser
import os
import time
import datetime
import logging

from os.path import dirname, abspath
from web3 import Web3

config=configparser.ConfigParser()
config_path = dirname(abspath(__file__)) + '/../.env'
config.read(config_path)
# py_path = config.get("DEFAULT", "PY_PATH")

# init config
path = dirname(abspath(__file__)) + '/../EvfGroupCoin.json'
with open(path) as contract_abi_file:
    contract_abi = json.load(contract_abi_file)

log_path = config.get("DEFAULT", "PY_LOG_PATH")
(filename, extension) = os.path.splitext(os.path.basename(__file__))
log_filename = log_path + 'script/'+filename+'/log_'+ str(datetime.date.today())  +'.txt'

file_dir = os.path.split(log_filename)[0]
if not os.path.isdir(file_dir):
    os.makedirs(file_dir)
if not os.path.exists(filename ):
    os.system(r'touch %s' % filename)
logging.basicConfig(filename=log_filename,level=logging.INFO, format='%(asctime)s %(message)s' ,datefmt='%H:%M:%S' )

# init web3
WS_INFURA_URL = config.get("DEFAULT", "WS_INFURA_URL")
web3 = Web3(Web3.WebsocketProvider(WS_INFURA_URL))
# init db
DB_HOST = config.get("DEFAULT", "DB_HOST")
DB_PORT = int(config.get("DEFAULT", "DB_PORT"))
DB_DATABASE = config.get("DEFAULT", "DB_DATABASE")
DB_USERNAME = config.get("DEFAULT", "DB_USERNAME")
DB_PASSWORD = config.get("DEFAULT", "DB_PASSWORD")

connection = pymysql.connect(host=DB_HOST,
                             port=DB_PORT,
                             user=DB_USERNAME,
                             password=DB_PASSWORD,
                             db=DB_DATABASE,
                             charset='utf8')
cursor = connection.cursor()

# fetch all allow import
sql = "SELECT withdraw_request.id,withdraw_request.amount,withdraw_request.token_id,withdraw_request.`to`,withdraw_request.`status`,erc20_token.`decimal`,erc20_token.contract_address,erc20_token.abi,erc20_token.allow_import FROM withdraw_request left join erc20_token on withdraw_request.token_id = erc20_token.id order by withdraw_request.id asc limit 1000"
cursor.execute(sql,(token_id))
token = cursor.fetchone()
abi_str = token[2]
contract_address = token[1]
# init contract filter
contract = web3.eth.contract(address=contract_address, abi=json.loads(abi_str))
transfer_filter = contract.events.Transfer.createFilter(fromBlock='latest')
connection.commit()
cursor.close()
connection.close()

while True:
    if web3.isConnected() == False:
        web3 = Web3(Web3.WebsocketProvider('wss://rinkeby.infura.io/ws/v3/c981cd44768347e4b0aac1451150a589'))
        # init db
        DB_HOST = config.get("DEFAULT", "DB_HOST")
        DB_PORT = int(config.get("DEFAULT", "DB_PORT"))
        DB_DATABASE = config.get("DEFAULT", "DB_DATABASE")
        DB_USERNAME = config.get("DEFAULT", "DB_USERNAME")
        DB_PASSWORD = config.get("DEFAULT", "DB_PASSWORD")

        connection = pymysql.connect(host=DB_HOST,
                                     port=DB_PORT,
                                     user=DB_USERNAME,
                                     password=DB_PASSWORD,
                                     db=DB_DATABASE,
                                     charset='utf8')
        cursor = connection.cursor()

        # fetch all allow import
        sql = "select id,contract_address,abi from erc20_token where allow_import = 1 and id = %s"
        cursor.execute(sql, (token_id))
        token = cursor.fetchone()
        abi_str = token[2]
        contract_address = token[1]
        # init contract filter
        contract = web3.eth.contract(address=contract_address, abi=json.loads(abi_str))
        transfer_filter = contract.events.Transfer.createFilter(fromBlock='latest')
        connection.commit()
        cursor.close()
        connection.close()
        
    # loop monitor new transaction
    transfer = transfer_filter.get_new_entries()
    if transfer:
        logging.info('new transfer')
        DB_HOST = config.get("DEFAULT", "DB_HOST")
        DB_PORT = int(config.get("DEFAULT", "DB_PORT"))
        DB_DATABASE = config.get("DEFAULT", "DB_DATABASE")
        DB_USERNAME = config.get("DEFAULT", "DB_USERNAME")
        DB_PASSWORD = config.get("DEFAULT", "DB_PASSWORD")

        connection = pymysql.connect(host=DB_HOST,
                                     port=DB_PORT,
                                     user=DB_USERNAME,
                                     password=DB_PASSWORD,
                                     db=DB_DATABASE,
                                     charset='utf8')
        cursor = connection.cursor()

        for transfer_log in transfer:
            count = 1000
            while count == 1000:

                sql = "select id, public_key from group_erc20token where token_id = %s limit 1000"
                cursor.execute(sql, (token_id))
                address_ary = cursor.fetchall()
                count = cursor.rowcount
                if cursor.rowcount == 0:
                    continue

                logging.info( str(cursor.rowcount) + ' address match')
                for row in address_ary:

                    if row[1] == transfer_log.args._to:
                        sql = "INSERT INTO erc20token_import_log (`transactionHash`,`token_id`,`from`,`to`,`value`,`blockNumber`,`status`,`created_at`) VALUES (%s,%s,%s,%s,%s,%s,%s,%s)"

                        cursor.execute(sql, (transfer_log.transactionHash.hex(),
                                             token_id,
                                             transfer_log.args._from,
                                             transfer_log.args._to,
                                             transfer_log.args._value,
                                             transfer_log.blockNumber,
                                             1,
                                             datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
                                             ))
                        connection.commit()
        cursor.close()
        connection.close()
    logging.info('sleep')
    time.sleep(30)
