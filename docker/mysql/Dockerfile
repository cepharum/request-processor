FROM mysql:5

COPY setup-mysql.sh /setup-mysql.sh
COPY init-mysql.sql /init-mysql.sql

ENTRYPOINT ["/bin/bash", "/setup-mysql.sh"]
