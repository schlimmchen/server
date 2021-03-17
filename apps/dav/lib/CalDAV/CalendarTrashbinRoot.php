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

use Sabre\CalDAV\CalendarRoot;
use Sabre\DAVACL\PrincipalBackend\BackendInterface as PrincipalBackend;

class CalendarTrashbinRoot extends CalendarRoot {

	/** @var CalDavBackend */
	protected $customCaldavBackend;

	public function __construct(PrincipalBackend $principalBackend,
								CalDavBackend $caldavBackend,
								$principalPrefix = 'principals') {
		parent::__construct($principalBackend, $caldavBackend, $principalPrefix);

		$this->customCaldavBackend = $caldavBackend;
	}

	public function getChildForPrincipal(array $principal) {
		return new CalendarTrashbinHome($this->customCaldavBackend, $principal);
	}

	public function getName() {
		return 'calendars_trashbin';
	}
}
