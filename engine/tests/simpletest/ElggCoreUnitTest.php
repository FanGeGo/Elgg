<?php
use Elgg\Database\Seeds\Seedable;
use Elgg\Database\Seeds\Seeding;
use Elgg\Testing;

/**
 * Elgg Core Unit Tester
 *
 * This class is to be extended by all Elgg unit tests. As such, any method here
 * will be available to the tests.
 */
abstract class ElggCoreUnitTest extends UnitTestCase implements Seedable, \Elgg\Testable {

	use Seeding;
	use Testing;

	/**
	 * Class constructor.
	 *
	 * A simple wrapper to call the parent constructor.
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Class destructor.
	 *
	 * The parent does not provide a destructor, so including an explicit one here.
	 */
	public function __destruct() {

	}

	public function setUp() {
		parent::setUp();

//		$this->assertTrue(elgg_is_admin_logged_in());
//		$this->assertFalse(elgg_get_ignore_access());
//		$this->assertFalse(access_get_show_hidden_status());

	}

	public function tearDown() {
//		$this->assertTrue(elgg_is_admin_logged_in());
//		$this->assertFalse(elgg_get_ignore_access());
//		$this->assertFalse(access_get_show_hidden_status());

		parent::tearDown();
	}

	/**
	 * Will trigger a pass if the two entity parameters have
	 * the same "value" and same type. Otherwise a fail.
	 *
	 * @param mixed  $first   Entity to compare.
	 * @param mixed  $second  Entity to compare.
	 * @param string $message Message to display.
	 *
	 * @return boolean
	 */
	public function assertIdenticalEntities(\ElggEntity $first, \ElggEntity $second, $message = '%s') {
		if (!($res = $this->assertIsA($first, '\ElggEntity'))) {
			return $res;
		}
		if (!($res = $this->assertIsA($second, '\ElggEntity'))) {
			return $res;
		}
		if (!($res = $this->assertEqual(get_class($first), get_class($second)))) {
			return $res;
		}

		return $this->assert(new IdenticalEntityExpectation($first), $second, $message);
	}

	/**
	 * Replace the current user session
	 *
	 * @param ElggUser $user New user to login as (null to log out)
	 *
	 * @return ElggUser|null Removed session user (or null)
	 */
	public function replaceSession(ElggUser $user = null) {
		$session = elgg_get_session();
		$old = $session->getLoggedInUser();
		if ($user) {
			$session->setLoggedInUser($user);
		} else {
			$session->removeLoggedInUser();
		}

		return $old;
	}

}

/**
 * Test for identity.
 * @package    SimpleTest
 * @subpackage UnitTester
 */
class IdenticalEntityExpectation extends EqualExpectation {

	/**
	 * Sets the value to compare against.
	 *
	 * @param mixed  $value   Test value to match.
	 * @param string $message Customised message on failure.
	 */
	public function __construct($value, $message = '%s') {
		parent::__construct($value, $message);
	}

	/**
	 * Tests the expectation. True if it exactly matches the held value.
	 *
	 * @param mixed $compare Comparison value.
	 *
	 * @return boolean
	 */
	public function test($compare) {
		$value = $this->entityToFilteredArray($this->getValue());
		$compare = $this->entityToFilteredArray($compare);

		return SimpleTestCompatibility::isIdentical($value, $compare);
	}

	/**
	 * Converts entity to array and filters not important attributes
	 *
	 * @param \ElggEntity $entity An entity to convert
	 *
	 * @return array
	 */
	protected function entityToFilteredArray($entity) {
		$skippedKeys = ['last_action'];
		$array = (array) $entity;
		unset($array["\0*\0volatile"]);
		unset($array["\0*\0orig_attributes"]);
		foreach ($skippedKeys as $key) {
			// See: http://www.php.net/manual/en/language.types.array.php#language.types.array.casting
			unset($array["\0*\0attributes"][$key]);
		}
		ksort($array["\0*\0attributes"]);

		return $array;
	}

	/**
	 * Returns a human readable test message.
	 *
	 * @param mixed $compare Comparison value.
	 *
	 * @return string
	 */
	public function testMessage($compare) {
		$dumper = $this->getDumper();

		$value2 = $this->entityToFilteredArray($this->getValue());
		$compare2 = $this->entityToFilteredArray($compare);

		if ($this->test($compare)) {
			return "Identical entity expectation [" . $dumper->describeValue($this->getValue()) . "]";
		} else {
			return "Identical entity expectation [" . $dumper->describeValue($this->getValue()) .
				"] fails with [" .
				$dumper->describeValue($compare) . "] " .
				$dumper->describeDifference($value2, $compare2, TYPE_MATTERS);
		}
	}

}
