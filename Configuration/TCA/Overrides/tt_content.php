<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'Masterplan',
    'Masterplan',
    'LLL:EXT:masterplan/Resources/Private/Language/locallang_db.xlf:plugin.masterplan.title'
);
