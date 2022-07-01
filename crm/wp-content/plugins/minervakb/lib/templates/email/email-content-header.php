<tr>
    <td class="email-masthead">
        <?php

        // 1. Content Header: Logo
        if (MKB_Options::option('email_header_template') === 'logo'):

            ?>
                <a href="<?php echo esc_url($email_context['company_url']); ?>" target="_blank" class="f-fallback email-masthead_logo">
                    <img src="<?php echo esc_url($email_context['company_logo']); ?>" class="email-masthead_logo" border="0">
                </a>
            <?php

        // 2. Content Header: Company Name
        elseif (MKB_Options::option('email_header_template') === 'name'):

            ?>
                <a href="<?php echo esc_url($email_context['company_url']); ?>" target="_blank" class="f-fallback email-masthead_name">
                    <?php echo $email_context['company_name']; ?>
                </a>
            <?php

        endif;

        ?>
    </td>
</tr>