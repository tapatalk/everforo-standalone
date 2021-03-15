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

path = dirname(abspath(__file__)) + '/../EvfGroupCoin.json'
with open(path) as contract_abi_file:
    not_import_contract_abi = json.load(contract_abi_file)

logging.basicConfig(filename=log_filename,level=logging.INFO, format='%(asctime)s %(message)s' ,datefmt='%H:%M:%S' )

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

rowcount = 100

while rowcount == 100:

    sql = "SELECT withdraw_request.id, withdraw_request.wallet_id,withdraw_request.amount,withdraw_request.token_id,withdraw_request.`to`, erc20_token.allow_import , erc20_token.contract_address , erc20token_pool.private_key , erc20token_pool.public_key  , erc20token_pool.abi FROM `withdraw_request` left join erc20_token on withdraw_request.token_id = erc20_token.id  left join erc20token_pool on withdraw_request.token_id = erc20token_pool.token_id where deleted_at  is NUll limit 100"
    cursor.execute( sql )
    request = cursor.fetchall()
    rowcount = cursor.rowcount


    for row in request:
        request_id = row[0]
        wallet = row[1]
        amount = int(row[2])
        amount = 1
        token_id = row[3]
        address = '0x31631CF4a13526206A670B17c26CdeCB7a18af29'
        allow_import = row[5]
        contractAddress = row[6]
        contractAddress = '0xa5dc04482520552690FD0397b8aCA06F60BaC8D4'
        private_key = row[7]
        private_key = '0x695881cd8e16ec35c9855b61822183c3d87a6736cdd3ab0ee20cf1b02d31d187'
        public_key = row[8]
        public_key = '0x225532B0BcF388e2001877323A9278d550dB2233'
        contract_abi = row[9]
        contract_abi = not_import_contract_abi['abi']


        web3 = Web3(Web3.HTTPProvider('https://rinkeby.infura.io/v3/c981cd44768347e4b0aac1451150a589'))
        ToAddress = web3.toChecksumAddress(address)
        contractAddress = web3.toChecksumAddress(contractAddress)
        unicorns = web3.eth.contract(address=contractAddress, abi=contract_abi)
        nonce = web3.eth.getTransactionCount(public_key, 'latest')

        if allow_import == 1:
            balance = unicorns.functions.balanceOf(public_key).call()
            if(balance < amount):
                # logerror
                logging.info('error :' + str(public_key) + ' balance not enough, token id:' + str(token_id))
                continue

        unicorn_txn = unicorns.functions.transfer(
            ToAddress,
            amount,
        ).buildTransaction({
            'gas': 70000,
            'gasPrice': web3.toWei('1', 'gwei'),
            'nonce': nonce,
        })

        signed_txn = web3.eth.account.signTransaction(unicorn_txn, private_key=private_key)

        result = web3.eth.sendRawTransaction(signed_txn.rawTransaction)
        transaction = result.hex()
        print(transaction)
        exit()

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
