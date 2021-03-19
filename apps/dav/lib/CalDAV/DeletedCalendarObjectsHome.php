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

use Sabre\CalDAV\Calendar;
use Sabre\CalDAV\CalendarHome;
use function array_map;

class DeletedCalendarObjectsHome extends CalendarHome {
	/** @var CalDavBackend */
	protected $customCaldavBackend;

	public function __construct(CalDavBackend $caldavBackend, array $principalInfo) {
		parent::__construct($caldavBackend, $principalInfo);
		$this->customCaldavBackend = $caldavBackend;
	}

	public function getChildren() {
		return array_map(function (array $calendarInfo) {
			return new Calendar($this->caldavBackend, $calendarInfo);
		}, $this->customCaldavBackend->getDeletedCalendarsForUser($this->principalInfo['uri']));
	}
}
