<?php
namespace FreepPBX\Core\utests;

require_once('../api/utests/ApiBaseTestCase.php');

use FreePBX\modules\Core;
use Exception;
use FreePBX\modules\Api\utests\ApiBaseTestCase;
use GuzzleHttp\Client;

class CoreDeviceGQLTest extends ApiBaseTestCase {
	protected static $core;
	protected static $minTestExtension = 979000;
	protected static $maxTestExtension = 979999;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$core = self::$freepbx->Core;
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();
		self::$freepbx->Core = self::$core;
		foreach(self::$core->getAllDevicesByType() as $device) {
			if ($device['id'] >= self::$minTestExtension && $device['id'] <= self::$maxTestExtension) {
				self::$core->delDevice($device['id']);
			}
		}
	}

	public function test_coreDeviceQuery_whenAllIsWell_shouldReturnDevice() {
		$testExtension = 979898;

		// clean up previous test
		self::$core->delDevice($testExtension);

		// create test device
		$settings = self::$core->generateDefaultDeviceSettings(
			'pjsip', 
			$testExtension,
			'pjsip test'
		);
		self::$core->addDevice($testExtension, 'pjsip', $settings);

		$stubConfig = $this->getMockBuilder(\FreePBX\Core::class)
			->setMethods(array('getDevice'))
			->getMock();

		$stubConfig = $this->getMock(\FreePBX\Core::class, array('getDevice'));
		$stubConfig->method('getDevice')
			->willReturn(Array(
				'id' => 7070,
				'tech' => 'sip',
				'dial' => 'SIP/7071',
				'devicetype' => 'fixed',
				'user' => 7045,
				'description' => 'Test D70',
				'emergency_cid' => '',
				'hint_override' => '',
				'account' => 1234,
				'accountcode' => '',
				'aggregate_mwi' => 'yes',
				'allow' => '',
				'avpf' => 'no',
				'callerid' => 'Test D70 <6969>',
				'context' => 'from-internal',
				'defaultuser' => '',
				'device_state_busy_at' => 0,
				'disallow' => '',
				'dtmfmode' => 'rfc4733',
				'force_rport' => 'yes',
				'icesupport' => 'no',
				'match' => '',
				'max_contacts' => 1,
				'maximum_expiration' => 7200,
				'media_encryption' => 'no',
				'media_encryption_optimistic' => 'no',
				'media_use_received_transport' => 'no',
				'minimum_expiration' => 60,
				'mwi_subscription' => 'auto',
				'namedcallgroup' => '',
				'namedpickupgroup' => '',
				'outbound_proxy' => '',
				'qualifyfreq' => 60,
				'rewrite_contact' => 'yes',
				'rtcp_mux' => 'no',
				'rtp_symmetric' => 'yes',
				'secret' => '0ab2282c967d042246b202ba7e199455',
				'secret_origional' => '',
				'sendrpid' => 'pai',
				'sipdriver' => 'chan_sip',
				'timers' => 'yes',
				'transport' => '',
				'trustrpid' => 'yes'
			));

		self::$freepbx->Core = $stubConfig;

		// run the test
		$query = "query { 
			coreDevice(id: \"{$testExtension}\") { 
				id,
				device_id,
				description,
				devicetype,
				dial,
				emergency_cid
			}
		}";
		$response = $this->request($query);

		echo "\n";
		print_r((string)$response->getBody());
		echo "\n";
		
		$token = $this->createAccessToken();
		$response = (new Client)->request('post', 'http://localhost/admin/api/api/gql', [
			'headers' => [
				'Authorization' => 'Bearer ' . $token,
				'Content-Type' => 'application/graphql'
			],
			'body' => $query
		]);

		echo "\n";
		print_r((string)$response->getBody());
		echo "\n";

		/*
		$json = json_decode((string)$response->getBody(), true);

		$mutationId = base64_encode("coredevice:{$testExtension}");
		$this->assertEquals($json, array(
			'data' => array(
				'coreDevice' => array(
					'id' => $mutationId,
					'device_id' => $testExtension,
					'description' => 'pjsip test',
					'devicetype' => 'fixed',
					'dial' => "PJSIP/{$testExtension}",
					'emergency_cid' => ''
				)
			)
		));
		*/
	}
}