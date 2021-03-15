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

file_dir = os.path.split(log_filename)[0]
if not os.path.isdir(file_dir):
    os.makedirs(file_dir)
if not os.path.exists(filename ):
    os.system(r'touch %s' % filename)

path = dirname(abspath(__file__)) + '/../EvfGroupCoin.json'
with open(path) as contract_abi_file:
    not_import_contract_abi = json.load(contract_abi_file)

logging.basicConfig(filename=log_filename,level=logging.INFO, format='%(asctime)s %(message)s' ,datefmt='%H:%M:%S' )

DB_HOST=config.get("DEFAULT","DB_HOST")
DB_PORT=int(config.get("DEFAULT","DB_PORT"))
DB_DATABASE=config.get("DEFAULT","DB_DATABASE")
DB_USERNAME=config.get("DEFAULT","DB_USERNAME")
DB_PASSWORD=config.get("DEFAULT","DB_PASSWORD")

https_infura_url=config.get("DEFAULT","HTTPS_INFURA_URL")

connection = pymysql.connect(host=DB_HOST,
                             port=DB_PORT,
                             user=DB_USERNAME,
                             password=DB_PASSWORD,
                             db=DB_DATABASE,
                             charset='utf8')

cursor = connection.cursor()

rowcount = 100

web3 = Web3(Web3.HTTPProvider(https_infura_url))
gasPrice = Decimal(web3.eth.gasPrice) / Decimal(math.pow(10,9))

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
gaslimit = int(Decimal(10)*Decimal(math.pow(10,9))/(Decimal(current_price)*gasPrice))

# print(Decimal(current_price)/)
# gaslimit =

while rowcount == 100:

    sql = "SELECT withdraw_request.id, withdraw_request.wallet_id,withdraw_request.amount,withdraw_request.token_id,withdraw_request.`to`, erc20_token.allow_import , erc20_token.contract_address , erc20token_pool.private_key , erc20token_pool.public_key  , erc20_token.abi FROM `withdraw_request` left join erc20_token on withdraw_request.token_id = erc20_token.id  left join erc20token_pool on withdraw_request.token_id = erc20token_pool.token_id where `status` = 3 and deleted_at  is NUll limit 100"
    cursor.execute( sql )
    request = cursor.fetchall()
    rowcount = cursor.rowcount

    if cursor.rowcount == 0:
        continue

    for row in request:
        request_id = row[0]
        wallet = row[1]
        amount = int(row[2])
        token_id = row[3]
        address = row[4]
        allow_import = row[5]
        contractAddress = row[6]
        private_key = row[7]
        public_key = row[8]
        contract_abi = row[9]
        # contract_abi = json.load(contract_abi)
        # logging.info('abi :' + contract_abi)
        if allow_import != 1:
            sql = "SELECT public_key,private_key from group_erc20token where token_id =  %s ;"
            cursor.execute( sql, (token_id) )
            tokenrow = cursor.fetchone()
            public_key = tokenrow[0]
            private_key = tokenrow[1]
            contract_abi = not_import_contract_abi['abi']


        ToAddress = web3.toChecksumAddress(address)
        contractAddress = web3.toChecksumAddress(contractAddress)
        unicorns = web3.eth.contract(address=contractAddress, abi=contract_abi)
        nonce = web3.eth.getTransactionCount(public_key, 'pending')

        print(nonce)

        if allow_import == 1:
            balance = unicorns.functions.balanceOf(public_key).call()
            if(balance < amount):
                # logerror
                logging.info('error :' + str(public_key) + ' balance not enough, token id:' + str(token_id))
                continue

        try:
            unicorn_txn = unicorns.functions.transfer(
                ToAddress,
                amount,
            ).buildTransaction({
                'gas': gaslimit,
                'gasPrice': int(gasPrice),
                'nonce': nonce,
            })

            signed_txn = web3.eth.account.signTransaction(unicorn_txn, private_key=private_key)

            result = web3.eth.sendRawTransaction(signed_txn.rawTransaction)
            transaction = result.hex()
            print(transaction)

        except ValueError:
            print(ValueError);
            exit()

        except:
            sql = "UPDATE withdraw_request SET status = 5  WHERE id = %s ;"
            cursor.execute(sql, (request_id))
            connection.commit()
            logging.info('error request id :' + str(request_id))
        else:
            sql = "UPDATE withdraw_request SET status = 4 , transactionHash = %s  WHERE id = %s ;"
            cursor.execute(sql, (transaction, request_id))
            connection.commit()
            logging.info('success request id :' + str(request_id))


cursor.close()
connection.close()

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
