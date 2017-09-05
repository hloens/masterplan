<?php
$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['masterplan']);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::makeCategorizable(
    'masterplan',
    'tx_masterplan_domain_model_project',
    'area_of_activity',
    array(
        'label' => 'LLL:EXT:masterplan/Resources/Private/Language/locallang_db.xlf:tx_masterplan_domain_model_project.area_of_activity',
        'fieldConfiguration' => array(
            'treeConfig' => array(
                'rootUid' => $extConf['rootCategory']
            )
        )
    )
);