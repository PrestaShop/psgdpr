CREATE TABLE IF NOT EXISTS `psgdpr_log` (
  id_gdpr_log INT(10) unsigned AUTO_INCREMENT NOT NULL,
  id_customer INT(10) unsigned NOT NULL,
  id_guest INT(10) unsigned NULL,
  client_name VARCHAR(250),
  id_module INT(10) unsigned NOT NULL,
  request_type INT(10) NOT NULL,
  date_add DATETIME NOT NULL,
  date_upd DATETIME NOT NULL,
  PRIMARY KEY (id_gdpr_log)
  INDEX (id_customer)
  INDEX idx_id_customer (id_customer, id_guest, client_name, id_module, date_add, date_upd)
) ENGINE InnoDB DEFAULT CHARSET=UTF8;
