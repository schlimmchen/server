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

use OCA\DAV\CalDAV\Trashbin\TrashbinSupport;
use OCP\IConfig;
use OCP\IL10N;
use Sabre\DAV\Exception\Forbidden;
use Sabre\DAV\Exception\NotFound;
use Sabre\DAV\ICollection;
use Sabre\DAV\INode;
use function in_array;

class TrashbinHome implements ICollection {

	public const NAME = 'trashbin';

	/** @var TrashbinSupport */
	private $caldavBackend;

	/** @var array */
	private $principalInfo;

	public function __construct(TrashbinSupport $caldavBackend,
								array $principalInfo) {
		$this->caldavBackend = $caldavBackend;
		$this->principalInfo = $principalInfo;
	}

	public function createFile($name, $data = null) {
		throw new Forbidden('Permission denied to create files in the trashbin');
	}

	public function createDirectory($name) {
		throw new Forbidden('Permission denied to create a directory in the trashbin');
	}

	public function getChild($name): INode {
		switch ($name) {
			case RestoreTarget::NAME:
				return new RestoreTarget(
					$this->caldavBackend
				);
			case DeletedCalendarsCollection::NAME:
				return new DeletedCalendarsCollection(
					$this->caldavBackend,
					$this->principalInfo
				);
			case DeletedCalendarObjectsCollection::NAME:
				return new DeletedCalendarObjectsCollection(
					$this->caldavBackend,
					$this->principalInfo
				);
		}

		throw new NotFound();
	}

	public function getChildren(): array {
		return [
			new RestoreTarget(
				$this->caldavBackend
			),
			new DeletedCalendarsCollection(
				$this->caldavBackend,
				$this->principalInfo
			),
			new DeletedCalendarObjectsCollection(
				$this->caldavBackend,
				$this->principalInfo
			),
		];
	}

	public function childExists($name): bool {
		return in_array($name, [
			RestoreTarget::NAME,
			DeletedCalendarsCollection::NAME,
			DeletedCalendarObjectsCollection::NAME,
		], true);
	}

	public function delete() {
		throw new Forbidden('Permission denied to delete the trashbin');
	}

	public function getName(): string {
		return self::NAME;
	}

	public function setName($name) {
		throw new Forbidden('Permission denied to rename the trashbin');
	}

	public function getLastModified(): int {
		return 0;
	}
}
