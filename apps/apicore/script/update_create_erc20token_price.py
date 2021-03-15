# SPDX-License-Identifier: MIT
# @author wangbingxuan wangbingxuan@tapatalk.com
# file for create erc20token in blockchain
# update price of create token and account balance
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
log_filename = log_path + 'script/'+filename+'/log_'+ str(datetime.date.today())  +'.txt'

logging.basicConfig(filename=log_filename,level=logging.INFO, format='%(asctime)s %(message)s' ,datefmt='%H:%M:%S' )

file_dir = os.path.split(log_filename)[0]
if not os.path.isdir(file_dir):
    os.makedirs(file_dir)
if not os.path.exists(filename ):
    os.system(r'touch %s' % filename)

path = dirname(abspath(__file__)) + '/../EvfGroupCoin.json'
with open(path) as contract_abi_file:
    contract_abi = json.load(contract_abi_file)


DB_HOST=config.get("DEFAULT","DB_HOST")
DB_PORT=int(config.get("DEFAULT","DB_PORT"))
DB_DATABASE=config.get("DEFAULT","DB_DATABASE")
DB_USERNAME=config.get("DEFAULT","DB_USERNAME")
DB_PASSWORD=config.get("DEFAULT","DB_PASSWORD")

count = 10000000000000
name = 'name'
symbol = 'sym'
decimal = 5

https_infura_url=config.get("DEFAULT","HTTPS_INFURA_URL")
web3 = Web3(Web3.HTTPProvider(https_infura_url))

cfToken = web3.eth.contract(abi=contract_abi['abi'], bytecode = contract_abi['evm']['bytecode']['object'])

gas = cfToken.constructor(int(count),name,symbol ,int(decimal), '0xBc757163b2C743c9D3bFDf0922Bddd433d7fb6A3').estimateGas()

url = 'mainnet.infura.io:443'
params = json.dumps({"jsonrpc":"2.0","method":"eth_gasPrice","params": [],"id":1})
headers = {"Content-type": "application/json"}
conn = http.client.HTTPSConnection(url)
conn.request('POST', '/v3/c981cd44768347e4b0aac1451150a589', params, headers)
response = conn.getresponse()
res = response.read()
try:
    res = json.loads(res.decode('UTF-8'))['result']
    wei = gas * int(res,16)
    ether = web3.fromWei(wei, 'ether')
except:
    logging.info('error : ether api' )
    exit()

url = 'api.coingecko.com:443'
conn = http.client.HTTPSConnection(url)
conn.request('GET', '/api/v3/coins/markets?vs_currency=usd&ids=ethereum')
response = conn.getresponse()
res = response.read()

connection = pymysql.connect(host=DB_HOST,
                             port=DB_PORT,
                             user=DB_USERNAME,
                             password=DB_PASSWORD,
                             db=DB_DATABASE,
                             charset='utf8')

cursor = connection.cursor()

try:
    current_price = json.loads(res.decode('UTF-8'))[0]['current_price']
    USD = ether * Decimal(current_price) * Decimal(1.3)
except:
    logging.info('error : USD api')
    sql = "UPDATE product SET status = 0 WHERE id = 1 ;"
    cursor.execute(sql)
    connection.commit()
    cursor.close()
    connection.close()
    exit()
# USD = math.ceil(USD)
USD = Decimal(USD).quantize(Decimal("0.00"))

if USD < 40:
    USD = 40

sql = "UPDATE product SET price = %s , status = %s WHERE id = 1 ;"
if USD >80:
    status = 0
else:
    status = 1

PUBLIC_KEY=config.get("DEFAULT","ERC20TOKEN_PUBLICKEY")
balance = web3.eth.getBalance(PUBLIC_KEY)
balance = Decimal(current_price)*Decimal(balance)/Decimal(math.pow(10,18))

if balance < 100:
    status = 0
    logging.info('balance not enough :' + str(balance))

cursor.execute(sql, (str(USD), status))

logging.info('success updated:' + str(USD) )
connection.commit()
cursor.close()
connection.close()

