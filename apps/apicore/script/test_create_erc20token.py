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
log_filename = log_path + 'script/'+filename+'/log_'+ str(datetime.date.today())  +'.txt'

file_dir = os.path.split(log_filename)[0]
if not os.path.isdir(file_dir):
    os.makedirs(file_dir)
if not os.path.exists(filename ):
    os.system(r'touch %s' % filename)

path = dirname(abspath(__file__)) + '/../EvfGroupCoin.json'
with open(path) as contract_abi_file:
    contract_abi = json.load(contract_abi_file)

acct = w3.eth.account.create()

print(acct.privateKey.hex())
print(acct.address)
