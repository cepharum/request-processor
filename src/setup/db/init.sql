CREATE TABLE IF NOT EXISTS $$prefix$$requests
(
    id            INTEGER UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    formid        VARCHAR(63),
    json          MEDIUMTEXT       NOT NULL,
    yaml          MEDIUMTEXT       NOT NULL,
    mailaddress   VARCHAR(255),
    validationkey VARCHAR(255),
    querykey      VARCHAR(255),
    ts_received   INTEGER UNSIGNED NOT NULL,
    ts_clientmail INTEGER UNSIGNED,
    ts_validated  INTEGER UNSIGNED,
    ts_adminmail  INTEGER UNSIGNED,
    ts_grabbed    INTEGER UNSIGNED
)
