import configparser
import logging
import os

import pymysql

class MySQL:
    def __init__(self):
        config=configparser.ConfigParser()
        config.read('/var/www/everforo.com/.env')
        
        DB_HOST = config.get("DEFAULT", "DB_HOST")
        DB_PORT = int(config.get("DEFAULT", "DB_PORT"))
        DB_DATABASE = config.get("DEFAULT", "DB_DATABASE")
        DB_USERNAME = config.get("DEFAULT", "DB_USERNAME")
        DB_PASSWORD = config.get("DEFAULT", "DB_PASSWORD")

        self.connection = pymysql.connect(host=DB_HOST, port=DB_PORT, user=DB_USERNAME, password=DB_PASSWORD, db=DB_DATABASE, charset='utf8')
        self.cursor = self.connection.cursor

    def query(self, sql, parameters):
        try:
            with self.connection.cursor() as cursor:

                if type(sql) is not list:
                    sql = [sql]
                    parameters = [parameters]

                for i in range(sql.length):
                    cursor.execute(sql[i], parameters[i])

            self.connection.commit()
        except:
            self.connection.rollback()
        finally:
            self.connection.close()

    def getAll(self, sql, parameters):
        try:
            with self.connection.cursor() as cursor:
                cursor.execute(sql, parameters)

                return cursor.fetchall()
        finally:
            self.connection.close()

    def getOne(self, sql, parameters):
        try:
            with self.connection.cursor() as cursor:
                cursor.execute(sql, parameters)

                return cursor.fetchone()
        finally:
            self.connection.close()

    def getconnection(self):
        return self.connection
    def getcursor(self):
        return self.cursor
    def close(self):
        self.cursor.close()
        self.connection.close()


