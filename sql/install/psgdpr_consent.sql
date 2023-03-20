CREATE TABLE IF NOT EXISTS `PREFIX_psgdpr_consent` (
  id_gdpr_consent INT(10) AUTO_INCREMENT NOT NULL,
  id_module INT(10) NOT NULL,
  active INT(10) NOT NULL,
  error INT(10),
  error_message TEXT,
  date_add DATETIME NOT NULL,
  date_upd DATETIME NOT NULL,
  PRIMARY KEY (id_gdpr_consent, id_module)
) ENGINE InnoDB DEFAULT CHARSET = UTF8;
