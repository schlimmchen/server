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

namespace OCA\DAV\CalDAV;

use OCP\IConfig;
use OCP\IL10N;
use Sabre\CalDAV\CalendarHome;
use Sabre\DAV\Exception\NotFound;
use function array_map;

class DeletedCalendarsHome extends CalendarHome {
	/** @var CalDavBackend */
	protected $customCaldavBackend;

	/** @var IL10N */
	private $l10n;

	/** @var IConfig */
	private $config;

	public function __construct(CalDavBackend $caldavBackend,
								IL10N $l10n,
								IConfig $config,
								array $principalInfo) {
		parent::__construct($caldavBackend, $principalInfo);
		$this->customCaldavBackend = $caldavBackend;
		$this->l10n = $l10n;
		$this->config = $config;
	}

	public function getChildren() {
		return array_map(function (array $calendarInfo) {
			return new Calendar(
				$this->caldavBackend,
				$calendarInfo,
				$this->l10n,
				$this->config
			);
		}, $this->customCaldavBackend->getDeletedCalendarsForUser($this->principalInfo['uri']));
	}

	public function getChild($uri) {
		$data = $this->customCaldavBackend->getCalendarByUri(
			$this->principalInfo['uri'],
			$uri,
		);

		if ($data === false) {
			throw new NotFound();
		}

		return new Calendar(
			$this->caldavBackend,
			$data,
			$this->l10n,
			$this->config
		);
	}
}
