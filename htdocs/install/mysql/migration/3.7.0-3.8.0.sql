--
-- Be carefull to requests order.
-- This file must be loaded by calling /install/index.php page
-- when current version is 3.8.0 or higher.
--
-- To rename a table:       ALTER TABLE llx_table RENAME TO llx_table_new;
-- To add a column:         ALTER TABLE llx_table ADD COLUMN newcol varchar(60) NOT NULL DEFAULT '0' AFTER existingcol;
-- To rename a column:      ALTER TABLE llx_table CHANGE COLUMN oldname newname varchar(60);
-- To drop a column:        ALTER TABLE llx_table DROP COLUMN oldname;
-- To change type of field: ALTER TABLE llx_table MODIFY COLUMN name varchar(60);
-- To drop a foreign key:   ALTER TABLE llx_table DROP FOREIGN KEY fk_name;
-- To restrict request to Mysql version x.y use -- VMYSQLx.y
-- To restrict request to Pgsql version x.y use -- VPGSQLx.y
-- To make pk to be auto increment (mysql):    VMYSQL4.3 ALTER TABLE llx_c_shipment_mode CHANGE COLUMN rowid rowid INTEGER NOT NULL AUTO_INCREMENT;
-- To make pk to be auto increment (postgres): VPGSQL8.2 NOT POSSIBLE. MUST DELETE/CREATE TABLE
-- To set a field as NULL:                     VPGSQL8.2 ALTER TABLE llx_table ALTER COLUMN name DROP NOT NULL;
-- To set a field as default NULL:             VPGSQL8.2 ALTER TABLE llx_table ALTER COLUMN name SET DEFAULT NULL;
-- -- VPGSQL8.2 DELETE FROM llx_usergroup_user      WHERE fk_user      NOT IN (SELECT rowid from llx_user);
-- -- VMYSQL4.1 DELETE FROM llx_usergroup_user      WHERE fk_usergroup NOT IN (SELECT rowid from llx_usergroup);


ALTER TABLE llx_extrafields ADD COLUMN perms varchar(255) after fieldrequired;
ALTER TABLE llx_extrafields ADD COLUMN list integer DEFAULT 0 after perms;

ALTER TABLE llx_payment_salary ADD COLUMN salary real after datev;

UPDATE llx_projet_task_time SET task_datehour = task_date where task_datehour IS NULL;
ALTER TABLE llx_projet_task_time ADD COLUMN task_date_withhour integer DEFAULT 0 after task_datehour;


ALTER TABLE llx_commande_fournisseur MODIFY COLUMN date_livraison datetime; 

-- Add id commandefourndet in llx_commande_fournisseur_dispatch to correct /fourn/commande/dispatch.php display when several times same product in supplier order
ALTER TABLE llx_commande_fournisseur_dispatch ADD COLUMN fk_commandefourndet INTEGER NOT NULL DEFAULT 0 AFTER fk_product;


-- Remove menu entries of removed or renamed modules
DELETE FROM llx_menu where module = 'printipp';


ALTER TABLE llx_bank ADD INDEX idx_bank_num_releve(num_releve);


--create table for price expressions and add column in product supplier
create table llx_c_price_expression
(
  rowid      integer AUTO_INCREMENT PRIMARY KEY,
  title      varchar(20) NOT NULL,
  expression varchar(80) NOT NULL
)ENGINE=innodb;

ALTER TABLE llx_product_fournisseur_price ADD COLUMN fk_supplier_price_expression integer DEFAULT NULL;
ALTER TABLE llx_product ADD COLUMN fk_price_expression integer DEFAULT NULL;
ALTER TABLE llx_product_price ADD COLUMN fk_price_expression integer DEFAULT NULL;


--create table for user conf of printing driver
CREATE TABLE llx_printing 
(
 rowid integer AUTO_INCREMENT PRIMARY KEY,
 tms timestamp,
 datec datetime,
 printer_name text NOT NULL, 
 printer_location text NOT NULL,
 printer_id varchar(255) NOT NULL,
 copy integer NOT NULL DEFAULT '1',
 module varchar(16) NOT NULL,
 driver varchar(16) NOT NULL,
 userid integer
)ENGINE=innodb;

ALTER TABLE llx_product_fournisseur_price ADD COLUMN fk_price_expression integer DEFAULT NULL;

-- Taiwan VAT Rates
insert into llx_c_tva(rowid,fk_pays,taux,recuperableonly,note,active) values ( 2131, 213, '5', '0', 'VAT 5%', 1);

-- Add situation invoices
ALTER TABLE llx_facture ADD COLUMN situation_cycle_ref smallint;
ALTER TABLE llx_facture ADD COLUMN situation_counter smallint;
ALTER TABLE llx_facture ADD COLUMN situation_final smallint;
ALTER TABLE llx_facturedet ADD COLUMN situation_percent real;
ALTER TABLE llx_facturedet ADD COLUMN fk_prev_id integer;

-- Convert SMTP config to main entity, so new entities don't get the old values
UPDATE llx_const SET entity = 1 WHERE entity = 0 AND name = "MAIN_MAIL_SENDMODE";
UPDATE llx_const SET entity = 1 WHERE entity = 0 AND name = "MAIN_MAIL_SMTP_PORT";
UPDATE llx_const SET entity = 1 WHERE entity = 0 AND name = "MAIN_MAIL_SMTP_SERVER";
UPDATE llx_const SET entity = 1 WHERE entity = 0 AND name = "MAIN_MAIL_SMTPS_ID";
UPDATE llx_const SET entity = 1 WHERE entity = 0 AND name = "MAIN_MAIL_SMTPS_PW";
UPDATE llx_const SET entity = 1 WHERE entity = 0 AND name = "MAIN_MAIL_EMAIL_TLS";


create table llx_bank_account_extrafields
(
  rowid                     integer AUTO_INCREMENT PRIMARY KEY,
  tms                       timestamp,
  fk_object                 integer NOT NULL,
  import_key                varchar(14)                          		-- import key
) ENGINE=innodb;


ALTER TABLE llx_stock_mouvement MODIFY COLUMN label varchar(255);
ALTER TABLE llx_stock_mouvement ADD COLUMN inventorycode varchar(128);

ALTER TABLE llx_product_association ADD COLUMN incdec integer DEFAULT 1;



ALTER TABLE llx_bank_account_extrafields ADD INDEX idx_bank_account_extrafields (fk_object);


create table llx_contratdet_extrafields
(
  rowid            integer AUTO_INCREMENT PRIMARY KEY,
  tms              timestamp,
  fk_object        integer NOT NULL,    -- object id
  import_key       varchar(14)      	-- import key
)ENGINE=innodb;

ALTER TABLE llx_contratdet_extrafields ADD INDEX idx_contratdet_extrafields (fk_object);

ALTER TABLE llx_product_fournisseur_price ADD COLUMN delivery_time_days integer;


ALTER TABLE llx_commande_fournisseur_dispatch ADD COLUMN comment	varchar(255);
ALTER TABLE llx_commande_fournisseur_dispatch ADD COLUMN status integer;
ALTER TABLE llx_commande_fournisseur_dispatch ADD COLUMN tms timestamp;
ALTER TABLE llx_commande_fournisseur_dispatch ADD COLUMN batch varchar(30) DEFAULT NULL;
ALTER TABLE llx_commande_fournisseur_dispatch ADD COLUMN eatby date DEFAULT NULL;
ALTER TABLE llx_commande_fournisseur_dispatch ADD COLUMN sellby date DEFAULT NULL;
ALTER TABLE llx_stock_mouvement ADD COLUMN batch varchar(30) DEFAULT NULL;
ALTER TABLE llx_stock_mouvement ADD COLUMN eatby date DEFAULT NULL;
ALTER TABLE llx_stock_mouvement ADD COLUMN sellby date DEFAULT NULL;



CREATE TABLE llx_expensereport (
  rowid integer NOT NULL AUTO_INCREMENT PRIMARY KEY,
  ref        		varchar(50) NOT NULL,
  entity 			integer DEFAULT 1 NOT NULL,		-- multi company id
  ref_number_int 	integer DEFAULT NULL,
  ref_ext 			integer,
  total_ht 			double(24,8) DEFAULT 0,
  total_tva 		double(24,8) DEFAULT 0,
  localtax1			double(24,8) DEFAULT 0,				-- amount total localtax1
  localtax2			double(24,8) DEFAULT 0,				-- amount total localtax2	
  total_ttc 		double(24,8) DEFAULT 0,
  date_debut 		date NOT NULL,
  date_fin 			date NOT NULL,
  date_create 		datetime NOT NULL,
  date_valid 		datetime,
  date_approve		datetime,
  date_refuse 		datetime,
  date_cancel 		datetime,
  date_paiement 	datetime,
  tms 		 		timestamp,
  fk_user_author 	integer NOT NULL,
  fk_user_modif 	integer DEFAULT NULL,
  fk_user_valid 	integer DEFAULT NULL,
  fk_user_validator integer DEFAULT NULL,
  fk_user_approve   integer DEFAULT NULL,
  fk_user_refuse 	integer DEFAULT NULL,
  fk_user_cancel 	integer DEFAULT NULL,
  fk_user_paid 		integer DEFAULT NULL,
  fk_statut			integer NOT NULL,		-- 1=brouillon, 2=validé (attente approb), 4=annulé, 5=approuvé, 6=payed, 99=refusé
  fk_c_paiement 	integer DEFAULT NULL,
  note_public		text,
  note_private 		text,
  detail_refuse 	varchar(255) DEFAULT NULL,
  detail_cancel 	varchar(255) DEFAULT NULL,
  integration_compta integer DEFAULT NULL,		-- not used
  fk_bank_account 	integer DEFAULT NULL,
  model_pdf 		varchar(50) DEFAULT NULL
) ENGINE=innodb;


CREATE TABLE llx_expensereport_det
(
   rowid integer NOT NULL AUTO_INCREMENT PRIMARY KEY,
   fk_expensereport integer NOT NULL,
   fk_c_type_fees integer NOT NULL,
   fk_projet integer NOT NULL,
   fk_c_tva integer NOT NULL,
   comments text NOT NULL,
   product_type integer DEFAULT -1,
   qty real NOT NULL,
   value_unit real NOT NULL,
   remise_percent real,
   tva_tx						double(6,3),						    -- Vat rat
   localtax1_tx               	double(6,3)  DEFAULT 0,    		 	-- localtax1 rate
   localtax1_type			 	varchar(10)	  	 NULL, 				 	-- localtax1 type
   localtax2_tx               	double(6,3)  DEFAULT 0,    		 	-- localtax2 rate
   localtax2_type			 	varchar(10)	  	 NULL, 				 	-- localtax2 type
   total_ht double(24,8) DEFAULT 0 NOT NULL,
   total_tva double(24,8) DEFAULT 0 NOT NULL,
   total_localtax1				double(24,8)  	DEFAULT 0,		-- Total LocalTax1 for total quantity of line
   total_localtax2				double(24,8)	DEFAULT 0,		-- total LocalTax2 for total quantity of line
   total_ttc double(24,8) DEFAULT 0 NOT NULL,
   date date NOT NULL,
   info_bits					integer DEFAULT 0,				-- TVA NPR ou non
   special_code					integer DEFAULT 0,			    -- code pour les lignes speciales
   rang							integer DEFAULT 0,				-- position of line
   import_key					varchar(14)
) ENGINE=innodb;


ALTER TABLE llx_projet ADD COLUMN budget_amount double(24,8);


create table llx_commande_fournisseurdet_extrafields
(
  rowid                     integer AUTO_INCREMENT PRIMARY KEY,
  tms                       timestamp,
  fk_object                 integer NOT NULL,
  import_key                varchar(14)
) ENGINE=innodb;

ALTER TABLE llx_commande_fournisseurdet_extrafields ADD INDEX idx_commande_fournisseurdet_extrafields (fk_object);


create table llx_facture_fourn_det_extrafields
(
  rowid                     integer AUTO_INCREMENT PRIMARY KEY,
  tms                       timestamp,
  fk_object                 integer NOT NULL,
  import_key                varchar(14)                          		-- import key
) ENGINE=innodb;

ALTER TABLE llx_facture_fourn_det_extrafields ADD INDEX idx_facture_fourn_det_extrafields (fk_object);

ALTER TABLE llx_facture_fourn_det ADD COLUMN special_code	 integer DEFAULT 0;
ALTER TABLE llx_facture_fourn_det ADD COLUMN rang integer DEFAULT 0;
ALTER TABLE llx_facture_fourn_det ADD COLUMN fk_parent_line integer NULL after fk_facture_fourn;

ALTER TABLE llx_commande_fournisseurdet ADD COLUMN special_code	 integer DEFAULT 0;
ALTER TABLE llx_commande_fournisseurdet ADD COLUMN rang integer DEFAULT 0;
ALTER TABLE llx_commande_fournisseurdet ADD COLUMN fk_parent_line integer NULL after fk_commande;

ALTER TABLE llx_projet ADD COLUMN date_close datetime DEFAULT NULL;    
ALTER TABLE llx_projet ADD COLUMN fk_user_close integer DEFAULT NULL;


  
-- Module AskPriceSupplier --
CREATE TABLE llx_askpricesupplier (
  rowid integer NOT NULL AUTO_INCREMENT PRIMARY KEY,
  ref varchar(30) NOT NULL,
  entity integer NOT NULL DEFAULT '1',
  ref_ext varchar(255) DEFAULT NULL,
  ref_int varchar(255) DEFAULT NULL,
  fk_soc integer DEFAULT NULL,
  fk_projet integer DEFAULT NULL,
  tms timestamp,
  datec datetime DEFAULT NULL,
  date_valid datetime DEFAULT NULL,
  date_cloture datetime DEFAULT NULL,
  fk_user_author integer DEFAULT NULL,
  fk_user_modif integer DEFAULT NULL,
  fk_user_valid integer DEFAULT NULL,
  fk_user_cloture integer DEFAULT NULL,
  fk_statut smallint NOT NULL DEFAULT '0',
  price double DEFAULT '0',
  remise_percent double DEFAULT '0',
  remise_absolue double DEFAULT '0',
  remise double DEFAULT '0',
  total_ht double(24,8) DEFAULT 0,
  tva double(24,8) DEFAULT 0,
  localtax1 double(24,8) DEFAULT 0,
  localtax2 double(24,8) DEFAULT 0,
  total double(24,8) DEFAULT 0,
  fk_account integer DEFAULT NULL,
  fk_currency varchar(3) DEFAULT NULL,
  fk_cond_reglement integer DEFAULT NULL,
  fk_mode_reglement integer DEFAULT NULL,
  note_private text,
  note_public text,
  model_pdf varchar(255) DEFAULT NULL,
  date_livraison date DEFAULT NULL,
  fk_shipping_method integer DEFAULT NULL,
  import_key varchar(14) DEFAULT NULL,
  extraparams varchar(255) DEFAULT NULL
) ENGINE=innodb;

CREATE TABLE llx_askpricesupplierdet (
  rowid integer NOT NULL AUTO_INCREMENT PRIMARY KEY,
  fk_askpricesupplier integer NOT NULL,
  fk_parent_line integer DEFAULT NULL,
  fk_product integer DEFAULT NULL,
  label varchar(255) DEFAULT NULL,
  description text,
  fk_remise_except integer DEFAULT NULL,
  tva_tx double(6,3) DEFAULT 0,
  localtax1_tx double(6,3) DEFAULT 0,
  localtax1_type varchar(10) DEFAULT NULL,
  localtax2_tx double(6,3) DEFAULT 0,
  localtax2_type varchar(10) DEFAULT NULL,
  qty double DEFAULT NULL,
  remise_percent double DEFAULT '0',
  remise double DEFAULT '0',
  price double DEFAULT NULL,
  subprice double(24,8) DEFAULT 0,
  total_ht double(24,8) DEFAULT 0,
  total_tva double(24,8) DEFAULT 0,
  total_localtax1 double(24,8) DEFAULT 0,
  total_localtax2 double(24,8) DEFAULT 0,
  total_ttc double(24,8) DEFAULT 0,
  product_type integer DEFAULT 0,
  info_bits integer DEFAULT 0,
  buy_price_ht double(24,8) DEFAULT 0,
  fk_product_fournisseur_price integer DEFAULT NULL,
  special_code integer DEFAULT 0,
  rang integer DEFAULT 0,
  ref_fourn varchar(30) DEFAULT NULL
) ENGINE=innodb;

CREATE TABLE llx_askpricesupplier_extrafields (
  rowid integer AUTO_INCREMENT PRIMARY KEY,
  tms timestamp,
  fk_object integer NOT NULL,
  import_key varchar(14) DEFAULT NULL
) ENGINE=innodb;

CREATE TABLE llx_askpricesupplierdet_extrafields (
  rowid integer AUTO_INCREMENT PRIMARY KEY,
  tms timestamp,
  fk_object integer NOT NULL,
  import_key varchar(14) DEFAULT NULL
) ENGINE=innodb;
-- End Module AskPriceSupplier --


ALTER TABLE llx_societe ADD COLUMN fk_incoterms integer;
ALTER TABLE llx_societe ADD COLUMN location_incoterms varchar(255);
ALTER TABLE llx_propal ADD COLUMN fk_incoterms integer;
ALTER TABLE llx_propal ADD COLUMN location_incoterms varchar(255);
ALTER TABLE llx_commande ADD COLUMN fk_incoterms integer;
ALTER TABLE llx_commande ADD COLUMN location_incoterms varchar(255);
ALTER TABLE llx_commande_fournisseur ADD COLUMN fk_incoterms integer;
ALTER TABLE llx_commande_fournisseur ADD COLUMN location_incoterms varchar(255);
ALTER TABLE llx_facture ADD COLUMN fk_incoterms integer;
ALTER TABLE llx_facture ADD COLUMN location_incoterms varchar(255);
ALTER TABLE llx_facture_fourn ADD COLUMN fk_incoterms integer;
ALTER TABLE llx_facture_fourn ADD COLUMN location_incoterms varchar(255);
ALTER TABLE llx_expedition ADD COLUMN fk_incoterms integer;
ALTER TABLE llx_expedition ADD COLUMN location_incoterms varchar(255);
ALTER TABLE llx_livraison ADD COLUMN 	fk_incoterms integer;
ALTER TABLE llx_livraison ADD COLUMN 	location_incoterms varchar(255);

CREATE TABLE llx_c_incoterms (
  rowid integer AUTO_INCREMENT PRIMARY KEY,
  code varchar(3) NOT NULL,
  libelle varchar(255) NOT NULL,
  active tinyint DEFAULT 1  NOT NULL
) ENGINE=innodb;

ALTER TABLE llx_c_incoterms ADD UNIQUE INDEX uk_c_incoterms (code);

INSERT INTO llx_c_incoterms (code, libelle, active) VALUES ('EXW', 'Ex Works, au départ non chargé, non dédouané sortie d''usine (uniquement adapté aux flux domestiques, nationaux)', 1);
INSERT INTO llx_c_incoterms (code, libelle, active) VALUES ('FCA', 'Free Carrier, marchandises dédouanées et chargées dans le pays de départ, chez le vendeur ou chez le commissionnaire de transport de l''acheteur', 1);
INSERT INTO llx_c_incoterms (code, libelle, active) VALUES ('FAS', 'Free Alongside Ship, sur le quai du port de départ', 1);
INSERT INTO llx_c_incoterms (code, libelle, active) VALUES ('FOB', 'Free On Board, chargé sur le bateau, les frais de chargement dans celui-ci étant fonction du liner term indiqué par la compagnie maritime (à la charge du vendeur)', 1);
INSERT INTO llx_c_incoterms (code, libelle, active) VALUES ('CFR', 'Cost and Freight, chargé dans le bateau, livraison au port de départ, frais payés jusqu''au port d''arrivée, sans assurance pour le transport, non déchargé du navire à destination (les frais de déchargement sont inclus ou non au port d''arrivée)', 1);
INSERT INTO llx_c_incoterms (code, libelle, active) VALUES ('CIF', 'Cost, Insurance and Freight, chargé sur le bateau, frais jusqu''au port d''arrivée, avec l''assurance marchandise transportée souscrite par le vendeur pour le compte de l''acheteur', 1);
INSERT INTO llx_c_incoterms (code, libelle, active) VALUES ('CPT', 'Carriage Paid To, livraison au premier transporteur, frais jusqu''au déchargement du mode de transport, sans assurance pour le transport', 1);
INSERT INTO llx_c_incoterms (code, libelle, active) VALUES ('CIP', 'Carriage and Insurance Paid to, idem CPT, avec assurance marchandise transportée souscrite par le vendeur pour le compte de l''acheteur', 1);
INSERT INTO llx_c_incoterms (code, libelle, active) VALUES ('DAT', 'Delivered At Terminal, marchandises (déchargées) livrées sur quai, dans un terminal maritime, fluvial, aérien, routier ou ferroviaire désigné (dédouanement import, et post-acheminement payés par l''acheteur)', 1);
INSERT INTO llx_c_incoterms (code, libelle, active) VALUES ('DAP', 'Delivered At Place, marchandises (non déchargées) mises à disposition de l''acheteur dans le pays d''importation au lieu précisé dans le contrat (déchargement, dédouanement import payé par l''acheteur)', 1);
INSERT INTO llx_c_incoterms (code, libelle, active) VALUES ('DDP', 'Delivered Duty Paid, marchandises (non déchargées) livrées à destination finale, dédouanement import et taxes à la charge du vendeur ; l''acheteur prend en charge uniquement le déchargement (si exclusion des taxes type TVA, le préciser clairement)', 1);

-- Extrafields fk_object must be unique (1-1 relation)
ALTER TABLE llx_societe_extrafields DROP INDEX idx_societe_extrafields;
ALTER TABLE llx_societe_extrafields ADD UNIQUE INDEX uk_societe_extrafields (fk_object);
