import json
import sys
import pymysql
import configparser

from os.path import dirname, abspath
from web3 import Web3
from web3.auto import w3


config=configparser.ConfigParser()
config_path = dirname(abspath(__file__)) + '/../.env'
config.read(config_path)

leng = len(sys.argv)
if leng >= 1:
    token_id = sys.argv[1]
else:
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

sql = "SELECT name,symbol,status FROM erc20_token WHERE id = %s ;"

cursor.execute( sql, (token_id) )
token = cursor.fetchone()
name = token[0]
symbol = token[1]
status = token[2]

sql = "SELECT balance FROM token_wallet WHERE user_id = 0 and token_id = %s ;"
cursor.execute( sql, (token_id) )
token = cursor.fetchone()
count = token[0]

path = dirname(abspath(__file__)) + '/../EvfGroupCoin.json'
with open(path) as contract_abi_file:
    contract_abi = json.load(contract_abi_file)

# sql = "UPDATE erc20_token SET status = 2 WHERE id = %s ;"
# cursor.execute( sql, (token_id) )

# create local account
acct = w3.eth.account.create()
sql = "UPDATE token_wallet SET private_key = %s , public_key = %s  WHERE user_id = 0 and token_id = %s ;"
cursor.execute( sql, (acct.privateKey.hex(), acct.address, token_id) )

# web3 = Web3(Web3.HTTPProvider('http://127.0.0.1:8545'))
web3 = Web3(Web3.WebsocketProvider('ws://127.0.0.1:8545'))
cfToken = web3.eth.contract(abi=contract_abi['abi'], bytecode = contract_abi['evm']['bytecode']['object'])

signed_txn = web3.eth.account.signTransaction(cfToken.constructor(int(count),name,symbol ,0, acct.address).buildTransaction({"nonce":web3.eth.getTransactionCount(web3.eth.coinbase),
                                            "gasPrice":web3.eth.gasPrice,"gas":6721975}),
                                         '0x7ee54124336865e34a79167c41be8ee0b3743fd719f843d8d3f473cd7e219399')
result = web3.eth.sendRawTransaction(signed_txn.rawTransaction)
transaction = result.hex()

sql = "UPDATE erc20_token SET transaction = %s , status = 2   WHERE id = %s ;"
cursor.execute( sql, (transaction, token_id) )

connection.commit()
cursor.close()
connection.close()

contractAddress = web3.eth.getTransactionReceipt(result.hex()).contractAddress

# connection again
connection = pymysql.connect(host=DB_HOST,
                             port=DB_PORT,
                             user=DB_USERNAME,
                             password=DB_PASSWORD,
                             db=DB_DATABASE,
                             charset='utf8')

cursor = connection.cursor()

sql = "UPDATE erc20_token SET status = 3, contract_address = %s WHERE id = %s ;"
cursor.execute( sql, (contractAddress , token_id) )
connection.commit()
cursor.close()
connection.close()

