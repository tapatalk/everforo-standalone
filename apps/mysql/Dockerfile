FROM mysql:8.0

ENV MYSQL_RANDOM_ROOT_PASSWORD="yes"
ENV MYSQL_DATABASE="everforo"
ENV MYSQL_USER="efadmin"
ENV MYSQL_PASSWORD="12344321"

CMD ["--default-authentication-plugin=mysql_native_password"]

# docker run -itd --name mysql --restart=unless-stopped --mount type=bind,source=$HOME/mysql-dump,target=/docker-entrypoint-initdb.d -p 3306:3306 -p 33060:33060 hansen1416/mysql

# docker network connect --alias localmysql everforo_efnet mysql