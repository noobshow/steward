<?php
/**
 * Allows sharing data between test-cases and phases of tests
 *
 * Two possible uses
 * 1. loadWithName and saveWithName - you must define a name for the legacy which will be used as a filename
 * to store the data - beware - the name must be unique through all test-cases
 *
 * example:
 * class FooPhase1Test
 * {
 *      public function test()
 *      {
 *          $legacy->saveWithName("some data to be remembered", 'my_test_case_legacy');
 *      }
 * }
 *
 * class FooPhase2Test
 * {
 *      public function test()
 *      {
 *          $data = $legacy->loadWithName('my_test_case_legacy');
 *      }
 * }
 *
 *
 * 2. load and save - the name of the legacy (file) is generated from the name of the test case class and the name
 * of the test running - the class must have PhaseN in the name where N is a digit - this because different phases
 * of the test-case will differ in the digit but the rest of the name will be the same
 * - and so different phases of the same test-case can access the same legacy
 * You can choose whether the legacy should be shared between tests in a test case (class) or accessible only
 * by the same test function.
 *
 * example:
 * class FooPhase1Test
 * {
 *      public function test()
 *      {
 *          $legacy->save("some data to be remembered");
 *      }
 * }
 *
 * class FooPhase2Test
 * {
 *      public function test()
 *      {
 *          $data = $legacy->load();
 *      }
 * }
 *
 */

namespace Lmc\Steward\Test;

class Legacy
{
    const LEGACY_TYPE_CASE = "CASE";
    const LEGACY_TYPE_TEST = "TEST";

    /**
     * @var AbstractTestCaseBase
     */
    protected $test;

    /**
     * @var string
     */
    protected $testClassName;

    /**
     * Create Legacy instance
     * @param \Lmc\Steward\Test\AbstractTestCaseBase $test
     */
    public function __construct(AbstractTestCaseBase $test)
    {
        $this->test = $test;
        $this->testClassName = get_class($this->test);
    }

    /**
     * Generates a filename (without path) for the legacy based on the name of the test-case
     * @param $type string LEGACY_TYPE_CASE (shared by all tests in test case)
     *      or LEGACY_TYPE_TEST (shared only by the same test function)
     * @return string
     * @throws LegacyException
     */
    protected function getLegacyName($type)
    {
        $name = $this->testClassName;

        if (preg_match('/Phase\d/', $name)) {
            $name = preg_replace('/Phase\d/', '', $name);
            $name = str_replace(['/', '\\'], '-', $name);
            if ($type == Legacy::LEGACY_TYPE_TEST) {
                $name .= '#' . $this->test->getName();
            }
            $name .= ".legacy";
        } else {
            throw new LegacyException(
                "Cannot generate legacy name from class without 'Phase' followed by number in name " . $name);
        }
        return $name;
    }

    /**
     * Makes a fully qualified path to file with legacy
     * @param $filename
     * @return string
     */
    protected function makeLegacyFullPath($filename)
    {
        return "logs/" . $filename;
    }

    /**
     * Store legacy of test under a custom name
     * @param $data
     * @param string $legacyName filename to store the data if null getLegacyFilename is called to generate filename
     *      from the test class name
     * @throws LegacyException
     */
    public function saveWithName($data, $legacyName)
    {
        $filename = $this->makeLegacyFullPath($legacyName);
        if (file_put_contents($filename, serialize($data)) === false) {
            throw new LegacyException("Cannot save legacy to file " . $filename);
        }
    }

    /**
     * Store legacy of test getLegacyFilename is called to generate filename
     *      from the test class name
     * @param $data
     * @param $type string LEGACY_TYPE_CASE (shared by all tests in test case)
     *      or LEGACY_TYPE_TEST (shared only by the same test function)
     * @throws LegacyException
     */
    public function save($data, $type = Legacy::LEGACY_TYPE_CASE)
    {
        $this->saveWithName($data, $this->getLegacyName($type));
    }

    /**
     * Reads legacy of test getLegacyFilename is called to generate filename
     * from the test class name
     * raises exception if it is not found
     * @param $type string LEGACY_TYPE_CASE (shared by all tests in test case)
     *      or LEGACY_TYPE_TEST (shared only by the same test function)
     * @return Mixed
     * @throws LegacyException
     */
    public function load($type = Legacy::LEGACY_TYPE_CASE)
    {
        return $this->loadWithName($this->getLegacyName($type));
    }

    /**
     * Reads legacy specified by custom name
     * raises exception if it is not found
     * @param string $legacyName filename to store the data
     *      from the test class name
     * @return Mixed
     * @throws LegacyException
     */
    public function loadWithName($legacyName)
    {
        $filename = $this->makeLegacyFullPath($legacyName);

        // if the file doesn't exist - raise exception
        if (!file_exists($filename)) {
            throw new LegacyException("Cannot find legacy file " . $filename);
        }

        $data = file_get_contents($filename);
        if ($data===false) {
            throw new LegacyException("Cannot read legacy file " . $filename);
        }

        $legacy = unserialize($data);
        if ($legacy===false) {
            throw new LegacyException("Cannot parse legacy form file " . $filename);
        }

        return $legacy;
    }

}