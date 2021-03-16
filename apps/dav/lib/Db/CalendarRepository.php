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

namespace OCA\DAV\Db;

use OCP\IDBConnection;
use Psr\Log\LoggerInterface;
use function sprintf;

class CalendarRepository {

	private const CALENDAR_TABLE = 'calendars';
	private const CALENDAR_OBJECTS_TABLE = 'calendarobjects';

	/** @var IDBConnection */
	private $db;

	/** @var LoggerInterface */
	private $logger;

	public function __construct(IDBConnection $db,
								LoggerInterface $logger) {
		$this->db = $db;
		$this->logger = $logger;
	}

	public function deleteExpiredCalendarsAndObjects(int $olderThan): void {
		$qb = $this->db->getQueryBuilder();
		$deleteCalendars = $qb->delete(self::CALENDAR_TABLE)
			->where(
				$qb->expr()->isNotNull('deleted_at'),
				$qb->expr()->lte('deleted_at', $qb->createNamedParameter($olderThan))
			);
		$numCalendars = $deleteCalendars->executeUpdate();
		$this->logger->debug(sprintf("Cleaned up %d calendars that were deleted before %d",
			$numCalendars,
			$olderThan
		));

		$qb = $this->db->getQueryBuilder();
		$deleteCalendarObjects = $qb->delete(self::CALENDAR_OBJECTS_TABLE)
			->where(
				$qb->expr()->isNotNull('deleted_at'),
				$qb->expr()->lte('deleted_at', $qb->createNamedParameter($olderThan))
			);
		$numObjects = $deleteCalendarObjects->executeUpdate();
		$this->logger->debug(sprintf("Cleaned up %d calendar objects that were deleted before %d",
			$numObjects,
			$olderThan
		));
	}
}
