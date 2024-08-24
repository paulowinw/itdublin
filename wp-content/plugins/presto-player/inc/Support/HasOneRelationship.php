<?php

namespace PrestoPlayer\Support;

class HasOneRelationship {

	protected $classname, $parent_model, $parent_field;

	/**
	 * Construct
	 *
	 * @param string                     $classname
	 * @param \PrestoPlayer\Models\Model $parent_model
	 */
	public function __construct( $classname, $parent_model, $parent_field ) {
		$this->classname    = $classname;
		$this->parent_model = $parent_model;
		$this->parent_field = $parent_field;
	}

	public function getRelationshipClass() {
		return $this->classname;
	}

	public function getParentField() {
		return $this->parent_field;
	}

	public function getRelationshipTable() {
		return ( new $this->classname() )->getTableName();
	}

	/**
	 * Save the model with the relationship
	 *
	 * @param array $arguments
	 * @return \PrestoPlayer\Models\Model
	 */
	public function save( $arguments ) {
		// create the item and save as
		$saved_id = ( new $this->classname() )->create( $arguments );

		// update or create parent model
		if ( $this->parent_model->id ) {
			return $this->parent_model->update(
				array(
					$this->parent_field => (int) $saved_id,
				)
			);
		}

		return new \WP_Error( 'unsaved', 'Please save the model before adding the relationship.' );
	}

	public function update( $arguments ) {
		if ( ! $this->parent_field ) {
			return new \WP_Error( 'unsaved', 'Please create the relationship before updating it.' );
		}

		$item_class = ( new $this->classname() );
		$item       = $this->parent_model->{$this->parent_field};

		if ( is_int( $item ) ) {
			$item = $item_class->get( $item );
		} elseif ( is_object( $item ) ) {
			$item = $item_class->set( $item );
		}

		if ( empty( $item ) ) {
			return new \WP_Error( 'unsaved', 'Please create the relationship before updating it.' );
		}

		return $item->update( $arguments );
	}
}
