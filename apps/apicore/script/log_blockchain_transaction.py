# SPDX-License-Identifier: MIT
# @author wangbingxuan wangbingxuan@tapatalk.com
# loop log transaction in blockchain that match our db address && insect to out db wait block confirm transaction

import json
import sys
import pymysql
import configparser
import time
import datetime
import logging
import os

from os.path import dirname, abspath
from web3 import Web3

# init config
path = dirname(abspath(__file__)) + '/../EvfGroupCoin.json'
with open(path) as contract_abi_file:
    contract_abi = json.load(contract_abi_file)

config=configparser.ConfigParser()
config_path = dirname(abspath(__file__)) + '/../.env'
config.read(config_path)

leng = len(sys.argv)
if leng >= 1:
    token_id = sys.argv[1]
else:
    exit()

log_path = config.get("DEFAULT", "PY_LOG_PATH")
(filename, extension) = os.path.splitext(os.path.basename(__file__))

log_filename = log_path + 'script/log_blockchain_transaction_' + str(token_id)+'/log_'+ str(datetime.date.today())  +'.txt'
file_dir = os.path.split(log_filename)[0]
if not os.path.isdir(file_dir):
    os.makedirs(file_dir)
# if not os.path.exists(filename ):
#     os.system(r'touch %s' % filename)

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
sql = "select id,contract_address,abi from erc20_token where allow_import = 1 and id = %s"
cursor.execute(sql,(token_id))
token = cursor.fetchone()
abi_str = token[2]
contract_address = token[1]
contract_address = web3.toChecksumAddress(contract_address)
# init contract filter
contract = web3.eth.contract(address=contract_address, abi=json.loads(abi_str))
transfer_filter = contract.events.Transfer.createFilter(fromBlock='latest')
connection.commit()
cursor.close()
connection.close()

while True:
    log_filename = log_path + 'script/log_blockchain_transaction_' + str(token_id) + '/log_' + str(datetime.date.today()) + '.txt'
    logging.basicConfig(filename=log_filename, level=logging.INFO, format='%(asctime)s %(message)s', datefmt='%H:%M:%S')

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
                for row in address_ary:
                    if row[1].lower() == transfer_log.args.to.lower():
                        logging.info('one address match:' + str(row[1]) )
                        sql = "INSERT INTO erc20token_import_log (`transactionHash`,`token_id`,`from`,`to`,`value`,`blockNumber`,`status`,`created_at`) VALUES (%s,%s,%s,%s,%s,%s,%s,%s)"
                        cursor.execute(sql, (transfer_log.transactionHash.hex(),
                                             token_id,
                                             transfer_log.args['from'],
                                             transfer_log.args['to'],
                                             transfer_log.args['value'],
                                             transfer_log['blockNumber'],
                                             1,
                                             datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
                                             ))
                        connection.commit()
        cursor.close()
        connection.close()
    logging.info('loop')
    time.sleep(15)
