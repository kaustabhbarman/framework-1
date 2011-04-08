ALTER TABLE `documents`
    DROP COLUMN `server_date_unlocking`,
    ADD COLUMN `server_date_deleted` VARCHAR(50) NULL COMMENT 'Date of deletion, if server_state = delete (is generated by the system).' AFTER `server_date_published`,
    MODIFY COLUMN `server_state` ENUM('published', 'restricted', 'inprogress', 'unpublished', 'deleted', 'temporary') NOT NULL COMMENT 'Status of publication process in the repository.';

ALTER TABLE `document_identifiers`
    MODIFY COLUMN `type` ENUM('doi', 'handle', 'urn', 'std-doi', 'url', 'cris-link', 'splash-url', 'isbn', 'issn', 'opus3-id', 'opac-id', 'uuid', 'serial', 'old', 'pmid', 'arxiv') NOT NULL COMMENT 'Type of the identifier.' ,
    ADD INDEX `fk_document_identifiers_documents_type` (`document_id` ASC, `type` ASC);

ALTER TABLE `document_files`
    ADD COLUMN `comment` TEXT NULL COMMENT 'Comment for a file.',
    ADD COLUMN `embargo_date` VARCHAR(50) NULL COMMENT 'Embargo date of file, after which it will be publicly available.';

ALTER TABLE `document_references`
    MODIFY COLUMN `type` ENUM('doi', 'handle', 'urn', 'std-doi', 'url', 'cris-link', 'splash-url', 'isbn', 'issn', 'opus4-id') NOT NULL COMMENT 'Type of the identifier.' ,
    ADD COLUMN `relation` ENUM('referenced-by', 'updated-by') COMMENT 'Describes the type of the relation.';

ALTER TABLE `dnb_institutes`
    MODIFY COLUMN `is_grantor` TINYINT (1) NOT NULL DEFAULT 0 COMMENT 'Flag: is the institution grantor of academic degrees?' ,
    ADD COLUMN `is_publisher` TINYINT (1) NOT NULL DEFAULT 0 COMMENT 'Flag: is the institution of academic theses?';








