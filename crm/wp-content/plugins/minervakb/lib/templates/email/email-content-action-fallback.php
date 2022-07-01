<table class="body-sub" role="presentation">
    <tr>
        <td>
            <p class="f-fallback sub"><?php echo wp_kses_post($email_context['fallback_caption']); ?></p>
            <p class="f-fallback sub"><?php echo esc_url($email_context['action_url']); ?></p>
        </td>
    </tr>
</table>