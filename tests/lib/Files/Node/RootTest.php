<?php
/**
 * Copyright (c) 2013 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace Test\Files\Node;

use OC\Files\FileInfo;
use OC\Files\Mount\Manager;
use OC\User\NoUserException;

class RootTest extends \Test\TestCase {
	/** @var \OC\User\User */
	private $user;

	/** @var \OC\Files\Mount\Manager */
	private $manager;
	/** @var \OCP\Files\Config\IUserMountCache|\PHPUnit_Framework_MockObject_MockObject */
	private $userMountCache;

	protected function setUp() {
		parent::setUp();

		$config = $this->getMockBuilder('\OCP\IConfig')
			->disableOriginalConstructor()
			->getMock();
		$urlgenerator = $this->getMockBuilder('\OCP\IURLGenerator')
			->disableOriginalConstructor()
			->getMock();

		$this->user = new \OC\User\User('', new \Test\Util\User\Dummy, null, $config, $urlgenerator);

		$this->manager = $this->getMockBuilder('\OC\Files\Mount\Manager')
			->disableOriginalConstructor()
			->getMock();
		$this->userMountCache = $this->getMockBuilder('\OCP\Files\Config\IUserMountCache')
			->disableOriginalConstructor()
			->getMock();
	}

	protected function getFileInfo($data) {
		return new FileInfo('', null, '', $data, null);
	}

	public function testGet() {
		/**
		 * @var \OC\Files\Storage\Storage $storage
		 */
		$storage = $this->getMockBuilder('\OC\Files\Storage\Storage')
			->disableOriginalConstructor()
			->getMock();
		/**
		 * @var \OC\Files\View | \PHPUnit_Framework_MockObject_MockObject $view
		 */
		$view = $this->getMockBuilder('\OC\Files\View')
			->disableOriginalConstructor()
			->getMock();
		$root = new \OC\Files\Node\Root($this->manager, $view, $this->user, $this->userMountCache);

		$view->expects($this->once())
			->method('getFileInfo')
			->with('/bar/foo')
			->will($this->returnValue($this->getFileInfo(array('fileid' => 10, 'path' => 'bar/foo', 'name', 'mimetype' => 'text/plain'))));

		$root->mount($storage, '');
		$node = $root->get('/bar/foo');
		$this->assertEquals(10, $node->getId());
		$this->assertInstanceOf('\OC\Files\Node\File', $node);
	}

	/**
	 * @expectedException \OCP\Files\NotFoundException
	 */
	public function testGetNotFound() {
		/**
		 * @var \OC\Files\Storage\Storage $storage
		 */
		$storage = $this->getMockBuilder('\OC\Files\Storage\Storage')
			->disableOriginalConstructor()
			->getMock();
		/**
		 * @var \OC\Files\View | \PHPUnit_Framework_MockObject_MockObject $view
		 */
		$view = $this->getMockBuilder('\OC\Files\View')
			->disableOriginalConstructor()
			->getMock();
		$root = new \OC\Files\Node\Root($this->manager, $view, $this->user, $this->userMountCache);

		$view->expects($this->once())
			->method('getFileInfo')
			->with('/bar/foo')
			->will($this->returnValue(false));

		$root->mount($storage, '');
		$root->get('/bar/foo');
	}

	/**
	 * @expectedException \OCP\Files\NotPermittedException
	 */
	public function testGetInvalidPath() {
		/**
		 * @var \OC\Files\View | \PHPUnit_Framework_MockObject_MockObject $view
		 */
		$view = $this->getMockBuilder('\OC\Files\View')
			->disableOriginalConstructor()
			->getMock();
		$root = new \OC\Files\Node\Root($this->manager, $view, $this->user, $this->userMountCache);

		$root->get('/../foo');
	}

	/**
	 * @expectedException \OCP\Files\NotFoundException
	 */
	public function testGetNoStorages() {
		/**
		 * @var \OC\Files\View | \PHPUnit_Framework_MockObject_MockObject $view
		 */
		$view = $this->getMockBuilder('\OC\Files\View')
			->disableOriginalConstructor()
			->getMock();
		$root = new \OC\Files\Node\Root($this->manager, $view, $this->user, $this->userMountCache);

		$root->get('/bar/foo');
	}

	public function testGetUserFolder() {
		$this->logout();
		$manager = new Manager();
		/**
		 * @var \OC\Files\View | \PHPUnit_Framework_MockObject_MockObject $view
		 */
		$view = new \OC\Files\View();

		$user1 = $this->getUniqueID('user1_');
		$user2 = $this->getUniqueID('user2_');

		\OC_User::clearBackends();
		// needed for loginName2UserName mapping
		$userBackend = $this->getMock('\OC\User\Database');
		\OC::$server->getUserManager()->registerBackend($userBackend);

		$userBackend->expects($this->any())
			->method('userExists')
			->will($this->returnValueMap([
				[$user1, true],
				[$user2, true],
				[strtoupper($user1), true],
				[strtoupper($user2), true],
			]));
		$userBackend->expects($this->any())
			->method('loginName2UserName')
			->will($this->returnValueMap([
				[strtoupper($user1), $user1],
				[$user1, $user1],
				[strtoupper($user2), $user2],
				[$user2, $user2],
			]));

		$this->loginAsUser($user1);
		$root = new \OC\Files\Node\Root($manager, $view, null, $this->userMountCache);

		$folder = $root->getUserFolder($user1);
		$this->assertEquals('/' . $user1 . '/files', $folder->getPath());

		$folder = $root->getUserFolder($user2);
		$this->assertEquals('/' . $user2 . '/files', $folder->getPath());

		// case difference must not matter here
		$folder = $root->getUserFolder(strtoupper($user2));
		$this->assertEquals('/' . $user2 . '/files', $folder->getPath());

		$thrown = false;
		try {
			$folder = $root->getUserFolder($this->getUniqueID('unexist'));
		} catch (NoUserException $e) {
			$thrown = true;
		}
		$this->assertTrue($thrown);
	}
}
