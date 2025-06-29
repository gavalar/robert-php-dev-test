<?php
namespace Roger;
require_once(getcwd() . '/../src/Loader.php');

use PHPUnit\Framework\TestCase;
use Roger\Database;

class DatabaseTest extends TestCase
{
    private Database $db;

    /**
     * Set up a new database instance for testing.
     *
     * This method runs before each test and initializes the Database instance.
     */
    protected function setUp(): void
    {
        $this->db = Database::getInstance();
    }

    /**
     * Test the execute method of the Database class.
     *
     * This test ensures that the execute method correctly prepares and executes a query.
     * It verifies if the returned statement is an instance of PDOStatement.
     */
    public function testExecuteQuery()
    {
        $query = "SELECT * FROM Translation_Units WHERE id = :id";
        $params = [':id' => 1];
        $stmt = $this->db->execute($query, $params);

        $this->assertInstanceOf(PDOStatement::class, $stmt);
    }

    /**
     * Test fetching a single row of data using the fetch method.
     *
     * This test checks whether the fetch method returns an associative array with the correct keys.
     * It uses a query with parameters to fetch data from the database.
     */
    public function testFetchData()
    {
        $query = "SELECT * FROM Translation_Units WHERE id = :id";
        $params = [':id' => 1];
        $result = $this->db->fetch($query, $params);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
    }

    /**
     * Test fetching multiple rows of data using the fetchAll method.
     *
     * This test checks whether the fetchAll method returns an array of results and ensures that there is data returned.
     * It verifies that the number of results is greater than 0.
     */
    public function testFetchAllData()
    {
        $query = "SELECT * FROM Translation_Units";
        $results = $this->db->fetchAll($query);

        $this->assertIsArray($results);
        $this->assertGreaterThan(0, count($results));
    }

    /**
     * Test the retrieval of the last inserted ID using getLastInsertId.
     *
     * This test verifies that the getLastInsertId method returns a valid integer ID after an insert operation.
     */
    public function testGetLastInsertId()
    {
        $insertId = $this->db->getLastInsertId();
        $this->assertIsInt($insertId);
    }
}

