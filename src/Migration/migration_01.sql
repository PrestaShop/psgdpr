CREATE TABLE IF NOT EXISTS `psgdpr_log` (
  id_gdpr_log INT(10) AUTO_INCREMENT NOT NULL,
  id_customer INT(10) NOT NULL,
  id_guest INT(10) NULL,
  client_name VARCHAR(255),
  id_module INT(10) NOT NULL,
  request_type INT(10) NOT NULL,
  date_add DATETIME NOT NULL,
  date_upd DATETIME NOT NULL,
  PRIMARY KEY (id_gdpr_log)
  INDEX (id_customer)
  INDEX idx_id_customer (id_customer, id_guest, client_name, id_module, date_add, date_upd)
) ENGINE InnoDB DEFAULT CHARSET=UTF8;

CREATE TABLE IF NOT EXISTS `psgdpr_consent` (
  id_gdpr_consent INT(10) AUTO_INCREMENT NOT NULL,
  id_module INT(10) NOT NULL,
  active INT(10) NOT NULL,
  error INT(10),
  error_message TEXT,
  date_add DATETIME NOT NULL,
  date_upd DATETIME NOT NULL,
  PRIMARY KEY (id_gdpr_consent, id_module)
) ENGINE InnoDB DEFAULT CHARSET=UTF8;

CREATE TABLE IF NOT EXISTS `psgdpr_consent_lang` (
  id_gdpr_consent INT(10) AUTO_INCREMENT NOT NULL,
  id_lang INT(10) NOT NULL,
  message TEXT,
  id_shop INT(10) NOT NULL,
  PRIMARY KEY (id_gdpr_consent, id_lang, id_shop)
) ENGINE InnoDB DEFAULT CHARSET=UTF8;
