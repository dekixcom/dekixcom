<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */

class MinervaKB_ArticleEdit implements KST_EditScreen_Interface {

	private $restrict;

	const ATTACHMENTS_TRACKING_OPTION_KEY = 'minerva-kb-attachments-tracking';

	/**
	 * Constructor
	 */
	public function __construct($deps) {
		$this->setup_dependencies($deps);

        add_action('current_screen', array($this, 'page_setup'));
	}

	public function page_setup() {
        $screen = get_current_screen();

        if (isset($screen) && ($screen->base == 'post' || $screen->base == 'edit') && $screen->post_type == MKB_Options::option( 'article_cpt' )) {
            add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
            add_action('save_post', array($this, 'save_post'));
            add_action('admin_footer', array($this, 'article_tmpl'), 30);
        }
    }

	/**
	 * Sets up dependencies
	 * @param $deps
	 */
	private function setup_dependencies($deps) {
		if (isset($deps['restrict'])) {
			$this->restrict = $deps['restrict'];
		}
	}

	/**
	 * Register article meta box(es).
	 */
	public function add_meta_boxes() {

		// attachments meta box
		add_meta_box(
			'mkb-article-meta-attachments-id',
			__( 'Article attachments', 'minerva-kb' ),
			array($this, 'attachments_html'),
			MKB_Options::option( 'article_cpt' ),
			'normal',
			'high'
		);

		// feedback meta box
		add_meta_box(
			'mkb-article-meta-related-id',
			__( 'Related articles', 'minerva-kb' ),
			array($this, 'related_html'),
			MKB_Options::option( 'article_cpt' ),
			'normal',
			'high'
		);

		// feedback meta box
		add_meta_box(
			'mkb-article-meta-feedback-id',
			__( 'Article feedback', 'minerva-kb' ),
			array($this, 'feedback_html'),
			MKB_Options::option( 'article_cpt' ),
			'normal',
			'high'
		);

		// restrict meta box
		if (MKB_Options::option('restrict_on')) {
			add_meta_box(
				'mkb-article-meta-restrict-id',
				__( 'MinervaKB: Restrict access', 'minerva-kb' ),
				array($this, 'restrict_html'),
				MKB_Options::option( 'article_cpt' ),
				'normal',
				'high'
			);
		}

		// reset meta box
		add_meta_box(
			'mkb-article-meta-reset-id',
			__( 'MinervaKB: Reset article stats', 'minerva-kb' ),
			array($this, 'reset_html'),
			MKB_Options::option( 'article_cpt' ),
			'side',
			'default'
		);
	}

	/**
	 * Restrict access settings
	 * @param $post
	 */
	public function restrict_html( $post ) {

		$settings_helper = new MKB_SettingsBuilder(array(
			'post' => true,
			'no_tabs' => true
		));

		$options = array(
			array(
				'id' => 'mkb_article_access_role',
				'type' => 'roles_select',
				'label' => __( 'Content restriction: who can view article?', 'minerva-kb' ),
				'default' => 'none',
				'description' => __('You can restrict access for specific articles or for whole topics.', 'minerva-kb')
			),
		);

		foreach ( $options as $option ):

			$value = '';

			switch ($option["id"]) {
				case 'mkb_article_access_role':
					$value = stripslashes(get_post_meta(get_the_ID(), '_mkb_restrict_role', true));
					$value = $value ? $value : "none";
					break;

				default:
					break;
			}

			$settings_helper->render_option(
				$option["type"],
				$value,
				$option
			);

		endforeach;

	}

	/**
	 * Reset stats settings
	 * @param $post
	 */
	public function reset_html( $post ) {

		$options = array(
			array(
				'id' => 'views',
				'type' => 'checkbox',
				'label' => __( 'Reset views?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'likes',
				'type' => 'checkbox',
				'label' => __( 'Reset likes?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'dislikes',
				'type' => 'checkbox',
				'label' => __( 'Reset dislikes?', 'minerva-kb' ),
				'default' => false
			)
		);
		$settings_helper = new MKB_SettingsBuilder(array("no_tabs" => true));

		?>
		<div class="mkb-clearfix">
			<div class="mkb-settings-content fn-mkb-article-reset-form mkb-article-reset-form">
				<form action="" novalidate>
					<?php
					foreach ($options as $option):
						$settings_helper->render_option(
							$option["type"],
							$option["default"],
							$option
						);
					endforeach;
					?>
					<a href="#" class="mkb-action-button mkb-action-danger fn-mkb-article-reset-stats-btn"
					   data-id="<?php esc_attr_e(get_the_ID()); ?>"
					   title="<?php esc_attr_e('Reset data', 'minerva-kb'); ?>"><?php echo __('Reset data', 'minerva-kb'); ?></a>
				</form>
			</div>
		</div>
	<?php

	}

	/**
	 * Article feedback list
	 * @param $post
	 */
	public function feedback_html( $post ) {
		$feedback_args = array(
			'posts_per_page'   => - 1,
			'offset'           => 0,
			'category'         => '',
			'category_name'    => '',
			'orderby'          => 'DATE',
			'order'            => 'DESC',
			'include'          => '',
			'exclude'          => '',
			'meta_key'         => 'feedback_article_id',
			'meta_value'       => get_the_ID(),
			'post_type'        => 'mkb_feedback',
			'post_mime_type'   => '',
			'post_parent'      => '',
			'author'           => '',
			'author_name'      => '',
			'post_status'      => 'publish'
		);

		$feedback = get_posts( $feedback_args );

		if ( sizeof( $feedback ) ):
			foreach ( $feedback as $item ):
                $email = get_post_meta($item->ID, 'feedback_email', true);
				?>
				<div class="mkb-article-feedback-item">
					<div class="mkb-article-feedback-item-inner">
						<a href="#"
						   data-id="<?php echo esc_attr( $item->ID ); ?>"
						   class="fn-remove-feedback mkb-article-feedback-item-remove"
						   title="<?php esc_attr_e( 'Remove this entry?', 'minerva-kb' ); ?>">
							<i class="fa fa-close"></i>
						</a>
						<h4>
                            <?php echo esc_html( __('Submitted:', 'minerva-kb' ) ); ?> <?php echo esc_html( $item->post_date ); ?>
                            <?php if($email): ?>
                                , <?php esc_html_e( __('email:', 'minerva-kb' ) ); ?> <a href="mailto:<?php esc_attr_e($email); ?>"><?php esc_html_e($email); ?></a>
                            <?php endif; ?>
                        </h4>

						<p><?php echo esc_html( $item->post_content ); ?></p>
					</div>
				</div>
			<?php
			endforeach;
		else:
			?>
			<p><?php echo esc_html( __( 'No feedback was submitted for this article', 'minerva-kb' ) ); ?></p>
		<?php
		endif;
	}

	/**
	 * Article attachments list
	 * @param $post
	 */
	public function attachments_html( $post ) {
		?>
        <p>Media library <span class="mkb-attachment-color-sample" style="background: white;"></span> External file <span class="mkb-attachment-color-sample" style="background: rgba(25, 255, 0, 0.15);"></span></p>
		<div class="mkb-article-attachments js-mkb-attachments"><?php _e('Loading attachments info...', 'minerva-kb'); ?></div>
		<div class="mkb-attachments-actions">
			<a href="#"
			   id="mkb_add_article_attachment"
			   data-id="<?php echo esc_attr(get_the_ID()); ?>"
			   class="button button-primary button-large js-mkb-add-attachment"
			   title="<?php esc_attr_e('Add article attachment(s)', 'minerva-kb'); ?>">
				<?php _e('Add article attachment(s)', 'minerva-kb'); ?>
			</a>
            <a href="#"
               style="margin-left: 0.5em;"
               id="mkb_add_article_attachment_ext"
               data-id="<?php echo esc_attr(get_the_ID()); ?>"
               class="button button-large js-mkb-add-attachment-external"
               title="<?php esc_attr_e('Add external link', 'minerva-kb'); ?>">
                <?php _e('Add external link', 'minerva-kb'); ?>
            </a>
		</div>
	<?php
	}

	/**
	 * Related articles meta boxes html
	 * @param $post
	 */
	function related_html( $post ) {
		$related = get_post_meta(get_the_ID(), '_mkb_related_articles', true);

		?>
		<?php wp_nonce_field( 'mkb_save_article', 'mkb_save_article_nonce' ); ?>
		<div class="mkb-related-articles js-mkb-related-articles">
			<?php
			if ($related && is_array($related) && !empty($related)):

				$query_args = array(
					'post_type' => MKB_Options::option( 'article_cpt' ),
					'post__not_in' => array( get_the_ID() ),
					'posts_per_page' => -1
				);

				$related_articles = get_posts( $query_args );

				$articles_list = array();

                if ( sizeof( $related_articles ) ):
                    foreach ( $related_articles as $item ):
                        array_push( $articles_list, array(
							"title"  => $item->post_title,
							"id"   => $item->ID
						) );
                    endforeach;
                endif;

				wp_reset_postdata();

				foreach($related as $article_id):
					?>
					<div class="mkb-related-articles__item js-mkb-related-articles-item">
                        <a class="mkb-related-current js-mkb-related-current" href="<?php esc_attr_e(get_the_permalink($article_id)); ?>" target="_blank" title="Open in new tab">
                            <span class="js-mkb-related-current-title"><?php esc_html_e(get_the_title($article_id)); ?></span><i class="fa fa-external-link mkb-related-current-link-icon"></i>
                        </a>

                        <div class="mkb-related-article-search">
                            <input type="text" class="js-mkb-related-article-search-input mkb-related-article-search-input" placeholder="Type to search for articles">
                            <a href="#" class="mkb-related-edit-cancel js-mkb-related-edit-cancel">Cancel</a>
                            <ul class="mkb-related-article-search-results js-mkb-related-article-search-results"></ul>
                        </div>

                        <a href="#" class="mkb-related-edit js-mkb-related-edit">Edit</a>
                        <input type="hidden" class="js-mkb-related-article-id mkb-related-articles__store" name="mkb_related_articles[]" value="<?php esc_attr_e($article_id); ?>" />
						<a class="mkb-related-articles__item-remove js-mkb-related-remove mkb-unstyled-link" href="#">
							<i class="fa fa-close"></i>
						</a>
					</div>
				<?php
				endforeach;
			else:
				?>
				<div class="js-mkb-no-related-message mkb-no-related-message">
					<p><?php echo esc_html( __('No related articles selected', 'minerva-kb' ) ); ?></p>
				</div>
			<?php
			endif;
			?>
		</div>
		<div class="mkb-related-actions">
			<a href="#"
			   id="mkb_add_related_article"
			   data-id="<?php echo esc_attr(get_the_ID()); ?>"
			   class="button button-primary button-large"
			   title="<?php esc_attr_e('Add related article', 'minerva-kb'); ?>">
				<?php _e('Add related article', 'minerva-kb'); ?>
			</a>
		</div>
	<?php
	}

	public static function get_attachment_icon_config($attachment) {
	    if (!isset($attachment['filename'])) {
            return self::get_attachments_icon_default();
        }

		$attach_icon_map = self::get_attachments_icon_map();
		$extension = pathinfo($attachment['filename'], PATHINFO_EXTENSION);

		foreach($attach_icon_map as $config_item) {
			if (in_array($extension, $config_item['extension'])) {
				return $config_item;
			} else if (sizeof($config_item['mime'])) {
				$mime_base = isset($config_item['mimeBase']) ? $config_item['mimeBase'] : null;

				if ($mime_base) {
					$config_item_mimes = array_map(function($item) use ($mime_base) {
						return $mime_base . '/' . $item;
					}, $config_item['mime']);

					if (in_array($attachment['mime'], $config_item_mimes)) {
						return $config_item;
					}
				} else if (in_array($attachment['subtype'], $config_item['mime'])) {
					return $config_item;
				}
			}
		}

		return self::get_attachments_icon_default();
	}

    /**
     * NOTE: is used for admin assets.php js data, as well as for frontend
     * @return array
     */
	public static function get_attachments_data() {
		$attachment_ids = get_post_meta(get_the_ID(), '_mkb_article_attachments', true);

		$attachments_data = array();

		if (!empty($attachment_ids)) {
			foreach($attachment_ids as $id) {
				if (!$id) { continue; }

				if (strpos($id, 'EXTERNAL') !== false) {
				    // external

				    $url = str_replace('EXTERNAL{{', '', $id);
                    $url = preg_replace('/}}$/', '', $url);
                    $label = '';
                    $size = '';

                    // NOTE: order MUST be reverse to prevent broad matches
                    if (strpos($url, 'SIZE{{') !== false) {
                        preg_match('/SIZE{{(.*)}}/', $url, $size);
                        $size = sizeof($size) > 1 ? $size[1] : '';
                        $url = preg_replace('/SIZE{{.*}}/', '', $url);
                    }

                    if (strpos($url, 'LABEL{{') !== false) {
                        preg_match('/LABEL{{(.*)}}/', $url, $label);
                        preg_match('/LABEL{{(.*)}}/', $url, $label);
                        $label = sizeof($label) > 1 ? $label[1] : '';
                        $url = preg_replace('/LABEL{{.*}}/', '', $url);
                    }

                    $item_data = array(
                        'filename' => $url,
                        'url' => $url,
                        'title' => $label ? $label : $url,
                        'customLabel' => $label,
                        'id' => $id,
                        'filesizeHumanReadable' => $size ?
                            $size :
                            (is_admin() ? __('External link', 'minerva-kb') : ''),
                        'mime' => '',
                        'subtype' => '',
                        'isExternal' => true
                    );

                    $icon_config = self::get_attachment_icon_config($item_data);

                    $item_data['icon'] = $icon_config['icon'];
                    $item_data['color'] = $icon_config['color'];

                    array_push($attachments_data, $item_data);
                } else {
                    // media library

                    $item_data = wp_prepare_attachment_for_js($id);
                    $item_data['isExternal'] = false;
                    $icon_config = self::get_attachment_icon_config($item_data);

                    $item_data['icon'] = $icon_config['icon'];
                    $item_data['color'] = $icon_config['color'];

                    array_push($attachments_data, $item_data);
                }
			}
		}

		return $attachments_data;
	}

	public static function get_attachments_tracking_data() {
		return json_decode(get_option(self::ATTACHMENTS_TRACKING_OPTION_KEY, '[]'), true);
	}

	private static function save_attachments_tracking_data($data) {
		return update_option(self::ATTACHMENTS_TRACKING_OPTION_KEY, json_encode($data));
	}

	public static function track_attachment_download($id) {
		$tracking_data = self::get_attachments_tracking_data();

		if (!isset($tracking_data[$id])) {
			$tracking_data[$id] = array();
		}

		if (!isset($tracking_data[$id]['downloads'])) {
			$tracking_data[$id]['downloads'] = 0;
		}

		$tracking_data[$id]['downloads'] = (int) $tracking_data[$id]['downloads'] + 1;

		self::save_attachments_tracking_data($tracking_data);
	}

	public static function get_attachments_icon_map() {
		return array(
			array(
				'icon' => MKB_Options::option('attach_archive_icon'),
				'description' => __('Archive file', 'minerva-kb' ),
				'color' => MKB_Options::option('attach_archive_color'),
				'extension' => array('zip', 'rar', '7z', 'bz', 'bz2'),
				'mime' => array('zip', 'x-7z-compressed', 'x-rar-compressed', 'x-bzip', 'x-bzip2')
			),
			array(
				'icon' => MKB_Options::option('attach_text_icon'),
				'description' => __('Text file', 'minerva-kb' ),
				'color' => MKB_Options::option('attach_text_color'),
				'extension' => array('txt', 'md'),
				'mimeBase' => 'text',
				'mime' => array('plain', 'markdown')
			),
			array(
				'icon' => MKB_Options::option('attach_pdf_icon'),
				'description' => __('Adobe PDF file', 'minerva-kb' ),
				'color' => MKB_Options::option('attach_pdf_color'),
				'extension' => array('pdf'),
				'mime' => array('pdf')
			),
			array(
				'icon' => MKB_Options::option('attach_image_icon'),
				'description' => __('Image file', 'minerva-kb' ),
				'color' => MKB_Options::option('attach_image_color'),
				'extension' => array('jpg', 'jpeg', 'jpe', 'png', 'tif', 'tiff', 'gif', 'bmp', 'svg'),
				'mimeBase' => 'image',
				'mime' => array('jpeg', 'png', 'gif', 'svg+xml', 'tiff', 'bmp')
			),
			array(
				'icon' => MKB_Options::option('attach_excel_icon'),
				'description' => __('Office spreadsheet document', 'minerva-kb' ),
				'color' => MKB_Options::option('attach_excel_color'),
				'extension' => array('xls', 'xlsx', 'xlt', 'xla', 'xltx', 'xlsm', 'xltm', 'xlam', 'xlsb', 'csv', 'ods', 'ots'),
				'mime' => array(
					'vnd.ms-excel',
					'vnd.ms-excel.sheet.macroEnabled.12',
					'vnd.ms-excel.template.macroEnabled.12',
					'vnd.ms-excel.addin.macroEnabled.12',
					'vnd.ms-excel.sheet.binary.macroEnabled.12',
					'vnd.openxmlformats-officedocument.spreadsheetml.sheet',
					'vnd.openxmlformats-officedocument.spreadsheetml.template',
					'vnd.oasis.opendocument.spreadsheet',
					'vnd.oasis.opendocument.spreadsheet-template',
				)
			),
			array(
				'icon' => MKB_Options::option('attach_word_icon'),
				'description' => __('Office text document', 'minerva-kb' ),
				'color' => MKB_Options::option('attach_word_color'),
				'extension' => array('doc', 'docx', 'dot', 'dotx', 'docm', 'dotm', 'rtf', 'odt', 'ott'),
				'mime' => array(
					'msword',
					'vnd.openxmlformats-officedocument.wordprocessingml.document',
					'vnd.openxmlformats-officedocument.wordprocessingml.template',
					'vnd.ms-word.document.macroEnabled.12',
					'vnd.ms-word.template.macroEnabled.12',
					'vnd.oasis.opendocument.text',
					'vnd.oasis.opendocument.text-template',
				)
			),
			array(
				'icon' => MKB_Options::option('attach_video_icon'),
				'description' => __('Video file', 'minerva-kb' ),
				'color' => MKB_Options::option('attach_video_color'),
				'extension' => array('mpeg', 'mov', 'avi', 'ogv', 'webm', '3gp', '3g2', 'wmv'),
				'mimeBase' => 'video',
				'mime' => array('webm', 'quicktime', 'x-msvideo', 'mpeg', 'ogg', '3gpp', '3gpp2', 'x-ms-wmv')
			),
			array(
				'icon' => MKB_Options::option('attach_audio_icon'),
				'description' => __('Audio file', 'minerva-kb' ),
				'color' => MKB_Options::option('attach_audio_color'),
				'extension' => array('mp3', 'mp4', 'aif', 'aifc', 'aac', 'mid', 'midi', 'oga', 'wav', 'weba', '3gp', '3g2'),
				'mimeBase' => 'audio',
				'mime' => array('mpeg', 'mp4', 'aac', 'midi', 'x-midi', 'ogg', '3gpp', '3gpp2', 'wav', 'webm')
			)
		);
	}

	public static function get_attachments_icon_default() {
		return array(
			'icon' => MKB_Options::option('attach_default_icon'),
			'description' => __('Default', 'minerva-kb' ),
			'color' => MKB_Options::option('attach_default_color'),
			'extension' => array(),
			'mime' => array()
		);
	}

	/**
	 * Article templates
	 */
	public function article_tmpl() {

		// attachments item
		?>
		<script type="text/html" id="tmpl-mkb-attachment-item">
			<div class="mkb-article-attachments__item <# if (data.isExternal) { #>mkb-article-attachments__item--external <# } #>js-mkb-attachment-item">
				<input type="hidden" class="js-mkb-attachment-value" name="mkb_article_attachments[]" value="{{ data.id }}">

				<div class="mkb-article-attachments__item-info">
					<i class="mkb-attachment-icon fa {{ data.icon }}" style="color:{{ data.color }}"></i>
					<span class="mkb-attachment-label">
						<span class="mkb-attachment-title">{{ data.title }}</span>
						<span class="mkb-attachment-size">({{ data.filesizeHumanReadable }})</span><# if (data.extension) { #><!--
						--><span class="mkb-attachment-type">, {{ data.description }}, .{{ data.extension }}</span><# } #>
						<span class="mkb-attachment-downloads js-mkb-tooltip"
						      data-tooltip="<?php esc_attr_e(__('Number of downloads', 'minerva-kb')); ?>">
							  <i class="fa fa-cloud-download"></i> {{ data.downloads }}
						</span>
					</span>
				</div>

				<div class="mkb-article-attachments__item-actions">
					<a class="mkb-article-attachments__item-edit js-mkb-attachment-edit js-mkb-tooltip mkb-unstyled-link"
					   href="#" data-tooltip="<?php esc_attr_e(__('Edit', 'minerva-kb')); ?>">
						<i class="fa fa-folder-open"></i>
					</a>
					<a class="mkb-article-attachments__item-remove js-mkb-attachment-remove js-mkb-tooltip mkb-unstyled-link"
					   href="#" data-tooltip="<?php esc_attr_e(__('Delete', 'minerva-kb')); ?>">
						<i class="fa fa-close"></i>
					</a>
				</div>
			</div>
		</script>
		<?php

		// no attachments
		?>
		<script type="text/html" id="tmpl-mkb-no-attachments">
			<div class="js-mkb-no-attachments mkb-no-attachments">
				<p><?php echo esc_html( __( 'No attachments added for this article', 'minerva-kb' ) ); ?></p>
			</div>
		</script>
		<?php

        // external link popup
        ?>
        <script type="text/html" id="tmpl-mkb-external-attachment">
            <div>
                <form action="" novalidate>
                    <label for="mkb_ext_file_caption"><?php esc_html_e( __( 'Custom label (optional)', 'minerva-kb' ) ); ?></label>
                    <br>
                    <input id="mkb_ext_file_caption" class="mkb-external-file-caption js-mkb-external-file-caption" type="text" value="{{ data.title }}"/>
                    <br>
                    <label for="mkb_ext_file_url"><?php esc_html_e( __( 'Paste URL of external file', 'minerva-kb' ) ); ?></label>
                    <br>
                    <input id="mkb_ext_file_url" class="mkb-external-file-url js-mkb-external-file-url" type="text" value="{{ data.url }}"/>
                    <br>
                    <label for="mkb_ext_file_size"><?php esc_html_e( __( 'Set size of external file (optional)', 'minerva-kb' ) ); ?></label>
                    <br>
                    <input id="mkb_ext_file_size" class="mkb-external-file-size js-mkb-external-file-size" type="text" value="{{ data.size }}"/>
                    <br>
                </form>
            </div>
        </script>
        <?php

        // external link popup add
        ?>
        <script type="text/html" id="tmpl-mkb-external-attachment-add">
            <a href="#" class="fn-mkb-external-link-insert mkb-action-button mkb-action-default">
                <?php esc_html_e( __( 'Add URL', 'minerva-kb' ) ); ?>
            </a>
        </script>
        <?php

        // external link popup update
        ?>
        <script type="text/html" id="tmpl-mkb-external-attachment-update">
            <a href="#" class="fn-mkb-external-link-update mkb-action-button mkb-action-default">
                <?php esc_html_e( __( 'Update URL', 'minerva-kb' ) ); ?>
            </a>
        </script>
        <?php
	}

	/**
	 * Saves article meta box fields
	 * @param $post_id
	 * @return mixed|void
	 */
	function save_post( $post_id ) {
		/**
		 * Verify user is indeed user
		 */
		if (
			! isset( $_POST['mkb_save_article_nonce'] )
			|| ! wp_verify_nonce( $_POST['mkb_save_article_nonce'], 'mkb_save_article' )
		) {
			return;
		}

		$post_type = get_post_type($post_id);

		if ($post_type !== MKB_Options::option( 'article_cpt' )) {
			return;
		}

		// TODO: normalize all these maybe
		update_post_meta(
			$post_id,
			'_mkb_related_articles',
			isset($_POST['mkb_related_articles']) ?
				$_POST['mkb_related_articles'] :
				array()
		);

		update_post_meta(
			$post_id,
			'_mkb_article_attachments',
			isset($_POST['mkb_article_attachments']) ?
				$_POST['mkb_article_attachments'] :
				array()
		);

		// restrict access
		if (MKB_Options::option('restrict_on')) {
			update_post_meta(
				$post_id,
				'_mkb_restrict_role',
				isset($_POST['mkb_article_access_role']) ?
					$_POST['mkb_article_access_role'] :
					'none'
			);
		}

		$this->restrict->invalidate_restriction_cache();
	}
}
