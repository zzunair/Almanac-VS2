<?php
/**
 * A Customizer control that renders a drag-to-reorder, checkbox-to-enable
 * list of page sections -- the "Shopify-style" section manager.
 *
 * The control's value is a JSON string: [{"id":"hero","enabled":true}, ...]
 */

if ( ! class_exists( 'WP_Customize_Control' ) ) {
	return;
}

class VSS_Section_Order_Control extends WP_Customize_Control {

	public $type = 'vss-section-order';

	/** @var array section_id => label */
	public $sections_config = array();

	public function enqueue() {
		wp_enqueue_script(
			'vss-customizer-controls',
			get_template_directory_uri() . '/assets/js/customizer-controls.js',
			array( 'jquery', 'jquery-ui-sortable', 'customize-controls' ),
			VSS_VERSION,
			true
		);
		wp_enqueue_style(
			'vss-customizer-controls',
			get_template_directory_uri() . '/assets/css/customizer-admin.css',
			array(),
			VSS_VERSION
		);
	}

	public function render_content() {
		$value = json_decode( $this->value(), true );
		if ( ! is_array( $value ) ) {
			$value = array();
		}
		?>
		<label class="customize-control-title"><?php echo esc_html( $this->label ); ?></label>
		<?php if ( $this->description ) : ?>
			<span class="description customize-control-description"><?php echo wp_kses_post( $this->description ); ?></span>
		<?php endif; ?>

		<ul class="vss-section-order" data-control-id="<?php echo esc_attr( $this->id ); ?>">
			<?php foreach ( $value as $row ) :
				$section_id = isset( $row['id'] ) ? $row['id'] : '';
				$label      = isset( $this->sections_config[ $section_id ] ) ? $this->sections_config[ $section_id ] : $section_id;
				$enabled    = ! empty( $row['enabled'] );
				?>
				<li class="vss-section-order-item" data-id="<?php echo esc_attr( $section_id ); ?>">
					<span class="vss-drag-handle" aria-hidden="true">&#10022;</span>
					<label>
						<input type="checkbox" class="vss-section-enabled" <?php checked( $enabled ); ?> />
						<?php echo esc_html( $label ); ?>
					</label>
				</li>
			<?php endforeach; ?>
		</ul>

		<input type="hidden" class="vss-section-order-value" value="<?php echo esc_attr( $this->value() ); ?>" />
		<p class="description">Drag <strong>&#10022;</strong> to reorder. Uncheck to hide a section on the front end.</p>
		<?php
	}
}
