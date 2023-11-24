<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(static function () {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Masterplan',
        'Masterplan',
        [
            \JWeiland\Masterplan\Controller\ProjectController::class => 'list, show',
            \JWeiland\Masterplan\Controller\LocationController::class => 'show'
        ],
        // non-cacheable actions
        [
            'Project' => '',
        ]
    );

    // Register SVG Icon Identifier
    $svgIcons = [
        'ext-masterplan-masterplan-wizard-icon' => 'plugin_wizard.svg',
    ];
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \TYPO3\CMS\Core\Imaging\IconRegistry::class
    );
    foreach ($svgIcons as $identifier => $fileName) {
        $iconRegistry->registerIcon(
            $identifier,
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:masterplan/Resources/Public/Icons/' . $fileName]
        );
    }

    // Add masterplan plugin to new element wizard
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:masterplan/Configuration/TSconfig/ContentElementWizard.txt">'
    );

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['masterplanUpdateSlug']
        = \JWeiland\Masterplan\Updater\MasterplanSlugUpdater::class;
});
