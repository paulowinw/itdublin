<?php

namespace PrestoPlayer\Support;

class BlockFinder {

	protected $namespace;

	public function __construct( $namespace = 'presto-player' ) {
		$this->namespace = $namespace;
	}

	public function find() {
		$blocks = array();

		/**
		 * Get an array of all of our post types, then we will
		 * remove any unwanted post types
		 */
		$post_types = get_post_types(
			array(
				'public'  => true,
				'show_ui' => true,
			)
		);

		array_push( $post_types, 'wp_block' );
		unset( $post_types['attachment'] );

		/**
		 * Get a list of all post ids
		 */
		$post_ids = array();

		foreach ( $post_types as $key => $post_type ) {
			$posts = get_posts(
				array(
					'posts_per_page' => -1,
					'post_type'      => $post_type,
					'fields'         => 'ids',
				)
			);
			foreach ( $posts as $id ) {
				array_push( $post_ids, $id );
			}
		}

		/**
		 * Loop through post IDs and get the blocks that are used.
		 */
		foreach ( $post_ids as $post_ID ) {
			$post = get_post( $post_ID );

			if ( ! has_blocks( $post->post_content ) ) {
				continue;
			}

			$post_blocks = parse_blocks( $post->post_content );

			foreach ( $post_blocks as $block ) {
				$this->findBlocks( $block, $blocks, $post );
			}
		}

		$data = array(
			'blocks' => $blocks,
		);

		return $data;
	}

	/**
	 * Searches an array for a value.
	 *
	 * @param array  $array - Array to search through.
	 * @param string $field - Key to search.
	 * @param string $value - Value to search in key.
	 *
	 * @return array/boolean
	 */
	function searchForBlockKey( $array, $field, $value ) {
		foreach ( $array as $key => $val ) {
			if ( $val[ $field ] === $value ) {
				return $key;
			}
		}
		return false;
	}

	public function findBlocks( $block, &$blocks, &$post, $nested_block_name = null ) {

		/**
		 * If the block name is blank, skip
		 */
		if ( strlen( $block['blockName'] ) === 0 ) {
			return;
		}

		/**
		 * If the block is reusable, skip
		 */
		if ( 'core/block' === $block['blockName'] ) {
			return;
		}

		foreach ( $block['innerBlocks'] as $inner_block ) {
			$this->findBlocks( $inner_block, $blocks, $post, $block['blockName'] );
		}

		/**
		 * If block is not in blocks array, push the
		 * blockName into the array.
		 */
		if ( ! in_array( $block['blockName'], array_column( $blocks, 'name' ), true ) ) {
			$block_array = array(
				'name'  => $block['blockName'],
				'posts' => array(),
			);

			array_push( $blocks, $block_array );
		}

		$block_key = $this->searchForBlockKey( $blocks, 'name', $block['blockName'] );

		if ( ! in_array( $post->ID, array_column( $blocks[ $block_key ]['posts'], 'id' ), true ) ) {
			$blocks[ $block_key ]['posts'][] = array(
				'id'              => $post->ID,
				'title'           => $post->post_title,
				'count'           => 1,
				'isReusable'      => 'wp_block' === $post->post_type,
				'isNested'        => $nested_block_name !== null,
				'nestedBlockType' => $nested_block_name,
				'postType'        => $post->post_type,
				'post_url'        => get_permalink( $post->ID ),
				'edit_url'        => home_url( '/wp-admin/post.php?post=' . $post->ID . '&action=edit' ),
			);
		} else {
			$post_key = $this->searchForBlockKey( $blocks[ $block_key ]['posts'], 'id', $post->ID );
			++$blocks[ $block_key ]['posts'][ $post_key ]['count'];
		}
	}
}
