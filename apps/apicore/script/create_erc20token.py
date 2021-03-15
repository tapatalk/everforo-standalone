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
import math
import os

from os.path import dirname, abspath
from web3 import Web3
from web3.auto import w3

# 流程  先 db连接->获取token信息->创建local account->保存公私钥->根据token信息上链创建合约->保存合约transaction信息->查询合约地址并且保存
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

leng = len(sys.argv)
if leng >= 1:
    group_token_id = sys.argv[1]
else:
    logging.info('no token_id')
    exit()

DB_HOST=config.get("DEFAULT","DB_HOST")
DB_PORT=int(config.get("DEFAULT","DB_PORT"))
DB_DATABASE=config.get("DEFAULT","DB_DATABASE")
DB_USERNAME=config.get("DEFAULT","DB_USERNAME")
DB_PASSWORD=config.get("DEFAULT","DB_PASSWORD")

PRIVATE_KEY=config.get("DEFAULT","ERC20TOKEN_PRIVATEKEY")
PUBLIC_KEY=config.get("DEFAULT","ERC20TOKEN_PUBLICKEY")

connection = pymysql.connect(host=DB_HOST,
                             port=DB_PORT,
                             user=DB_USERNAME,
                             password=DB_PASSWORD,
                             db=DB_DATABASE,
                             charset='utf8')

cursor = connection.cursor()

sql = "SELECT group_erc20token.token_id, group_erc20token.`status`, group_erc20token.blockchain_balance, erc20_token.`name`,     erc20_token.symbol ,erc20_token.`decimal` FROM group_erc20token left join erc20_token on group_erc20token.token_id = erc20_token.id WHERE group_erc20token.id = %s ;"
cursor.execute( sql, (group_token_id) )
token = cursor.fetchone()
token_id = token[0]
status = token[1]
count = int(token[2])
name = token[3]
symbol = token[4]
decimal = int(token[5])

if decimal > 0:
    count = int(count/ math.pow(10,decimal))

path = dirname(abspath(__file__)) + '/../EvfGroupCoin.json'
with open(path) as contract_abi_file:
    contract_abi = json.load(contract_abi_file)

# create local account
acct = w3.eth.account.create()
sql = "UPDATE group_erc20token SET private_key = %s , public_key = %s, status = 3   WHERE id = %s ;"
cursor.execute( sql, (acct.privateKey.hex(), acct.address, group_token_id) )
connection.commit()
logging.info('create account id:' + str(token_id))
https_infura_url=config.get("DEFAULT","HTTPS_INFURA_URL")
web3 = Web3(Web3.HTTPProvider(https_infura_url))
cfToken = web3.eth.contract(abi=contract_abi['abi'], bytecode = contract_abi['evm']['bytecode']['object'])

gas = cfToken.constructor(count,name,symbol,decimal, acct.address).estimateGas()
gas = int(int(gas) * 1.2)

# sign transaction
signed_txn = web3.eth.account.signTransaction(cfToken.constructor(count,name,symbol,decimal, acct.address).buildTransaction({
    "nonce":web3.eth.getTransactionCount(PUBLIC_KEY),
    # "nonce":web3.eth.getTransactionCount(web3.eth.coinbase),  local test
    "gasPrice":web3.eth.gasPrice,"gas":gas}),
    PRIVATE_KEY
    )
# send transaction
result = web3.eth.sendRawTransaction(signed_txn.rawTransaction)
transaction = result.hex()

# another script will verify the transaction
# contractAddress = web3.eth.getTransactionReceipt(result.hex()).contractAddress

# sql = "UPDATE group_erc20token SET status = 3 WHERE id = %s ;"
# cursor.execute( sql, (group_token_id) )

sql = "INSERT INTO erc20token_import_log (`transactionHash`,`token_id`,`to`,`value`,`status`,`created_at`) VALUES (%s,%s,%s,%s,%s,%s)"
cursor.execute(sql, (transaction,
                     token_id,
                     acct.address,
                     int(count),
                     1,
                     datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
                     ))
connection.commit()

logging.info('success :' + str(token_id))
# sql = "UPDATE erc20_token SET contract_address = %s WHERE id = %s ;"
# cursor.execute( sql, (contractAddress , token_id) )
# connection.commit()
cursor.close()
connection.close()

