<?php
/**
 * Base Modal class.
 * php version 5.6
 *
 * @category Model
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Models;

use ReflectionClass;
use wpdb;

/**
 * Responsible for interacting with the database.
 *
 * Class Model
 *
 * @package SureTriggers\Model
 * @psalm-consistent-constructor
 */
abstract class Model {

	/**
	 * Table name.
	 *
	 * @var string
	 */
	public $table;

	/**
	 * Join table name.
	 *
	 * @var string
	 */
	public $join_table;

	/**
	 * Select
	 *
	 * @var string
	 */
	public $select;

	/**
	 * Query output.
	 *
	 * @var string
	 */
	public $output = OBJECT;

	/**
	 * Total results count
	 *
	 * @var int
	 */
	public $total;

	/**
	 * Start from
	 *
	 * @var int
	 */
	public $start;

	/**
	 * Per page results count
	 *
	 * @var int
	 */
	public $per_page;

	/**
	 * Pagination Properties
	 */

	/**
	 * Search term
	 *
	 * @var string
	 */
	public $search_term;

	/**
	 * Current page of the pagination
	 *
	 * @var int
	 */
	public $current_page;

	/**
	 * Which column should be order
	 *
	 * @var string
	 */
	public $order_by_col;

	/**
	 * ASC|DESC
	 *
	 * @var string
	 */
	public $order_by = 'DESC';

	/**
	 * Order by Sql query
	 *
	 * @var string
	 */
	public $order_by_sql;

	/**
	 * Primary ID.
	 *
	 * @var string
	 */
	protected $primary_id = 'id';

	/**
	 * Database.
	 *
	 * @var wpdb
	 */
	protected $db;

	/**
	 * It will generate Where SQL
	 *
	 * @var array
	 */
	protected $where = [];

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		global $wpdb;

		$this->db = $wpdb;

		if ( ! empty( $this->table ) ) {
			$this->table = $wpdb->prefix . $this->table;
		} else {
			$this->table = $wpdb->prefix . strtolower( ( new ReflectionClass( $this ) )->getShortName() );
		}

		$this->total        = 0;
		$this->start        = 0;
		$this->per_page     = 0;
		$this->search_term  = '';
		$this->current_page = 1;

		$this->order_by_col = $this->primary_id;
		$this->order_by_sql = " ORDER BY {$this->order_by_col} {$this->order_by} ";
	}

	/**
	 * Initialize query.
	 *
	 * @param string $output output.
	 * @return static|null
	 */
	public static function init( $output = OBJECT ) {
		$_instance         = new static();
		$_instance->output = $output;

		return $_instance;
	}

	/**
	 * Get all results.
	 *
	 * @return array|object|void|null
	 */
	public function all() {
		return $this->db->get_results( "SELECT * FROM  {$this->table}", $this->output );
	}

	/**
	 * Prepare where query on given array.
	 *
	 * @param array $data where data.
	 * @return $this
	 */
	public function where( $data ) {
		foreach ( $data as $key => $value ) {
			$this->where[] = "AND {$key} = '{$value}'";
		}
		return $this;
	}

	/**
	 * Get the table name.
	 *
	 * @return string
	 */
	public function table() {
		return $this->table;
	}

	/**
	 * Add selections.
	 *
	 * @param string $select select string.
	 * @return $this
	 */
	public function select( $select ) {
		$this->select = $select;
		return $this;
	}

	/**
	 * Get specific results.
	 *
	 * @return array|object|void|null
	 */
	public function get() {
		return $this->db->get_results( "SELECT {$this->get_select()} FROM {$this->table} {$this->get_left_join()} {$this->get_where_sql()} ", $this->output );
	}

	/**
	 * Prepare WHERE conditions for sql.
	 *
	 * @return string
	 */
	public function get_where_sql() {
		$where_clause = '';
		if ( count( $this->where ) ) {
			$where_clause .= implode( ' ', $this->where );
		}

		return " WHERE 1 = 1 {$where_clause} ";
	}

	/**
	 * Get var from the db.
	 *
	 * @return string|null|int
	 */
	public function get_var() {
		return $this->db->get_var( "SELECT {$this->get_select()} FROM {$this->table} {$this->get_left_join()} {$this->get_where_sql()} " );
	}

	/**
	 * Find the result by id.
	 *
	 * @param int $id id.
	 * @return array|object|void|null
	 */
	public function find( $id ) {
		return $this->db->get_row(
			$this->db->prepare(
				"SELECT * FROM {$this->table} WHERE {$this->primary_id} = %d",
				$id
			),
			$this->output
		);
	}

	/**
	 * Get rows.
	 *
	 * @param string|null $query SQL query.
	 * @param int         $y Optional. Row to return. Indexed from 0.
	 *
	 * @return array|object|void|null
	 */
	public function get_row( $query = null, $y = 0 ) {
		return $this->db->get_row( $query, $this->output, $y );
	}

	/**
	 * Prepare query.
	 *
	 * @param string $query query string.
	 * @param array  ...$args arguments.
	 * @return string|void|null
	 */
	public function prepare( $query, ...$args ) {
		return $this->db->prepare( $query, ...$args );
	}

	/**
	 * Generate search query
	 *
	 * @param string $search_term Search Term.
	 *
	 * @return $this
	 */
	public function search( $search_term = '' ) {
		$this->search_term = $search_term;

		return $this;
	}

	/**
	 * Set order by value DESC
	 *
	 * @param string $col Table Column.
	 *
	 * @return $this
	 */
	public function desc( $col = '' ) {
		if ( ! empty( $col ) ) {
			$this->order_by_col = $col;
		}
		$this->order_by = 'DESC';

		return $this;
	}

	/**
	 * Set order by value ASC
	 *
	 * @param string $col Order by Column.
	 *
	 * @return $this
	 */
	public function asc( $col = '' ) {
		if ( ! empty( $col ) ) {
			$this->order_by_col = $col;
		}
		$this->order_by = 'ASC';

		return $this;
	}
}
