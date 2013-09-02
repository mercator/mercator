<?php

$this->startSetup();

try {
    $this->run("ALTER TABLE {$this->getTable('ugiftcert_cert')} ADD `pos_number` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;");
} catch (Exception $e) {
    // column already exists, ignore
}

$this->endSetup();