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
use OCP\IConfig;
use OCP\IL10N;
use Sabre\DAV\Exception\Forbidden;
use Sabre\DAV\Exception\NotFound;
use Sabre\DAV\ICollection;
use function array_map;

class DeletedCalendarsCollection implements ICollection {

	public const NAME = 'calendars';

	/** @var CalDavBackend */
	protected $caldavBackend;

	/** @var mixed[] */
	private $principalInfo;

	public function __construct(CalDavBackend $caldavBackend,
								array $principalInfo) {
		$this->caldavBackend = $caldavBackend;
		$this->principalInfo = $principalInfo;
	}

	public function getChildren() {
		return array_map(function (array $calendarInfo) {
			return new DeletedCalendar(
				$calendarInfo['uri'],
			);
		}, $this->caldavBackend->getDeletedCalendarsForUser($this->principalInfo['uri']));
	}

	public function getChild($uri) {
		$data = $this->caldavBackend->getCalendarByUri(
			$this->principalInfo['uri'],
			$uri,
		);

		if ($data === false) {
			throw new NotFound();
		}

		return new DeletedCalendar(
			$data['uri'],
		);
	}

	public function createFile($name, $data = null) {
		throw new Forbidden();
	}

	public function createDirectory($name) {
		throw new Forbidden();
	}

	public function childExists($name) {
		try {
			$this->getChild($name);
		} catch (NotFound $e) {
			return false;
		}

		return true;
	}

	public function delete() {
		throw new Forbidden();
	}

	public function getName(): string {
		return self::NAME;
	}

	public function setName($name) {
		throw new Forbidden();
	}

	public function getLastModified(): int {
		return 0;
	}
}
