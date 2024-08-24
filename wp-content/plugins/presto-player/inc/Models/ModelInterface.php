<?php

namespace PrestoPlayer\Models;

interface ModelInterface {

	/**
	 * Sets attributes in the model
	 *
	 * @param array $args
	 * @return ModelInterface
	 */
	public function set( $args );

	/**
	 * Gets a model from the database
	 *
	 * @param integer $id
	 * @return ModelInterface
	 */
	public function get( $id );

	/**
	 * Attempt to locate a database record using the given
	 * column / value pairs. If the model can NOT be found
	 * in the database, a record will be inserted with
	 * the attributes resulting from merging the first array
	 * argument with the optional second array argument.
	 *
	 * @param array $search Model to search for
	 * @param array $create Attributes to create
	 * @return void
	 */
	public function firstOrCreate( $search, $create );

	/**
	 * Re-retrieve the instance from the database
	 *
	 * @return ModelInterface
	 */
	public function fresh();

	/**
	 * Fetch all models from the database
	 *
	 * @return array Array of ModelInterface objects
	 */
	public function all();

	/**
	 * Fetch models from the database
	 *
	 * @param array $args
	 * @return array Array of ModelInterface objects
	 */
	public function fetch( $args );

	/**
	 * Create a new model in the database
	 *
	 * @param array $args
	 * @return int ID of the created model
	 */
	public function create( $args );

	/**
	 * Convenience function to create and get model
	 *
	 * @param array $args Model creation args
	 * @return Model
	 */
	public function createAndGet( $args );

	/**
	 * Update an existing model or create a new model if no matching model exists
	 *
	 * @param array $search
	 * @param array $create
	 * @return Model
	 */
	public function updateOrCreate( $search, $create );

	/**
	 * Update a model in the database
	 *
	 * @param array $args
	 * @return ModelInterface
	 */
	public function update( $args );

	/**
	 * Trash a model
	 *
	 * @return ModelInterface
	 */
	public function trash();

	/**
	 * Untrash a model
	 *
	 * @return ModelInterface
	 */
	public function untrash();

	/**
	 * Permanently delete a model
	 *
	 * @return bool
	 */
	public function delete();
}
