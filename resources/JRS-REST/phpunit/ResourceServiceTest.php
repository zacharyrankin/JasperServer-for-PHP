<?php
/* ==========================================================================

Copyright (C) 2005 - 2012 Jaspersoft Corporation. All rights reserved.
http://www.jaspersoft.com.

Unless you have purchased a commercial license agreement from Jaspersoft,
the following license terms apply:

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as
published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU Affero  General Public License for more details.

You should have received a copy of the GNU Affero General Public  License
along with this program. If not, see <http://www.gnu.org/licenses/>.

=========================================================================== */
use Jasper\JasperClient;
use Jasper\ResourceDescriptor;
use Jasper\ResourceProperty;
use Jasper\JasperTestUtils;

require_once(dirname(__FILE__) . '/lib/JasperTestUtils.php');
require_once(dirname(__FILE__) . '/../client/JasperClient.php');


class JasperResourceServiceTest extends PHPUnit_Framework_TestCase {

	protected $jc;
	protected $newResource_image;
	protected $newResource_folder;
	public $pwd;

	public function setUp() {
		$bootstrap = parse_ini_file(dirname(__FILE__) . '/test.properties');

		$this->pwd = dirname(__FILE__);
		$this->jc = new JasperClient(
				$bootstrap['hostname'],
				$bootstrap['port'],
				$bootstrap['admin_username'],
				$bootstrap['admin_password'],
				$bootstrap['base_url'],
				$bootstrap['admin_org']
		);

		// Determines the relative path to the binary image we use to test with
		$this->image_location = $this->pwd . "/resources/pitbull.jpg";
	}

	public function tearDown() {

	}

	//** Tests **//

	public function testPutResource_withImage() {
		$folder = JasperTestUtils::createFolder();
		$image = JasperTestUtils::createImage($folder);
		$this->jc->putResource('', $folder);
		$test = $this->jc->putResource('', $image, $this->image_location);
		$image_data = $this->jc->getResource($image->getUriString(), true);
		$this->jc->deleteResource($image->getUriString());
		$this->jc->deleteResource($folder->getUriString());
		$this->assertEquals(filesize($this->image_location), strlen($image_data));
 	}

	public function testPutResource_withFolder() {
		$folder = JasperTestUtils::createFolder();
		$success = $this->jc->putResource('', $folder);
		$folder_data = $this->jc->getResource($folder->getUriString());
		$this->jc->deleteResource($folder->getUriString());
		$this->assertEquals($folder_data->getLabel(), $folder->getLabel());
	}

	public function testPostResource_withFolder() {
		$folder = JasperTestUtils::createFolder();
		$this->jc->putResource('', $folder);
		$folder_data = $this->jc->getResource($folder->getUriString());
		$folder_data->setLabel('testTWO');
		$this->jc->postResource($folder->getUriString(), $folder_data);
		$updated_folder = $this->jc->getResource($folder->getUriString());
		$this->jc->deleteResource($updated_folder->getUriString());

		$this->assertEquals('testTWO', $updated_folder->getLabel());
	}

	public function testPutResource_withDataSource() {
		$folder = JasperTestUtils::createFolder();
		$datasource = JasperTestUtils::createDataSource($folder);
		$this->jc->putResource('', $folder);
		$this->jc->putResource($folder->getUriString(), $datasource);

		$datasource_data = $this->jc->getResource($datasource->getUriString());
		$this->jc->deleteResource($datasource->getUriString());
		$this->jc->deleteResource($folder->getUriString());
		$this->assertEquals($datasource_data->getName(), $datasource->getName());
	}

}

?>