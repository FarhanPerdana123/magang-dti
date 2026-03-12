<?php

defined( 'ABSPATH' ) || exit;

if ( empty( $campaign->content ) ) {
    return;
}

$decoded_content = $campaign->content;
$settings        = $decoded_content['settings'];

if ( ! empty( $settings['display_conditions'] ) ) {
    $is_matched_display_conditions = \OptinCraft\App\Helpers\DisplayConditions::is_matched( $settings['display_conditions'] );

    if ( ! $is_matched_display_conditions ) {
        return;
    }

    unset( $settings['display_conditions'] );
}

$steps             = '';
$form_after_submit = '';
$form_redirect_url = '';
$active_step_id    = '';
$step_ids          = [];

foreach ( $decoded_content['steps'] as $key => $step ) {
    if ( empty( $key ) ) {
        $active_step_id = $step["id"];
    }
    $step_ids[] = $step['id'];
    $elements   = '';
    foreach ( $step['elements'] as $element ) {
        if ( 'shortcode' === $element['type'] ) {
            $elements .= str_replace( 'SHORTCODE_CONTENT', do_shortcode( $element['attributes']['shortcode'] ), $element['content'] ) . ' ';
        } else {
            $elements .= $element['content'] . ' ';
        }

        if ( 'form' === $element['type'] ) {
            $form_after_submit = isset( $element['attributes']['button_after_submit'] ) ? $element['attributes']['button_after_submit'] : '';
            $form_redirect_url = isset( $element['attributes']['redirect_url'] ) ? $element['attributes']['redirect_url'] : '';
        }
    }

    $steps .= "<div id='optincraft-campaign-step-" . $campaign->id . "-" . $step['id'] . "' class='optincraft-campaign-step-hide' data-wp-interactive='optincraft/campaign' data-wp-context='" . wp_json_encode( ["stepId" => $step["id"]] ) . "' data-wp-class--optincraft-campaign-step-show='state.isStepShowing'>" . $elements . '</div>';
}

$step_data = [];
foreach ( $decoded_content['steps'] as $step ) {
    $step_data[$step['id']] = [
        'width'  => isset( $step['width'] ) ? $step['width'] : $decoded_content['width'],
        'height' => isset( $step['height'] ) ? $step['height'] : $decoded_content['height'],
    ];
}

$content = str_replace( '{BUILDER_CONTENT}', $steps, $decoded_content['wrapper'] );
$context = [
    'campaign_id'       => $campaign->id,
    'type'              => $campaign->type,
    'activeStepId'      => $active_step_id,
    'stepIds'           => $step_ids,
    'stepData'          => $step_data, // Per-step dimension data for frontend transitions
    'settings'          => $settings,
    'height'            => isset( $step_data[$active_step_id] ) ? $step_data[$active_step_id]['height'] : $decoded_content['height'],
    'width'             => isset( $step_data[$active_step_id] ) ? $step_data[$active_step_id]['width'] : $decoded_content['width'],
    'form_after_submit' => $form_after_submit,
    'form_redirect_url' => $form_redirect_url,
    'showForcefully'    => apply_filters( 'optincraft_show_campaign_forcefully', false, $campaign ),
];

$canvas_styles = '';

if ( isset( $settings['effect'] ) &&  in_array( $settings['effect'], ['stars', 'christmas_light'], true ) && 'floating_bar' !== $campaign->type ) {
    $canvas_styles = 'z-index: -1;';
}

?>

<div
    class="optincraft-campaign optincraft-campaign-<?php echo esc_attr( $campaign->id ); ?>"
    data-wp-interactive="optincraft/campaign"
    data-wp-context='<?php echo wp_json_encode( $context ); ?>'
    data-wp-init="callbacks.init"
>

    <!-- Modal Overlay -->
    <?php if ( $campaign->type === 'popup' ) : ?>
        <div class="optincraft-campaign-overlay" data-wp-class--open="context.isShowing" data-wp-on--click="actions.onClickOutside"></div>
    <?php endif; ?>

    <div class="optincraft-campaign-content" data-wp-class--open="context.isShowing">
        <form data-wp-init="callbacks.initForm">
            <?php if ( $campaign->type === 'floating_bar' ) {
                //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                echo $content;
            } else {
                include __DIR__ . '/popup.php';
            } ?>
            <canvas class="optincraft-effect-canvas" style="<?php echo esc_attr( $canvas_styles ); ?>" id="optincraft-effect-canvas-<?php echo esc_attr( $campaign->id ); ?>"></canvas>
        </form>
    </div>
    
</div>