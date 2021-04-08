<?php

declare(strict_types=1);

/*
 * @copyright 2021 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2021 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace OCA\DAV\CalDAV\Trashbin;

use OCA\DAV\CalDAV\Calendar;
use OCP\IConfig;
use OCP\IL10N;
use Sabre\DAV\Collection;
use Sabre\DAV\INode;
use function array_map;
use function array_merge;

class Trashbin extends Collection {

	public const NAME = 'trash';

	/** @var TrashbinSupport */
	private $caldavBackend;

	/** @var IL10N */
	private $l10n;

	/** @var IConfig */
	private $config;

	/** @var string */
	private $principalUri;

	public function __construct(TrashbinSupport $caldavBackend,
								IL10N $l10n,
								IConfig $config,
								string $principalUri) {
		$this->caldavBackend = $caldavBackend;
		$this->principalUri = $principalUri;
		$this->l10n = $l10n;
		$this->config = $config;
	}

	/**
	 * @return INode[]
	 */
	public function getChildren(): array {
		$calendarInfos = $this->caldavBackend->getDeletedCalendarsForUser($this->principalUri);
		$calendars = array_map(function(array $calendarInfo): INode {
			return new Calendar(
				$this->caldavBackend,
				$calendarInfo,
				$this->l10n,
				$this->config
			);
		}, $calendarInfos);

		$objectInfos = $this->caldavBackend->getDeletedCalendarObjects($this->principalUri);
		/*$objects = array_map(function(array $objectInfo): INode {
			return new CalendarObject(
				$this->caldavBackend,
				$this->l10n,
				[],
				$objectInfo
			);
		}, $objectInfos);*/
		$objects = [];

		return array_merge(
			$calendars,
			$objects,
		);
	}

	public function getName(): string {
		return self::NAME;
	}
}
