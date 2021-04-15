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

use Sabre\CalDAV\ICalendarObject;
use Sabre\DAV\Exception\Forbidden;

class DeletedCalendarObject implements ICalendarObject {

	/** @var string */
	private $name;

	/** @var mixed[] */
	private $objectData;

	public function __construct(string $name,
								array $objectData) {
		$this->name = $name;
		$this->objectData = $objectData;
	}

	public function delete() {
		throw new Forbidden();
	}

	public function getId(): int {
		return (int) $this->objectData['id'];
	}

	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		throw new Forbidden();
	}

	public function getLastModified() {
		return 0;
	}

	public function put($data) {
		throw new Forbidden();
	}

	public function get() {
		return $this->objectData['calendardata'];
	}

	public function getContentType() {
		$mime = 'text/calendar; charset=utf-8';
		if (isset($this->objectData['component']) && $this->objectData['component']) {
			$mime .= '; component='.$this->objectData['component'];
		}

		return $mime;
	}

	public function getETag() {
		return $this->objectData['etag'];
	}

	public function getSize() {
		return (int) $this->objectData['size'];
	}
}
