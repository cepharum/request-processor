#!/bin/bash

docker-entrypoint.sh mysqld --init-file=/init-mysql.sql
