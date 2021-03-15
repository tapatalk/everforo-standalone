# SPDX-License-Identifier: MIT
# @author wangbingxuan wangbingxuan@tapatalk.com
# file for check erc20token importing transaction in blockchain
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
from web3.auto import w3


path = dirname(abspath(__file__)) + '/../EvfGroupCoin.json'
with open(path) as contract_abi_file:
    contract_abi = json.load(contract_abi_file)

config=configparser.ConfigParser()
config_path = dirname(abspath(__file__)) + '/../.env'
config.read(config_path)


log_path = config.get("DEFAULT", "PY_LOG_PATH")
(filename, extension) = os.path.splitext(os.path.basename(__file__))
log_filename = log_path + 'script/'+filename+'/log_'+ str(datetime.date.today())  +'.txt'

file_dir = os.path.split(log_filename)[0]
if not os.path.isdir(file_dir):
    os.makedirs(file_dir)
if not os.path.exists(filename ):
    os.system(r'touch %s' % filename)

logging.basicConfig(filename=log_filename,level=logging.INFO, format='%(asctime)s %(message)s' ,datefmt='%H:%M:%S' )


DB_HOST = config.get("DEFAULT", "DB_HOST")
DB_PORT = int(config.get("DEFAULT", "DB_PORT"))
DB_DATABASE = config.get("DEFAULT", "DB_DATABASE")
DB_USERNAME = config.get("DEFAULT", "DB_USERNAME")
DB_PASSWORD = config.get("DEFAULT", "DB_PASSWORD")

WS_INFURA_URL = config.get("DEFAULT", "WS_INFURA_URL")
web3 = Web3(Web3.WebsocketProvider(WS_INFURA_URL))
# web3 = Web3(Web3.HTTPProvider('https://rinkeby.infura.io/v3/c981cd44768347e4b0aac1451150a589'))


connection = pymysql.connect(host=DB_HOST,
                             port=DB_PORT,
                             user=DB_USERNAME,
                             password=DB_PASSWORD,
                             db=DB_DATABASE,
                             charset='utf8')
cursor = connection.cursor()

# fetch all allow import token list
sql = "select id, contract_address from erc20_token where allow_import = 1"
cursor.execute(sql)
token = cursor.fetchall()

# verify blockNumber
blockNumber = web3.eth.blockNumber - 10

for tokeninfo in token:
    contractAddress = tokeninfo[1]

    # contractAddress = "0x5B34E32c14771619B4BC73dAEE72e9047BdF2655"
    # tokenContract = web3.eth.contract(abi=contract_abi['abi'], bytecode=contractAddress)
    if blockNumber < 0:
        logging.info('no block')
        continue

    # select all transaction that address match our everforo account in our db
    sql = "select id,transactionHash,value,status,blockNumber from erc20token_import_log where token_id = %s and status = 1 and blockNumber <= %s LIMIT 1000"
    cursor.execute(sql, (tokeninfo[0], blockNumber ))
    if cursor.rowcount == 0:
        logging.info('no raw')
        continue
    log = cursor.fetchall()

    for transferlog in log:
        # get transaction from transactionHash
        trx = web3.eth.getTransaction(transferlog[1])
        trx_blockNumber = trx.blockNumber

        try:
            trx_blockNumber = trx.blockNumber
        except:
            # error
            logging.info('no blockNumber')
            status = 0
        else:
            # check the blocknumber
            if trx_blockNumber > 0 and trx_blockNumber == transferlog[4]:
                status = 2
            # block = null or block = 0
            else :
                status = 0
        logging.info('update log id: %s', transferlog[0])
        sql = "UPDATE erc20token_import_log SET `status` = %s where id = %s"
        cursor.execute(sql, (status, transferlog[0]))
        connection.commit()
cursor.close()
connection.close()

logging.info('sleep')

