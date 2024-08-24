<?php

namespace PrestoPlayer\Models;

use PrestoPlayer\Support\Utility;
use PrestoPlayer\Support\HasOneRelationship;

/**
 * Model for interfacing with custom database tables
 */
abstract class Model implements ModelInterface {

	/**
	 * Needs a table name
	 *
	 * @var string
	 */
	protected $table = '';

	/**
	 * Store model attributes
	 *
	 * @var object
	 */
	protected $attributes;

	/**
	 * Model schema
	 *
	 * @return array
	 */
	public function schema() {
		return array();
	}

	/**
	 * Guarded variables
	 *
	 * @var array
	 */
	protected $guarded = array();

	/**
	 * Attributes we can query by
	 *
	 * @var array
	 */
	protected $queryable = array();

	/**
	 * Optionally get something from the db
	 *
	 * @param integer $id
	 */
	public function __construct( $id = 0 ) {
		$this->attributes = new \stdClass();
		if ( ! empty( $id ) ) {
			return $this->set( $this->get( $id )->toObject() );
			return $this;
		}
		return $this;
	}

	/**
	 * Get attributes properties
	 *
	 * @param string $property
	 * @return mixed
	 */
	public function __get( $property ) {
		if ( property_exists( $this->attributes, $property ) ) {
			return $this->attributes->$property;
		}
	}

	/**
	 * Get attributes properties
	 *
	 * @param string $property
	 * @return mixed
	 */
	public function __set( $property, $value ) {
		$this->attributes->$property = $value;
	}

	public function getTableName() {
		return $this->table;
	}

	/**
	 * Convert to Object
	 *
	 * @return object
	 */
	public function toObject() {
		$output = new \stdClass();
		foreach ( $this->attributes as $key => $attribute ) {
			if ( is_a( $attribute, self::class ) ) {
				$output->$key = $attribute->toObject();
			} else {
				$output->$key = $attribute;
			}
		}
		return $output;
	}

	/**
	 * Convert to array
	 *
	 * @return array
	 */
	public function toArray() {
		return (array) $this->toObject();
	}

	/**
	 * Formats row data based on schema
	 *
	 * @param object $columns
	 * @return object
	 */
	public function formatRow( $columns ) {
		$columns = (array) $columns;
		$schema  = $this->schema();

		$columns = $this->maybeUnSerializeArgs( $columns );

		foreach ( $columns as $key => $column ) {
			if ( ! empty( $schema[ $key ]['type'] ) ) {
				settype( $columns[ $key ], $schema[ $key ]['type'] );
			}
		}

		return (object) $columns;
	}

	/**
	 * Fetch all models
	 *
	 * @return array Array of preset objects
	 */
	public function all() {
		global $wpdb;

		// maybe get only published if we have soft deletes
		$where = ! empty( $this->schema()['deleted_at'] ) ? "WHERE (deleted_at IS NULL OR deleted_at = '0000-00-00 00:00:00') " : '';

		$results = $wpdb->get_results(
			"SELECT * FROM {$wpdb->prefix}{$this->table} $where"
		);

		return $this->parseResults( $results );
	}

	/**
	 * Fetch models from db
	 *
	 * @param array $args
	 * @return Object Array of models with pagination data
	 */
	public function fetch( $args = array() ) {
		global $wpdb;

		// remove empties for querying
		$args = array_filter(
			wp_parse_args(
				$args,
				array(
					'status'   => 'published',
					'per_page' => 10,
					'order_by' => array(),
					'page'     => 1,
				)
			)
		);

		// get query args
		$query = array_filter(
			$args,
			function ( $key ) {
				return in_array( $key, array( 'per_page', 'page', 'status', 'date_query', 'fields', 'order_by' ) );
			},
			ARRAY_FILTER_USE_KEY
		);

		$where  = 'WHERE 1=1 ';
		$schema = $this->schema();

		foreach ( $args as $attribute => $value ) {
			// must be queryable and in schema
			if ( ! in_array( $attribute, $this->queryable ) || empty( $schema[ $attribute ] ) ) {
				unset( $args[ $attribute ] );
				continue;
			}

			// attribute schema
			$attr_schema = $schema[ $attribute ];

			// force type
			settype( $value, $attr_schema['type'] );

			// sanitize input
			if ( ! empty( $attr_schema['sanitize_callback'] ) ) {
				$value = $attr_schema['sanitize_callback']( $value );
			}

			// maybe add quotes
			if ( in_array( $attr_schema['type'], array( 'integer', 'number', 'boolean' ) ) ) {
				$where .= $wpdb->prepare( 'AND %1s=%2s ', $attribute, $value );
			} else {
				$where .= $wpdb->prepare( "AND %1s='%2s' ", $attribute, $value );
			}
		}

		// soft deletes
		if ( ! empty( $this->schema()['deleted_at'] ) ) {
			$status = ! empty( $args['status'] ) ? $args['status'] : '';
			switch ( $status ) {
				case 'trashed':
					$where .= "AND (deleted_at IS NOT NULL OR deleted_at != '0000-00-00 00:00:00') ";
					break;
				default: // default to published
					$where .= "AND (deleted_at IS NULL OR deleted_at = '0000-00-00 00:00:00') ";
					break;
			}
		}

		// before and after
		if ( ! empty( $query['date_query'] ) ) {
			// use created at by default
			$query['date_query'] = wp_parse_args(
				$query['date_query'],
				array(
					'field' => 'created_at',
				)
			);

			// check for field
			$field = ! empty( $this->schema()[ $query['date_query']['field'] ] ) ? sanitize_text_field( $query['date_query']['field'] ) : null;
			if ( ! $field ) {
				return new \WP_Error( 'invalid_field', 'Cannot do a date query by ' . sanitize_text_field( $query['date_query']['field'] ) );
			}

			// if after
			if ( ! empty( $query['date_query']['after'] ) ) {
				$where .= $wpdb->prepare(
					"AND %1s >= '%2s' ",
					sanitize_text_field( $field ), // i.e. created_at
					date( 'Y-m-d H:i:s', strtotime( $query['date_query']['after'] ) ) // convert to date
				);
			}
			// before
			if ( ! empty( $query['date_query']['before'] ) ) {
				$where .= $wpdb->prepare(
					"AND %1s <= '%2s' ",
					sanitize_text_field( $field ), // i.e. created_at
					date( 'Y-m-d H:i:s', strtotime( $query['date_query']['before'] ) ) // convert to date
				);
			}
		}

		$limit      = (int) $query['per_page'];
		$offset     = (int) ( $query['per_page'] * ( $query['page'] - 1 ) );
		$pagination = $wpdb->prepare( 'LIMIT %1s OFFSET %2s ', $limit, $offset );

		$select = '*';
		if ( ! empty( $query['fields'] ) && 'ids' === $query['fields'] ) {
			$select = 'id';
		}

		$order_by = '';
		if ( ! empty( $query['order_by'] ) ) {
			$order_by .= 'ORDER BY';
			$number    = count( $query['order_by'] );
			$i         = 1;
			foreach ( $query['order_by'] as $attribute => $direction ) {
				$order_by .= $wpdb->prepare( ' %1s %2s', $attribute, $direction );
				$order_by .= $i === $number ? '' : ',';
				++$i;
			}
			$order_by .= ' ';
		}

		$total   = $wpdb->get_var( "SELECT count(id) as count FROM {$wpdb->prefix}{$this->table} $where$order_by" );
		$results = $wpdb->get_results( "SELECT $select FROM {$wpdb->prefix}{$this->table} $where$order_by$pagination" );

		return (object) array(
			'total'    => (int) $total,
			'per_page' => (int) $query['per_page'],
			'page'     => (int) $query['page'],
			'data'     => 'id' === $select ? $this->parseIds( $results ) : $this->parseResults( $results ),
		);
	}

	/**
	 * Find a specific model based on query
	 */
	public function findWhere( $args = array() ) {
		$args  = wp_parse_args( $args, array( 'per_page' => 1 ) );
		$items = $this->fetch( $args );
		return ! empty( $items->data[0] ) ? $items->data[0] : false;
	}

	/**
	 * Turns raw sql query results into models
	 *
	 * @param array $results
	 * @return array Array of Models
	 */
	protected function parseResults( $results ) {
		if ( is_wp_error( $results ) ) {
			return $results;
		}
		if ( empty( $results ) ) {
			return array();
		}

		$output = array();
		// return new models for each row
		foreach ( $results as $result ) {
			$class    = get_class( $this );
			$output[] = ( new $class() )->set( $result );
		}

		return $output;
	}

	public function parseIds( $results ) {
		if ( is_wp_error( $results ) ) {
			return $results;
		}
		if ( empty( $results ) ) {
			return array();
		}

		$ids = array();
		foreach ( $results as $result ) {
			$ids[] = (int) $result->id;
		}
		return $ids;
	}

	/**
	 * Gets fresh data from the db
	 *
	 * @return Model
	 */
	public function fresh() {
		if ( $this->id ) {
			return $this->get( $this->id );
		}
		return $this;
	}

	/**
	 * Get default values set from scheam
	 *
	 * @return array
	 */
	protected function getDefaults() {
		$schema   = $this->schema();
		$defaults = array();
		foreach ( $schema as $attribute => $scheme ) {
			if ( empty( $scheme['default'] ) ) {
				continue;
			}
			$defaults[ $attribute ] = $scheme['default'];
		}

		return $defaults;
	}

	/**
	 * Unset guarded variables
	 *
	 * @param array $args
	 * @return void
	 */
	protected function unsetGuarded( $args = array() ) {
		// unset guarded
		foreach ( $this->guarded as $arg ) {
			if ( $args[ $arg ] ) {
				unset( $args[ $arg ] );
			}
		}

		// we should never set an ID
		unset( $args['id'] );

		return $args;
	}

	/**
	 * Create a preset
	 *
	 * @param array $args
	 * @return integer
	 */
	public function create( $args ) {
		global $wpdb;

		// unset guarded args
		$args = $this->unsetGuarded( $args );

		// parse args with default args
		$args = wp_parse_args( $args, $this->getDefaults() );

		// creation time
		if ( ! empty( $this->schema()['created_at'] ) ) {
			$args['created_at'] = ! empty( $args['created_at'] ) ? $args['created_at'] : current_time( 'mysql' );
		}

		// maybe serialize args
		$args = $this->maybeSerializeArgs( $args );

		// insert
		$wpdb->insert( $wpdb->prefix . $this->table, $args );

		// set ID in attributes
		$this->attributes->id = $wpdb->insert_id;

		// created action
		do_action( "{$this->table}_created", $this );

		// return id
		return $this->attributes->id;
	}

	protected function maybeSerializeArgs( $args ) {
		foreach ( $args as $key => $arg ) {
			if ( ! empty( $this->schema()[ $key ] ) ) {
				if ( 'array' === $this->schema()[ $key ]['type'] ) {
					$args[ $key ] = maybe_serialize( $args[ $key ] );
				}
			}
		}
		return $args;
	}

	protected function maybeUnSerializeArgs( $args ) {
		foreach ( $args as $key => $arg ) {
			if ( ! empty( $this->schema()[ $key ] ) ) {
				if ( 'array' === $this->schema()[ $key ]['type'] ) {
					$args[ $key ] = maybe_unserialize( $args[ $key ] );
				}
			}
		}
		return $args;
	}

	/**
	 * Attempt to locate a database record using the given
	 * column / value pairs. If the model can NOT be found
	 * in the database, a record will be inserted with
	 * the attributes resulting from merging the first array
	 * argument with the optional second array argument.
	 *
	 * @param array $search Model to search for
	 * @param array $create Attributes to create
	 * @return Model|\WP_Error
	 */
	public function firstOrCreate( $search, $create = array() ) {
		if ( $this->id ) {
			return new \WP_Error( 'already_created', 'This model has already been created.' );
		}

		$models = $this->fetch( $search );
		if ( is_wp_error( $models ) ) {
			return $models;
		}

		// already created
		if ( ! empty( $models->data[0] ) ) {
			$this->set( $models->data[0]->toObject() );
			return $this;
		}

		// merge and create
		$merged = array_merge( $search, $create );
		$this->create( $merged );

		// return fresh instance
		return $this->fresh();
	}

	/**
	 * Create and get a model
	 *
	 * @param array $args
	 * @return Model|\WP_Error
	 */
	public function createAndGet( $args ) {
		$id = $this->create( $args );
		if ( is_wp_error( $id ) || ! $id ) {
			return $id;
		}
		return $this->fresh();
	}

	/**
	 * Attempt to locate a database record using the given
	 * column / value pairs and update. If the model can NOT be found
	 * in the database, a record will be inserted with
	 * the attributes resulting from merging the first array
	 * argument with the optional second array argument.
	 *
	 * @param array $search Model to search for
	 * @param array $create Attributes to create
	 * @return Model|\WP_Error
	 */
	public function getOrCreate( $search, $update = array() ) {
		// look for model
		$models = $this->fetch( $search );
		if ( is_wp_error( $models ) ) {
			return $models;
		}

		// already created, update it
		if ( ! empty( $models->data[0] ) && ! empty( $update ) ) {
			$this->set( $models->data[0]->toObject() );
			return $this;
		}

		// merge and create
		$merged = array_merge( $search, $update );

		// unset query stuff
		if ( ! empty( $merged['date_query'] ) ) {
			unset( $merged['date_query'] );
		}

		$this->create( $merged );

		// return fresh instance
		return $this->fresh();
	}

	/**
	 * Attempt to locate a database record using the given
	 * column / value pairs and update. If the model can NOT be found
	 * in the database, a record will be inserted with
	 * the attributes resulting from merging the first array
	 * argument with the optional second array argument.
	 *
	 * @param array $search Model to search for
	 * @param array $create Attributes to create
	 * @return Model|\WP_Error
	 */
	public function updateOrCreate( $search, $update = array() ) {
		// look for model
		$models = $this->fetch( $search );
		if ( is_wp_error( $models ) ) {
			return $models;
		}

		// already created, update it
		if ( ! empty( $models->data[0] ) && ! empty( $update ) ) {
			$this->set( $models->data[0]->toObject() );
			$this->update( $update );
			return $this;
		}

		// merge and create
		$merged = array_merge( $search, $update );

		// unset query stuff
		if ( ! empty( $merged['date_query'] ) ) {
			unset( $merged['date_query'] );
		}

		$this->create( $merged );

		// return fresh instance
		return $this->fresh();
	}

	/**
	 * Gets a single model
	 *
	 * @param int $id
	 *
	 * @return Model Model object
	 */
	public function get( $id ) {
		global $wpdb;

		// maybe cache results
		$results = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}{$this->table} WHERE id=%d", $id )
		);

		if ( ! empty( $this->with ) ) {
			foreach ( $this->with as $with ) {
				$method_name = Utility::snakeToCamel( $with );
				if ( method_exists( $this, $method_name ) ) {
					$relationship_class = $this->$method_name()->getRelationshipClass();
					$parent_field       = $this->$method_name()->getParentField();
					if ( $results->$parent_field ) {
						$results->$parent_field = ( new $relationship_class() )->get( $results->$parent_field );
					}
				}
			}
		}

		return $this->set( $results );
	}

	/**
	 * Set attributes
	 *
	 * @param array $args
	 * @return Model
	 */
	public function set( $args ) {
		$this->attributes = apply_filters( "presto_player/{$this->table}/data", $this->formatRow( $args ) );
		return $this;
	}

	/**
	 * Update a model
	 *
	 * @param array $args
	 * @return Model
	 */
	public function update( $args = array() ) {
		global $wpdb;

		// id is required
		if ( empty( $this->attributes->id ) ) {
			return new \WP_Error( 'missing_parameter', __( 'You must first create or save this model to update it.', 'presto-player' ) );
		}

		// unset guarded args
		$args = $this->unsetGuarded( $args );

		// parse args with default args
		$args = wp_parse_args( $args, $this->getDefaults() );

		// update time
		if ( ! empty( $this->schema()['updated_at'] ) ) {
			$args['updated_at'] = ! empty( $args['updated_at'] ) ? $args['updated_at'] : current_time( 'mysql' );
		}

		// maybe serialize
		$args = $this->maybeSerializeArgs( $args );

		// make update
		$updated = $wpdb->update( $wpdb->prefix . $this->table, $args, array( 'id' => (int) $this->id ) );

		// check for failure
		if ( false === $updated ) {
			return new \WP_Error( 'update_failure', __( 'There was an issue updating the model.', 'presto-player' ) );
		}

		// set attributes in model
		$this->set( $this->get( $this->id )->toObject() );

		// created action
		do_action( "{$this->table}_updated", $this );

		return $this;
	}

	/**
	 * Trash model
	 *
	 * @return Model
	 */
	public function trash() {
		return $this->update( array( 'deleted_at' => current_time( 'mysql' ) ) );
	}

	/**
	 * Untrash model
	 *
	 * @return Model
	 */
	public function untrash() {
		return $this->update( array( 'deleted_at' => null ) );
	}

	/**
	 * Permanently delete model
	 *
	 * @return boolean Whether the model was deleted
	 */
	public function delete( $where = array() ) {
		if ( empty( $where ) ) {
			$where = array( 'id' => (int) $this->id );
		}
		global $wpdb;
		return (bool) $wpdb->delete( $wpdb->prefix . $this->table, $where );
	}

	/**
	 * Bulk delete by a list of ids
	 *
	 * @param array $ids
	 * @return void
	 */
	public function bulkDelete( $ids = array() ) {
		global $wpdb;

		// convert to comman separated
		$ids = implode( ',', array_map( 'absint', $ids ) );

		// delete in bulk
		return (bool) $wpdb->query(
			$wpdb->prepare( "DELETE FROM {$wpdb->prefix}{$this->table} WHERE id IN(%1s)", $ids )
		);
	}

	/**
	 * Has One Relationship
	 */
	public function hasOne( $classname, $parent_field ) {
		return new HasOneRelationship( $classname, $this, $parent_field );
	}
}
