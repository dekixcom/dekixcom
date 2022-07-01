<tr>
    <td>
        <table class="email-footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
            <tr>
                <td class="content-cell" align="center">
                    <p class="f-fallback sub align-center">
                        <?php echo wp_kses_post($email_context['footer_copyright']); ?>
                    </p>
                    <p class="f-fallback sub align-center">
                        <?php echo wp_kses_post($email_context['footer_text']); ?>
                    </p>
                </td>
            </tr>
        </table>
    </td>
</tr>