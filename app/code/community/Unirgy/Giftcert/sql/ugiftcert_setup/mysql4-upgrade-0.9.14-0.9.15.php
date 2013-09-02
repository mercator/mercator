<?php

$this->startSetup();

try {
    $this->run("ALTER TABLE {$this->getTable('ugiftcert_history')} DROP FOREIGN KEY  `FK_ugiftcert_history_order`");
} catch (Exception $e) {
    // already deleted, ignore
}
try {
    $this->run("ALTER TABLE {$this->getTable('ugiftcert_history')} DROP FOREIGN KEY  `FK_ugiftcert_history_order_item`");
} catch (Exception $e) {
    // already deleted, ignore
}

$this->endSetup();