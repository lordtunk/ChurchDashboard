ALTER TABLE Users ADD `is_user_admin` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE Users ADD `is_site_admin` tinyint(1) NOT NULL DEFAULT '0';

ALTER TABLE `People` DROP ` first_visit `;