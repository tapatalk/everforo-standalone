#!/bin/bash
docker-compose -p efenterprise up -d
docker network connect --alias localmysql efenterprise_efe mysql
