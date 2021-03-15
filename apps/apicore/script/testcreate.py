# SPDX-License-Identifier: MIT
# @author wangbingxuan wangbingxuan@tapatalk.com
# loop check transaction in blockchain that create contract log

import json
import sys
import pymysql
import configparser
import time
import datetime
import logging

from os.path import dirname, abspath
from web3 import Web3
from web3.auto import w3

# init config
path = dirname(abspath(__file__)) + '/../EvfGroupCoin.json'
with open(path) as contract_abi_file:
    contract_abi = json.load(contract_abi_file)

config=configparser.ConfigParser()
config_path = dirname(abspath(__file__)) + '/../.env'
config.read(config_path)

# init log
log_path = config.get("DEFAULT", "PY_LOG_PATH")
log_filename = log_path + 'script/check_blockchain_create_transaction.txt'
logging.basicConfig(filename=log_filename,level=logging.INFO, format='%(asctime)s %(message)s' ,datefmt='%H:%M:%S' )

# init db
DB_HOST = config.get("DEFAULT", "DB_HOST")
DB_PORT = int(config.get("DEFAULT", "DB_PORT"))
DB_DATABASE = config.get("DEFAULT", "DB_DATABASE")
DB_USERNAME = config.get("DEFAULT", "DB_USERNAME")
DB_PASSWORD = config.get("DEFAULT", "DB_PASSWORD")
# web3 = Web3(Web3.WebsocketProvider('wss://rinkeby.infura.io/ws/v3/c981cd44768347e4b0aac1451150a589'))
# init web3
web3 = Web3(Web3.HTTPProvider('https://rinkeby.infura.io/v3/c981cd44768347e4b0aac1451150a589'))

connection = pymysql.connect(host=DB_HOST,
                             port=DB_PORT,
                             user=DB_USERNAME,
                             password=DB_PASSWORD,
                             db=DB_DATABASE,
                             charset='utf8')
cursor = connection.cursor()
count = 100
offset = 0
while count == 100:
    sql = "select id,transaction from erc20_token where allow_import = 0 and contract_address is Null limit " + str(count) + ' OFFSET ' + str(offset)
    cursor.execute(sql)
    token = cursor.fetchall()

    if cursor.rowcount == 0:
        cursor.close()
        connection.close()
        logging.info('no row')
        exit()
    count = cursor.rowcount
    offset = offset + count

    for tokeninfo in token:
        sql = "select id,transactionHash from erc20token_import_log where token_id = %s and status = 1 "
        cursor.execute(sql, tokeninfo[0])
        tokenlog = cursor.fetchone()

        if cursor.rowcount == 0:
            continue
    
        contractAddress = web3.eth.getTransactionReceipt(tokenlog[1]).contractAddress
        blockNumber = web3.eth.blockNumber - 2
        trx = web3.eth.getTransaction(tokenlog[1])
        exit()
        try:
            # get contractAddress from transactionHash
            contractAddress = web3.eth.getTransactionReceipt(tokenlog[1]).contractAddress
            blockNumber = web3.eth.blockNumber - 2
            # get transaction from transactionHash
            trx = web3.eth.getTransaction(tokenlog[1])
        except:
            logging.info("error id: %s" % (tokeninfo[0]))
            sql = "UPDATE erc20token_import_log SET `status` = %s where id = %s"
            cursor.execute(sql, (0, tokenlog[0]))
        else:
            if blockNumber >= trx.blockNumber:
                sql = "UPDATE erc20token_import_log SET `status` = %s where id = %s"
                cursor.execute(sql, (2, tokenlog[0]))
                sql = "UPDATE erc20_token SET `contract_address` = %s where id = %s"
                cursor.execute(sql, (contractAddress, tokeninfo[0]))
                sql = "UPDATE group_erc20token SET `status` = 3 where token_id = %s"
                cursor.execute(sql, (tokeninfo[0]))
                connection.commit()
                logging.info("update id: %s" % (tokeninfo[0]))
            else:
                logging.info("wait update id:%s" % (tokeninfo[0]))

cursor.close()
connection.close()
logging.info('leep')

