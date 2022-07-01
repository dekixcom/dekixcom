<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */
class MKB_Utils {
    const ENCRYPT_SALT = 'LjVC{JLqNU`+#4.<yR@Q<IZ+8xaM+jyD,vBk%j/S<o8d|Esc|-4ANELWH1Y?=!j8';
    const ENCRYPT_KEY = 'j2WG =W!7^w]^JB!(%a591Y_/>QlJ/-r-cuwe&b.+mJl-9*Pm:Sxm|uB|lWi}piY';

    /**
     * @param $value
     * @return bool|string
     */
    public static function encrypt($value) {
        if (!extension_loaded('openssl')) {
            return $value;
        }

        $method = 'aes-256-ctr';
        $ivlen = openssl_cipher_iv_length($method);
        $iv = openssl_random_pseudo_bytes($ivlen);

        $raw_value = openssl_encrypt($value . self::ENCRYPT_SALT, $method, self::ENCRYPT_KEY, 0, $iv);

        if (!$raw_value) {
            return '';
        }

        return base64_encode($iv . $raw_value);
    }

    /**
     * @param $raw_value
     * @return bool|false|string
     */
    public static function decrypt($raw_value) {
        if (!extension_loaded('openssl')) {
            return $raw_value;
        }

        $raw_value = base64_decode($raw_value, true);

        $method = 'aes-256-ctr';
        $ivlen = openssl_cipher_iv_length($method);
        $iv = substr($raw_value, 0, $ivlen);

        $raw_value = substr($raw_value, $ivlen);

        $value = openssl_decrypt($raw_value, $method, self::ENCRYPT_KEY, 0, $iv);

        if (!$value || substr($value, -strlen(self::ENCRYPT_SALT)) !== self::ENCRYPT_SALT) {
            return false;
        }

        return substr($value, 0, -strlen(self::ENCRYPT_SALT));
    }

    /**
     * @param $timestamp
     * @param $local
     * @param bool $no_content
     * @throws Exception
     * TODO: move to template helper
     */
    public static function render_human_date($timestamp, $local, $no_content = false) {
        if (!$timestamp) {
            _e('not yet', 'minerva-kb');

            return;
        }

        ?><span class="js-mkb-human-readable-time mkb-human-readable-time" data-timestamp="<?php esc_attr_e($timestamp); ?>" title="<?php esc_attr_e(date('F j, Y H:i:s', $local)); ?>"><?php esc_html_e($no_content === true ? '' : self::time_elapsed_string($timestamp)); ?></span><?php
    }

    /**
     * @param $timestamp
     * @param $local
     * @param bool $no_content
     * @return false|string
     * @throws Exception
     */
    public static function get_human_date_html($timestamp, $local, $no_content = false) {
        ob_start();
        self::render_human_date($timestamp, $local, $no_content);
        return ob_get_clean();
    }

    /**
     * @param $datetime
     * @param bool $full
     * @return string
     * @throws Exception
     */
    public static function time_elapsed_string($datetime, $full = false) {
        if (!$datetime) {
            return 'not yet';
        }

        $now = new DateTime;
        $then = new DateTime('@' . $datetime);
        $diff = (array) $now->diff( $then );

        $diff['w']  = floor( $diff['d'] / 7 );
        $diff['d'] -= $diff['w'] * 7;

        $string = array(
            'y' => __('year', 'minerva-kb'),
            'm' => __('month', 'minerva-kb'),
            'w' => __('week', 'minerva-kb'),
            'd' => __('day', 'minerva-kb'),
            'h' => __('hour', 'minerva-kb'),
            'i' => __('minute', 'minerva-kb'),
            's' => __('second', 'minerva-kb'),
        );

        foreach ($string as $k => & $v) {
            if ($diff[$k]) {
                $v = $diff[$k] . ' ' . $v . ($diff[$k] > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) {
            $cutoff = 1;
            $string = array_slice($string, 0, $cutoff);
        }

        return $string ?
            implode( ' ', $string ) . ' ' . __('ago', 'minerva-kb') :
            __('just now', 'minerva-kb');
    }

    /**
     * @param $post_id
     * @return string
     */
    public static function get_post_edit_admin_url($post_id) {
        return admin_url('post.php?post=' . $post_id . '&action=edit');
    }
}
