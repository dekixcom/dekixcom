<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */

/**
 * MinervaKB custom DB tables schema and internal API
 * Class MKB_DbModel
 */
class MKB_DbModel {

	// tables plugin prefix
	const PLUGIN_PREFIX = 'mkb_';

	// table names
	const KEYWORDS_TABLE_NAME = 'keywords';
	const HITS_TABLE_NAME = 'hits';
	const HITS_META_TABLE_NAME = 'hits_meta';

	// hit types
	const HIT_TYPE_SEARCH = 0;
	const HIT_TYPE_LIKE = 1;
	const HIT_TYPE_DISLIKE = 2;
	const HIT_TYPE_FEEDBACK = 3;

	/**
	 * Tracks analytics event
	 * @param $hit_type
	 * @param $hit_data
	 */
	public static function register_hit($hit_type, $hit_data) {
		switch ($hit_type) {
			case self::HIT_TYPE_SEARCH:
				self::save_search_hit($hit_data);
				break;

			default:
				break;
		}
	}

	/**
	 * Saves search hit and all related metadata
	 * @param $data
	 */
	private static function save_search_hit($data) {

		$keyword = $data["keyword"];
		$results_count = $data["results_count"];
		$results_ids = $data["results_ids"];

		global $wpdb;

		$keyword_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM " . self::get_table_name_for( self::KEYWORDS_TABLE_NAME ) . "
                    WHERE keyword = %s LIMIT 1",
				$keyword
			)
		);

		if (!$keyword_id) { // does not exist, save to db
			// save keyword
			$wpdb->insert(
				self::get_table_name_for( self::KEYWORDS_TABLE_NAME ),
				array(
					'keyword' => strtolower($keyword)
				),
				array(
					'%s'
				)
			);

			$keyword_id = $wpdb->insert_id;
		}

		$creation_timestamp = current_time( 'mysql' );

		// save hits
		$wpdb->insert(
			self::get_table_name_for( self::HITS_TABLE_NAME ),
			array(
				'type' => self::HIT_TYPE_SEARCH,
				'keyword_id'  => $keyword_id,
				'registered_at'  => $creation_timestamp,
			),
			array(
				'%d',
				'%s',
				'%s'
			)
		);

		$hit_id = $wpdb->insert_id;

		// save hit result count
		$wpdb->insert(
			self::get_table_name_for( self::HITS_META_TABLE_NAME ),
			array(
				'hit_id'  => $hit_id,
				'meta_key'  => "results_count",
				'meta_value'  => $results_count,
			),
			array(
				'%d',
				'%s',
				'%d'
			)
		);

		if ($results_count) {
			// save hit result ids
			$wpdb->insert(
				self::get_table_name_for( self::HITS_META_TABLE_NAME ),
				array(
					'hit_id'  => $hit_id,
					'meta_key'  => "results_ids",
					'meta_value'  => json_encode($results_ids),
				),
				array(
					'%d',
					'%s',
					'%s'
				)
			);
		}
	}

	/**
	 * Gets search statistics ordered by most searched keyword
	 * @return array|null|object
	 */
	public static function get_top_keywords($order_options = array()) {
		global $wpdb;

		$keywords_table_name = self::get_table_name_for( self::KEYWORDS_TABLE_NAME );
		$hits_table_name = self::get_table_name_for( self::HITS_TABLE_NAME );
		$hits_meta_table_name = self::get_table_name_for( self::HITS_META_TABLE_NAME );

        $results = array();
        $field = 'hits';
        $order = 'DESC';
        $offset = 0;

        if (!empty($order_options)) {
            if (isset($order_options['field'])) {
                $field = $order_options['field'];
            }

            if (isset($order_options['order']) && in_array($order_options['order'], array('ASC', 'DESC'))) {
                $order = $order_options['order'];
            }

            if (isset($order_options['offset'])) {
                $offset = $order_options['offset'];
            }
        }

        if ($field === 'keyword') {
            /**
             * Keywords [A-Z]
             */
            $results = $wpdb->get_results(
                $wpdb->prepare(
            "SELECT id, keyword
                    FROM $keywords_table_name
                    ORDER BY keyword $order
                    LIMIT %d,20;",
                    $offset
                )
            );

            foreach($results as $result) {
                $latest_hit = self::get_keyword_latest_hit($result->id)[0];

                $result->hit_id = $latest_hit->id;
                $result->last_search = $latest_hit->last_search;
                $result->last_results = self::get_hit_results_count($latest_hit->id);
                $result->hit_count = self::get_keyword_hit_count($result->id);
            }
        } else if ($field === 'hits') {
            /**
             * Hit count
             */
            $results = $wpdb->get_results(
                $wpdb->prepare(
            "SELECT
                    id as hit_id,
                    keyword_id as id,
                    registered_at as last_search,
                    COUNT(keyword_id) AS hit_count
                    FROM $hits_table_name
                    GROUP BY keyword_id
                    ORDER BY hit_count $order
                    LIMIT %d,20;",
                    $offset
                )
            );

            foreach($results as $result) {
                $result->keyword = self::get_keyword_by_id($result->id);
                $result->last_results = self::get_hit_results_count($result->hit_id);
            }
        } else if ($field === 'results') {
            /**
             * Results count
             */
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    // NOTE: must have only 2 cols to use DISTINCT
            "SELECT DISTINCT h.keyword_id as id, hm.meta_value as last_results
                    FROM $hits_table_name AS h
                        LEFT JOIN $hits_meta_table_name AS hm ON h.id=hm.hit_id
                    WHERE hm.meta_key='results_count'
                    ORDER BY hm.meta_value+0 $order
                    LIMIT %d,20;",
                    $offset
                )
            );

            foreach($results as $result) {
                $result->keyword = self::get_keyword_by_id($result->id);

                $latest_hit = self::get_keyword_latest_hit($result->id)[0];

                $result->hit_id = $latest_hit->id;
                $result->last_search = $latest_hit->last_search;
                $result->hit_count = self::get_keyword_hit_count($result->id);
            }
        } else if ($field === 'date') {
            /**
             * Date
             */
            $results = $wpdb->get_results(
                $wpdb->prepare(
            "SELECT
                    id as hit_id,
                    keyword_id as id,
                    registered_at as last_search
                    FROM $hits_table_name
                    ORDER BY registered_at $order
                    LIMIT %d,20;",
                    $offset
                )
            );

            foreach($results as $result) {
                $result->keyword = self::get_keyword_by_id($result->id);
                $result->last_results = self::get_hit_results_count($result->hit_id);
                $result->hit_count = self::get_keyword_hit_count($result->id);
            }
        }

		return $results;
	}

    /**
     * Gets hit info by id
     * @param $hit_id
     * @return array|object|void|null
     */
    private static function get_hit_by_id($hit_id) {
        global $wpdb;

        $hits_table_name = self::get_table_name_for( self::HITS_TABLE_NAME );

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT keyword_id as id, registered_at as last_search
                        FROM $hits_table_name
                        WHERE id=%d
                        LIMIT 1;",
                $hit_id
            )
        );
    }

    /**
     * Gets keyword total
     */
    public static function get_keyword_total() {
        global $wpdb;

        $keywords_table_name = self::get_table_name_for( self::KEYWORDS_TABLE_NAME );

        return $wpdb->get_var(
            "SELECT count(*) FROM $keywords_table_name;"
        );
    }

    /**
     * Gets keyword by id
     * @param $keyword_id
     */
	private static function get_keyword_by_id($keyword_id) {
        global $wpdb;

        $keywords_table_name = self::get_table_name_for( self::KEYWORDS_TABLE_NAME );

        return $wpdb->get_var(
            $wpdb->prepare(
                "SELECT keyword FROM $keywords_table_name WHERE id=%d LIMIT 1;", $keyword_id
            )
        );
    }

    /**
     * Gets total keyword hits
     * @param $keyword_id
     * @return int
     */
    private static function get_keyword_hit_count($keyword_id) {
        global $wpdb;

        $hits_table_name = self::get_table_name_for( self::HITS_TABLE_NAME );

        return $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM $hits_table_name WHERE keyword_id=%d;", $keyword_id)
        );
    }

    /**
     * Gets latest hit for keyword
     * @param $keyword_id
     * @return array
     */
    private static function get_keyword_latest_hit($keyword_id) {
        global $wpdb;

        $hits_table_name = self::get_table_name_for( self::HITS_TABLE_NAME );

        return $wpdb->get_results(
            $wpdb->prepare("SELECT id, registered_at as last_search FROM $hits_table_name WHERE keyword_id=%d ORDER BY id DESC LIMIT 1;", $keyword_id)
        );
    }

    /**
     * Gets results count for hit
     * @param $hit_id
     * @return int
     */
    private static function get_hit_results_count($hit_id) {
        global $wpdb;

        $hits_meta_table_name = self::get_table_name_for( self::HITS_META_TABLE_NAME );

        return $wpdb->get_var(
            $wpdb->prepare("SELECT meta_value FROM $hits_meta_table_name WHERE meta_key='results_count' AND hit_id=%d;", $hit_id)
        );
    }

	/**
	 * Gets results for specific search hit
	 * @param $hit_id
	 *
	 * @return array|mixed|object
	 */
	public static function get_search_hit_results($hit_id) {
		global $wpdb;

		$hits_table_name = self::get_table_name_for( self::HITS_TABLE_NAME );
		$hits_meta_table_name = self::get_table_name_for( self::HITS_META_TABLE_NAME );
		$hit_type = self::HIT_TYPE_SEARCH;

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
					hm.meta_value AS results
				FROM $hits_table_name AS h
					LEFT JOIN $hits_meta_table_name AS hm ON hm.hit_id=h.id AND hm.meta_key='results_ids'
				WHERE h.type=%d AND h.id=%d;",
				$hit_type,
				$hit_id
			)
		);

		return $results && isset($results[0]) ? json_decode($results[0]->results, true) : array();
	}

	/**
	 * Removes all search data
	 */
	public static function reset_search_data() {
		self::delete_schema();
		self::create_schema();
	}

	/**
	 * Helper to build table names
	 * @param $name
	 *
	 * @return string
	 */
	private static function get_table_name_for( $name ) {
		global $wpdb;

		return $wpdb->prefix . self::PLUGIN_PREFIX . $name;
	}

	/**
	 * For use in WP SQL filters
	 * @param $name
	 *
	 * @return string
	 */
	public static function get_wp_table_name_for( $name ) {
		global $wpdb;

		return $wpdb->prefix . $name;
	}

	/**
	 * Helper to get table charset and collate
	 * @return string
	 */
	private static function get_wp_charset_collate() {
		global $wpdb;
		$charset_collate = '';

		if ( ! empty( $wpdb->charset ) ) {
			$charset_collate = " DEFAULT CHARACTER SET $wpdb->charset";
		}

		if ( ! empty( $wpdb->collate ) ) {
			$charset_collate .= " COLLATE $wpdb->collate";
		}

		return $charset_collate;
	}

	/**
	 * Gets SQL to create keywords table
	 * @return string
	 */
	private static function get_keywords_structure() {
		$table_name = self::get_table_name_for( self::KEYWORDS_TABLE_NAME );

		return "CREATE TABLE $table_name (
		      id int unsigned NOT NULL auto_increment,
		      keyword varchar(128) default '',
		      PRIMARY KEY id (id),
		      UNIQUE KEY keyword (keyword)
		    )";
	}

	/**
	 * Gets SQL to create hits table
	 * @return string
	 */
	private static function get_hits_structure() {
		$table_name = self::get_table_name_for( self::HITS_TABLE_NAME );

		return "CREATE TABLE $table_name (
		      id int unsigned NOT NULL auto_increment,
		      article_id int default NULL,
		      keyword_id int default NULL,
		      type int NOT NULL,
		      registered_at datetime NOT NULL,
		      PRIMARY KEY  (id)
		    )";
	}

	/**
	 * Gets SQL to create hits meta table
	 * @return string
	 */
	private static function get_hits_meta_structure() {
		$table_name = self::get_table_name_for( self::HITS_META_TABLE_NAME );

		return "CREATE TABLE $table_name (
		      id int unsigned NOT NULL auto_increment,
		      hit_id int unsigned NOT NULL,
		      meta_key varchar(255) NOT NULL,
		      meta_value longtext default '',
		      PRIMARY KEY  (id)
		    )";
	}

	public static function get_all_table_names() {
		return array(
			self::get_table_name_for( self::KEYWORDS_TABLE_NAME ),
			self::get_table_name_for( self::HITS_TABLE_NAME ),
			self::get_table_name_for( self::HITS_META_TABLE_NAME )
		);
	}

	/**
	 * Gets join and where clauses for tag search results
	 * @return array
	 */
	public static function get_search_tags_join_clauses($search) {
		$posts = self::get_wp_table_name_for('posts');

		$rel = self::get_wp_table_name_for('term_relationships');
		$rel_alias = self::PLUGIN_PREFIX . $rel;

		$tax = self::get_wp_table_name_for('term_taxonomy');
		$tax_alias = self::PLUGIN_PREFIX . $tax;

		$terms = self::get_wp_table_name_for('terms');
		$terms_alias = self::PLUGIN_PREFIX . $terms;

		return array(
			"join" => "LEFT JOIN $rel AS $rel_alias
				ON $posts.id = $rel_alias.object_id
				LEFT JOIN $tax AS $tax_alias
				ON $rel_alias.term_taxonomy_id = $tax_alias.term_id
				LEFT JOIN $terms AS $terms_alias
				ON $rel_alias.term_taxonomy_id = $terms_alias.term_id ",

			"where" => " OR ($tax_alias.taxonomy = '" . esc_sql(MKB_Options::option( 'article_cpt_tag' )) .
			           "' AND $terms_alias.name = '" . esc_sql($search) .
			           "' AND $posts.post_title NOT LIKE '%" . esc_sql($search) . "%'" .
			           " AND $posts.post_excerpt NOT LIKE '%" . esc_sql($search) . "%'" .
			           " AND $posts.post_content NOT LIKE '%" . esc_sql($search) . "%'" .
			           ") "
		);
	}

	public static function get_all_article_ids() {
        $posts = self::get_wp_table_name_for('posts');

        global $wpdb;

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT ID FROM $posts WHERE post_type=%s;",
                MKB_Options::option('article_cpt')
            )
        );

        return $results;
    }

	/**
	 * Creates custom tables DB schema (to be called on plugin activation)
	 */
	public static function create_schema() {
		$wp_charset_collate = self::get_wp_charset_collate();
		$sql_postfix = $wp_charset_collate . ';';

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		dbDelta( self::get_keywords_structure() . $sql_postfix );
		dbDelta( self::get_hits_structure() . $sql_postfix );
		dbDelta( self::get_hits_meta_structure() . $sql_postfix );
	}

	/**
	 * Deletes all custom tables
	 */
	public static function delete_schema() {
		global $wpdb;

		if ( !current_user_can( 'administrator' ) ) {
			wp_die();
		}

		$wpdb->query( 'DROP TABLE IF EXISTS ' . self::get_table_name_for( self::KEYWORDS_TABLE_NAME ) );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . self::get_table_name_for( self::HITS_TABLE_NAME ) );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . self::get_table_name_for( self::HITS_META_TABLE_NAME ) );
	}
}

// delete the table whenever a blog is deleted
function mkb_on_delete_blog( $tables ) {
	$mkb_tables = MKB_DbModel::get_all_table_names();

	foreach($mkb_tables as $table) {
		$tables[] = $table;
	}

	return $tables;
}
add_filter( 'wpmu_drop_tables', 'mkb_on_delete_blog' );