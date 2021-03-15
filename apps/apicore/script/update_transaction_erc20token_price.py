# SPDX-License-Identifier: MIT
# @author wangbingxuan wangbingxuan@tapatalk.com
# file for create erc20token in blockchain
import json
import sys
import pymysql
import configparser
import time
import datetime
import logging
import os
import requests
import http.client
import math

from os.path import dirname, abspath
from web3 import Web3
from web3.auto import w3
from decimal import *

# 流程  先 db连接->获取token信息->创建local account->保存公私钥->根据token信息上链创建合约->保存合约transaction信息->查询合约地址并且保存
config=configparser.ConfigParser()
config_path = dirname(abspath(__file__)) + '/../.env'
config.read(config_path)

log_path = config.get("DEFAULT", "PY_LOG_PATH")
(filename, extension) = os.path.splitext(os.path.basename(__file__))
log_filename = log_path + 'script/' + filename + '/log_' + str(datetime.date.today()) + '.txt'

file_dir = os.path.split(log_filename)[0]
if not os.path.isdir(file_dir):
    os.makedirs(file_dir)
if not os.path.exists(filename ):
    os.system(r'touch %s' % filename)

logging.basicConfig(filename=log_filename, level=logging.INFO, format='%(asctime)s %(message)s', datefmt='%H:%M:%S')

# path = dirname(abspath(__file__)) + '/eth.json'
# with open(path) as contract_abi_file:
#     contract_abi = json.load(contract_abi_file)

url = 'api.coingecko.com:443'
conn = http.client.HTTPSConnection(url)
conn.request('GET', '/api/v3/coins/markets?vs_currency=usd&ids=ethereum')
response = conn.getresponse()
res = response.read()
try:
    current_price = json.loads(res.decode('UTF-8'))[0]['current_price']
    # USD = ether * Decimal(current_price) * Decimal(1.3)
except:
    logging.info('error : USD api')
    exit()

https_infura_url = config.get("DEFAULT", "HTTPS_INFURA_URL")
web3 = Web3(Web3.HTTPProvider(https_infura_url))
url = 'mainnet.infura.io:443'
params = json.dumps({"jsonrpc":"2.0","method":"eth_gasPrice","params": [],"id":1})
headers = {"Content-type": "application/json"}
conn = http.client.HTTPSConnection(url)
conn.request('POST', '/v3/c981cd44768347e4b0aac1451150a589', params, headers)
response = conn.getresponse()
res = response.read()
try:
    gasPrice = json.loads(res.decode('UTF-8'))['result']
except:
    logging.info('error : ether api' )
    exit()

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
sql = "select p.id, e.id as token_id, e.contract_address, e.abi, allow_import from product p left join erc20_token e on p.related_id = e.id where p.product_type = 2 and p.`status` = 1"
cursor.execute(sql)
product_ary = cursor.fetchall()
logging.info('start')
for row in product_ary:
    if row[4] == 1:
        logging.info('update start token id:' + str(row[1]))
        contractAddress = row[2]
        contractAddress = web3.toChecksumAddress(contractAddress)

        contract_abi = row[3]
        unicorns = web3.eth.contract(address=contractAddress, abi=contract_abi)
        ToAddress = '0xf8aC59E3bAe2304EB7c01d65Aa69857d0cC8718A'
        FromAddress = '0xF4d36bC42cF89154F09d6733b359129B3feF1bdF'
        FromAddress = web3.toChecksumAddress(FromAddress)
        nonce = web3.eth.getTransactionCount(ToAddress, 'latest')
        unicorn_txn = unicorns.functions.transfer(
            ToAddress,
            100,
        )

        trx = unicorn_txn.buildTransaction({
            'from': FromAddress,
            'gas': 7000000,
            'gasPrice': web3.toWei('1', 'gwei'),
            'nonce': nonce,
        })
        try:
            params = json.dumps({"jsonrpc": "2.0", "method": "eth_estimateGas", "params": [{"from": trx['from'],"to": trx['to'],"gas": hex(trx['gas']),"gasPrice": hex(trx['gasPrice']),"value": "0x0","data": trx['data']}], "id": 1})
            headers = {"Content-type": "application/json"}
            conn = http.client.HTTPSConnection(url)
            conn.request('POST', '/v3/c981cd44768347e4b0aac1451150a589', params, headers)
            response = conn.getresponse()
            res = response.read()
            gas = json.loads(res.decode('UTF-8'))['result']
            wei = int(gas, 16) * int(gasPrice, 16)
            ether = web3.fromWei(wei, 'ether')
            price = (Decimal(ether) * Decimal(current_price) * Decimal(1.2) ).quantize(Decimal("0.00"))
        except:
            logging.info('error : ether api')
            print(res)
        else:
            sql = "UPDATE product SET price = %s , status = %s WHERE id = %s ;"
            cursor.execute(sql, (str(price), 1, row[0]))
            print(price)
            logging.info('success :' + str(price))
            connection.commit()


logging.info('end')
cursor.close()
connection.close()

