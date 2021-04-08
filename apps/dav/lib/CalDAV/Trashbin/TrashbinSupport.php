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

use OCA\DAV\CalDAV\CalDavBackend;
use OCA\DAV\CalDAV\Calendar;
use OCA\DAV\CalDAV\CalendarObject;
use OCP\DB\QueryBuilder\IQueryBuilder;
use Sabre\CalDAV\Backend\BackendInterface;
use Sabre\DAV\INode;

interface TrashbinSupport extends BackendInterface {
	/**
	 * @param string $principalUri
	 *
	 * @return mixed[]
	 */
	public function getDeletedCalendarsForUser(string $principalUri): array;

	/**
	 * @param string $principalUri
	 *
	 * @return mixed[]
	 */
	public function getDeletedCalendarObjects(string $principalUri): array;

	/**
	 * @param INode $node
	 *
	 * @return bool whether the node could be restored
	 */
	public function restore(INode $node): bool;
}
