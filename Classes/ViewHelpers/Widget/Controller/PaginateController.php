<?php
namespace JWeiland\Masterplan\ViewHelpers\Widget\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Stefan Froemken <projects@jweiland.net>, jweiland.net
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use TYPO3\CMS\Core\Utility\ArrayUtility;

/**
 * @package masterplan
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PaginateController extends \TYPO3\CMS\Fluid\Core\Widget\AbstractWidgetController {

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper
	 * @inject
	 */
	protected $dataMapper;

	/**
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * Contains the settings of the current extension
	 *
	 * @var array
	 */
	protected $settings;

	/**
	 * @var array
	 */
	protected $configuration = array('itemsPerPage' => 15, 'insertAbove' => TRUE, 'insertBelow' => FALSE, 'maximumNumberOfLinks' => 5 );

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
	 */
	protected $objects;

	/**
	 * @var integer
	 */
	protected $currentPage = 1;

	/**
	 * @var integer
	 */
	protected $maximumNumberOfLinks = 99;

	/**
	 * @var string
	 */
	protected $originalStatement = '';

	/**
	 * @var integer
	 */
	protected $numberOfPages = 0;

	/**
	 * @var integer
	 */
	protected $displayRangeStart = 0;

	/**
	 * @var integer
	 */
	protected $displayRangeEnd = 0;





	/**
	 * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
		$this->settings = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);
	}

	/**
	 * @return void
	 */
	public function initializeAction() {
		$this->objects = $this->widgetConfiguration['objects'];
		$this->originalStatement = $this->objects->getQuery()->getStatement()->getStatement();
		ArrayUtility::mergeRecursiveWithOverrule($this->configuration, $this->widgetConfiguration['configuration'], TRUE);
		$this->numberOfPages = ceil($this->getCount() / (integer) $this->configuration['itemsPerPage']);
		$this->maximumNumberOfLinks = (integer) $this->configuration['maximumNumberOfLinks'];
	}

	/**
	 * @param integer $currentPage
	 * @return void
	 */
	public function indexAction($currentPage = 1) {
		// set current page
		$this->currentPage = (integer)$currentPage;
		if ($this->currentPage < 1) {
			$this->currentPage = 1;
		}

		if ($this->currentPage > $this->numberOfPages) {
			// set $modifiedObjects to NULL if the page does not exist
			$modifiedObjects = NULL;
		} else {
			// modify query
			$limit = (integer) $this->configuration['itemsPerPage'];
			if (!empty($this->widgetConfiguration['maxRecords']) && $this->widgetConfiguration['maxRecords'] < $limit) {
				$limit = $this->widgetConfiguration['maxRecords'];
			}
			$offset = 0;
			if ($this->currentPage > 1) {
				$offset = (integer) ($limit * ($this->currentPage - 1));
			}
			$modifiedObjects = $this->getModifiedObjects($limit, $offset);
		}

		$this->view->assign('contentArguments', array(
			$this->widgetConfiguration['as'] => $modifiedObjects
		));
		$this->view->assign('configuration', $this->configuration);
		$this->view->assign('pagination', $this->buildPagination());
	}

	/**
	 * If a certain number of links should be displayed, adjust before and after
	 * amounts accordingly.
	 *
	 * @return void
	 */
	protected function calculateDisplayRange() {
		$maximumNumberOfLinks = $this->maximumNumberOfLinks;
		if ($maximumNumberOfLinks > $this->numberOfPages) {
			$maximumNumberOfLinks = $this->numberOfPages;
		}
		$delta = floor($maximumNumberOfLinks / 2);
		$this->displayRangeStart = $this->currentPage - $delta;
		$this->displayRangeEnd = $this->currentPage + $delta + ($maximumNumberOfLinks % 2 === 0 ? 1 : 0);
		if ($this->displayRangeStart < 1) {
			$this->displayRangeEnd -= $this->displayRangeStart - 1;
		}
		if ($this->displayRangeEnd > $this->numberOfPages) {
			$this->displayRangeStart -= ($this->displayRangeEnd - $this->numberOfPages);
		}
		$this->displayRangeStart = (integer) max($this->displayRangeStart, 1);
		$this->displayRangeEnd = (integer) min($this->displayRangeEnd, $this->numberOfPages);
	}

	/**
	 * Returns an array with the keys "pages", "current", "numberOfPages", "nextPage" & "previousPage"
	 *
	 * @return array
	 */
	protected function buildPagination() {
		$this->calculateDisplayRange();
		$pages = array();
		for ($i = $this->displayRangeStart; $i <= $this->displayRangeEnd; $i++) {
			$pages[] = array('number' => $i, 'isCurrent' => ($i === $this->currentPage));
		}
		$pagination = array(
			'pages' => $pages,
			'current' => $this->currentPage,
			'numberOfPages' => $this->numberOfPages,
			'displayRangeStart' => $this->displayRangeStart,
			'displayRangeEnd' => $this->displayRangeEnd,
			'hasLessPages' => $this->displayRangeStart > 2,
			'hasMorePages' => $this->displayRangeEnd + 1 < $this->numberOfPages
		);
		if ($this->currentPage < $this->numberOfPages) {
			$pagination['nextPage'] = $this->currentPage + 1;
		}
		if ($this->currentPage > 1) {
			$pagination['previousPage'] = $this->currentPage - 1;
		}
		return $pagination;
	}

	/**
	 * get amount of rows
	 *
	 * @return integer
	 */
	protected function getCount() {
		$amountOfRows = 0;
		if ($this->widgetConfiguration['maxRecords']) {
			return (int) $this->widgetConfiguration['maxRecords'];
		} else {
			$this->modifyStatementToCount();
			$rows = $this->objects->getQuery()->execute(TRUE);
			foreach ($rows as $row) {
				$amountOfRows += current($row);
			}
			return $amountOfRows;
		}
	}

	/**
	 * get modified objects
	 *
	 * @param integer $limit
	 * @param integer $offset
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
	 */
	protected function getModifiedObjects($limit = 0, $offset = 0) {
		$this->modifyStatementToSelect($limit, $offset);
		$records = $this->objects->getQuery()->execute(TRUE);

		// As long as domain models will be cached by their UID, we have to create our own event storage
		/** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $projectStorage */
		$projectStorage = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage');
		/** @var \JWeiland\Masterplan\Domain\Model\Project $project */
		foreach ($records as $record) {
			list($project) = $this->dataMapper->map('JWeiland\\Masterplan\\Domain\\Model\\Project', array($record));
			$projectStorage->attach(clone $project);
		}
		return $projectStorage;

	}

	/**
	 * modify statement to count results
	 *
	 * @return void
	 */
	protected function modifyStatementToCount() {
		$statement = str_replace('###SELECT###', 'COUNT(*)', $this->originalStatement);
		$statement = str_replace('###LIMIT###', '', $statement);
		$boundVariables = $this->objects->getQuery()->getStatement()->getBoundVariables();
		$this->objects = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\QueryResult', $this->objects->getQuery()->statement($statement, $boundVariables));
	}

	/**
	 * modify statement to select results
	 *
	 * @param integer $limit
	 * @param integer $offset
	 * @return void
	 */
	protected function modifyStatementToSelect($limit = 0, $offset = 0) {
		$select = 'tx_masterplan_domain_model_project.*';
		$statement = str_replace('###SELECT###', $select, $this->originalStatement);
		if ($limit) {
			$statement = str_replace('###LIMIT###', 'LIMIT ' . $offset . ',' . $limit, $statement);
		} else $statement = str_replace('###LIMIT###', '', $statement);
		$boundVariables = $this->objects->getQuery()->getStatement()->getBoundVariables();
		$this->objects = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\QueryResult', $this->objects->getQuery()->statement($statement, $boundVariables));
	}

}
