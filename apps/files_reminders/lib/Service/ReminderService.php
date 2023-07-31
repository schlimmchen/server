<?php

declare(strict_types=1);

/**
 * @copyright 2023 Christopher Ng <chrng8@gmail.com>
 *
 * @author Christopher Ng <chrng8@gmail.com>
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\FilesReminders\Service;

use DateTime;
use DateTimeZone;
use OCA\FilesReminders\AppInfo\Application;
use OCA\FilesReminders\Db\Reminder;
use OCA\FilesReminders\Db\ReminderMapper;
use OCA\FilesReminders\Exception\UserNotFoundException;
use OCA\FilesReminders\Model\RichReminder;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\Files\IRootFolder;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\IUserManager;
use OCP\Notification\IManager as INotificationManager;
use Psr\Log\LoggerInterface;
use Throwable;

class ReminderService {
	public function __construct(
		protected IUserManager $userManager,
		protected IURLGenerator $urlGenerator,
		protected INotificationManager $notificationManager,
		protected ReminderMapper $reminderMapper,
		protected IRootFolder $root,
		protected LoggerInterface $logger,
	) {}

	public function get(int $id): RichReminder {
		$reminder = $this->reminderMapper->find($id);
		return new RichReminder($reminder, $this->root);
	}

	/**
	 * @throws DoesNotExistException
	 */
	public function getDueForUser(IUser $user, int $fileId): RichReminder {
		$reminder = $this->reminderMapper->findDueForUser($user, $fileId);
		return new RichReminder($reminder, $this->root);
	}

	/**
	 * @return RichReminder[]
	 */
	public function getAll(?IUser $user = null) {
		$reminders = ($user !== null)
			? $this->reminderMapper->findAllForUser($user)
			: $this->reminderMapper->findAll();
		return array_map(
			fn (Reminder $reminder) => new RichReminder($reminder, $this->root),
			$reminders,
		);
	}

	public function createOrUpdate(IUser $user, int $fileId, DateTime $dueDate): void {
		$now = new DateTime('now', new DateTimeZone('UTC'));
		try {
			$reminder = $this->reminderMapper->findDueForUser($user, $fileId);
			$reminder->setDueDate($dueDate);
			$reminder->setUpdatedAt($now);
			$this->reminderMapper->update($reminder);
		} catch (DoesNotExistException $e) {
			// Create new reminder if no reminder is found
			$reminder = new Reminder();
			$reminder->setUserId($user->getUID());
			$reminder->setFileId($fileId);
			$reminder->setDueDate($dueDate);
			$reminder->setUpdatedAt($now);
			$reminder->setCreatedAt($now);
			$this->reminderMapper->insert($reminder);
		}
	}

	/**
	 * @throws DoesNotExistException
	 */
	public function remove(IUser $user, int $fileId): void {
		$reminder = $this->reminderMapper->findDueForUser($user, $fileId);
		$this->reminderMapper->delete($reminder);
	}

	/**
	 * @throws DoesNotExistException
	 * @throws UserNotFoundException
	 */
	public function send(Reminder $reminder): void {
		if ($reminder->getNotified()) {
			return;
		}

		$user = $this->userManager->get($reminder->getUserId());
		if ($user === null) {
			throw new UserNotFoundException();
		}

		$notification = $this->notificationManager->createNotification();
		$notification
			->setApp(Application::APP_ID)
			->setIcon($this->urlGenerator->getAbsoluteURL($this->urlGenerator->imagePath('files', 'folder.svg')))
			->setUser($user->getUID())
			->setObject('reminder', (string)$reminder->getId())
			->setSubject('reminder-due')
			->setDateTime($reminder->getDueDate());

		try {
			$this->notificationManager->notify($notification);
			$this->reminderMapper->markNotified($reminder);
		} catch (Throwable $th) {
			$this->logger->error($th->getMessage(), $th->getTrace());
		}
	}

	public function cleanUp(?int $limit = null): void {
		$reminders = $this->reminderMapper->findNotified($limit);
		foreach ($reminders as $reminder) {
			$this->reminderMapper->delete($reminder);
		}
	}
}
