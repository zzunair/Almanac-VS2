<?php
/**
 * A Customizer control for an add / remove / reorder list of content
 * blocks -- e.g. slideshow slides, collection tiles, feature cards. This
 * is the "Shopify section blocks" pattern applied to core WP Customizer.
 *
 * The control's value is a JSON string: an array of block objects, each
 * shaped by $this->block_fields. Field values are read straight off the
 * DOM by data-field attribute (see assets/js/customizer-controls.js), so
 * no name="[index][field]" bookkeeping is needed on either side.
 */

if ( ! class_exists( 'WP_Customize_Control' ) ) {
	return;
}

class VSS_Repeater_Control extends WP_Customize_Control {

	public $type = 'vss-repeater';

	/** @var array field_key => array( type, label, default, options? ) */
	public $block_fields = array();

	/** @var string which block_fields key to show as the block's title */
	public $title_field = '';

	/** @var string button label, e.g. "Add slide" */
	public $add_button_text = 'Add block';

	/** @var int minimum number of blocks allowed (delete is disabled below this) */
	public $min_blocks = 0;

	public function enqueue() {
		wp_enqueue_media();
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

	private function resolve_options( $field ) {
		if ( empty( $field['options'] ) ) {
			return array();
		}
		if ( 'product_cat' === $field['options'] && function_exists( 'vss_product_cat_options' ) ) {
			return vss_product_cat_options();
		}
		return is_array( $field['options'] ) ? $field['options'] : array();
	}

	private function render_field_input( $key, $field, $value ) {
		if ( null === $value ) {
			$value = isset( $field['default'] ) ? $field['default'] : '';
		}
		switch ( $field['type'] ) {
			case 'textarea':
				?>
				<textarea data-field="<?php echo esc_attr( $key ); ?>" placeholder="<?php echo esc_attr( $field['label'] ); ?>" rows="3"><?php echo esc_textarea( $value ); ?></textarea>
				<?php
				break;
			case 'select':
				?>
				<select data-field="<?php echo esc_attr( $key ); ?>">
					<?php foreach ( $this->resolve_options( $field ) as $opt_value => $opt_label ) : ?>
						<option value="<?php echo esc_attr( $opt_value ); ?>" <?php selected( $value, $opt_value ); ?>><?php echo esc_html( $opt_label ); ?></option>
					<?php endforeach; ?>
				</select>
				<?php
				break;
			case 'number':
				?>
				<input type="number" data-field="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo esc_attr( $field['label'] ); ?>" />
				<?php
				break;
			case 'decimal':
				?>
				<input type="number" step="0.01" data-field="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo esc_attr( $field['label'] ); ?>" />
				<?php
				break;
			case 'image':
				?>
				<div class="tw-repeater-image">
					<img class="tw-repeater-image-preview<?php echo $value ? '' : ' tw-hidden'; ?>" src="<?php echo esc_url( $value ); ?>" alt="" />
					<input type="hidden" data-field="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>" />
					<div class="tw-repeater-image-buttons">
						<button type="button" class="button tw-repeater-image-select"><?php echo $value ? 'Change image' : 'Select image'; ?></button>
						<button type="button" class="button-link tw-repeater-image-remove<?php echo $value ? '' : ' tw-hidden'; ?>">Remove</button>
					</div>
				</div>
				<?php
				break;
			case 'checkbox':
				?>
				<input type="checkbox" data-field="<?php echo esc_attr( $key ); ?>" <?php checked( ! empty( $value ) ); ?> />
				<?php
				break;
			default:
				?>
				<input type="text" data-field="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo esc_attr( $field['label'] ); ?>" />
				<?php
		}
	}

	private function block_title( $block ) {
		if ( ! $this->title_field || ! isset( $block[ $this->title_field ] ) ) {
			return 'Block';
		}
		$field = isset( $this->block_fields[ $this->title_field ] ) ? $this->block_fields[ $this->title_field ] : array();
		$value = $block[ $this->title_field ];
		if ( isset( $field['type'] ) && 'select' === $field['type'] ) {
			$options = $this->resolve_options( $field );
			return isset( $options[ $value ] ) ? $options[ $value ] : ( $value ? $value : 'Block' );
		}
		return $value ? $value : 'Block';
	}

	private function render_block( $block ) {
		?>
		<li class="tw-repeater-block">
			<div class="tw-repeater-block-header">
				<span class="tw-repeater-drag-handle" aria-hidden="true">&#10022;</span>
				<button type="button" class="tw-repeater-block-toggle">
					<span class="tw-repeater-block-title"><?php echo esc_html( $this->block_title( $block ) ); ?></span>
				</button>
				<button type="button" class="tw-repeater-remove" aria-label="Remove">&#10005;</button>
			</div>
			<div class="tw-repeater-block-body">
				<?php foreach ( $this->block_fields as $key => $field ) : ?>
					<label class="tw-repeater-field">
						<span><?php echo esc_html( $field['label'] ); ?></span>
						<?php $this->render_field_input( $key, $field, isset( $block[ $key ] ) ? $block[ $key ] : null ); ?>
					</label>
				<?php endforeach; ?>
			</div>
		</li>
		<?php
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

		<ul class="tw-repeater" data-control-id="<?php echo esc_attr( $this->id ); ?>" data-min="<?php echo (int) $this->min_blocks; ?>" data-title-field="<?php echo esc_attr( $this->title_field ); ?>">
			<?php foreach ( $value as $block ) : ?>
				<?php $this->render_block( $block ); ?>
			<?php endforeach; ?>
		</ul>

		<template class="tw-repeater-template">
			<?php $this->render_block( array() ); ?>
		</template>

		<button type="button" class="button tw-repeater-add"><?php echo esc_html( $this->add_button_text ); ?></button>
		<input type="hidden" class="tw-repeater-value" value="<?php echo esc_attr( $this->value() ); ?>" />
		<?php
	}
}
