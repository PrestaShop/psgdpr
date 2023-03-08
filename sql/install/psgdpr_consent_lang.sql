CREATE TABLE IF NOT EXISTS `PREFIX_psgdpr_consent_lang` (
  id_gdpr_consent INT(10) AUTO_INCREMENT NOT NULL,
  id_lang INT(10) NOT NULL,
  message TEXT,
  id_shop INT(10) NOT NULL,
  PRIMARY KEY (id_gdpr_consent, id_lang, id_shop)
) ENGINE InnoDB DEFAULT CHARSET = UTF8;
