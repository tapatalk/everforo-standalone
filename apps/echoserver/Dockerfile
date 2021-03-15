FROM node:15.3-alpine

RUN npm --global config set user root && \
    npm --global install laravel-echo-server

COPY laravel-echo-server.local.json /home/laravel-echo-server.json

EXPOSE 6001

WORKDIR /home/

CMD ["laravel-echo-server", "start", "--force"]