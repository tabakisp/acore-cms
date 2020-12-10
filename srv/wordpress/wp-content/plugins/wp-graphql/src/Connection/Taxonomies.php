<?php

namespace WPGraphQL\Connection;

use WPGraphQL\Data\Connection\TaxonomyConnectionResolver;
use WPGraphQL\Model\PostType;
use WPGraphQL\Model\Term;

class Taxonomies {
	public static function register_connections() {

		register_graphql_connection(
			[
				'fromType'      => 'RootQuery',
				'toType'        => 'Taxonomy',
				'fromFieldName' => 'taxonomies',
				'resolve'       => function( $source, $args, $context, $info ) {
					$resolver = new TaxonomyConnectionResolver( $source, $args, $context, $info );
					return $resolver->get_connection();
				},
			]
		);

		$taxonomies = get_taxonomies( [ 'show_in_graphql' => true ], 'OBJECT' );

		if ( is_array( $taxonomies ) && ! empty( $taxonomies ) ) {
			foreach ( $taxonomies as $taxonomy ) {
				register_graphql_connection(
					[
						'fromType'      => $taxonomy->graphql_single_name,
						'toType'        => 'Taxonomy',
						'fromFieldName' => 'taxonomy',
						'oneToOne'      => true,
						'resolve'       => function( Term $source, $args, $context, $info ) {
							if ( empty( $source->taxonomyName ) ) {
								return null;
							}
							$resolver = new TaxonomyConnectionResolver( $source, $args, $context, $info );
							$resolver->setQueryArg( 'name', $source->taxonomyName );
							return $resolver->get_connection();
						},
					]
				);
			}
		}

		register_graphql_connection(
			[
				'fromType'      => 'ContentType',
				'toType'        => 'Taxonomy',
				'fromFieldName' => 'connectedTaxonomies',
				'resolve'       => function( PostType $source, $args, $context, $info ) {
					if ( empty( $source->taxonomies ) ) {
						return null;
					}
					$resolver = new TaxonomyConnectionResolver( $source, $args, $context, $info );
					$resolver->setQueryArg( 'in', $source->taxonomies );
					return $resolver->get_connection();
				},
			]
		);

	}
}