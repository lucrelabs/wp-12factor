<?php

class ET_Builder_Module_Field_Scroll extends ET_Builder_Module_Field_Base {

	public function get_defaults() {
		return array(
			'prefix'      => '',
			'label'       => esc_html__( 'Scroll Transform Effects', 'et_builder' ),
			'description' => 'Using Scrolling Effects, you can transform elements on your page as you scroll. The animation\'s transition is based on the user\'s scrolling behavior. Once the element enters the browser viewport (top), the animation begins, and it once it leaves the viewport (bottom), the animation ends.',
			'tab_slug'    => 'custom_css',
			'toggle_slug' => 'scroll_effects',
			'options'     => array(),
		);
	}

	public function get_fields( array $args = array() ) {
		$settings           = array_merge( $this->get_defaults(), $args );
		$prefix             = $settings['prefix'];
		$grid_support       = $settings['grid_support'];
		$name               = et_builder_add_prefix( $prefix, 'scroll_effects' );
		$grid_motion_toggle = array();

		if ( 'yes' === $grid_support ) {
			$grid_motion_toggle = array(
				'enable_grid_motion' => array(
					'label'           => esc_html__( 'Apply Motion Effects To Child Elements', 'et_builder' ),
					'type'            => 'yes_no_button',
					'option_category' => 'configuration',
					'options'         => array(
						'off' => esc_html__( 'No', 'et_builder' ),
						'on'  => esc_html__( 'Yes', 'et_builder' ),
					),
					'default'         => 'off',
					'description'     => esc_html__( 'This applies motion effects to individual elements within the module rather than the module as a whole. For example, to each image within a Gallery, rather than the Gallery container.', 'et_builder' ),
					'tab_slug'        => $settings['tab_slug'],
					'toggle_slug'     => $settings['toggle_slug'],
					'bb_support'      => false,
				),
			);
		}

		return array_merge(
			$grid_motion_toggle,
			array(
				$name => array(
					'label'               => $settings['label'],
					'description'         => $settings['description'],
					'tab_slug'            => $settings['tab_slug'],
					'toggle_slug'         => $settings['toggle_slug'],
					'attr_suffix'         => '',
					'type'                => 'composite',
					'option_category'     => 'layout',
					'composite_type'      => 'default',
					'composite_structure' => $this->get_options( $settings['options'], $settings['tab_slug'], $settings['toggle_slug'], $prefix ),
					'bb_support'          => false,
				),
			)
		);
	}

	private function get_options( array $options_settings, $tab, $toggle, $prefix = '' ) {
		$options = array();

		foreach ( $options_settings as $name => $settings ) {
			$option_name = et_builder_add_prefix( $prefix, $name );
			$icon        = et_()->array_get( $settings, 'icon', '' );
			$label       = et_()->array_get( $settings, 'label', '' );
			$description = et_()->array_get( $settings, 'description' );
			$default     = et_()->array_get( $settings, 'default' );
			$resolver    = et_()->array_get( $settings, 'resolver' );

			$options[ $option_name ] = array(
				'icon'     => $icon,
				'tooltip'  => $label,
				'controls' => array(
					"{$option_name}_enable" => array(
						'label'           => esc_html( sprintf( __( 'Enable %s', 'et_builder' ), $label ) ),
						'type'            => 'yes_no_button',
						'option_category' => 'configuration',
						'options'         => array(
							'off' => esc_html__( 'No', 'et_builder' ),
							'on'  => esc_html__( 'Yes', 'et_builder' ),
						),
						'default'          => 'off',
						'description'      => esc_html( $description ),
						'tab_slug'         => $tab,
						'toggle_slug'      => $toggle,
						'main_tab_setting' => 'on',
					),
					$option_name            => array(
						'label'          => esc_html( sprintf( __( 'Set %s', 'et_builder' ), $label ) ),
						'type'           => 'motion',
						'default'        => $default,
						'description'    => esc_html( $description ),
						'tab_slug'       => $tab,
						'toggle_slug'    => $toggle,
						'mobile_options' => true,
						'show_if'        => array(
							"{$option_name}_enable" => 'on',
						),
						'resolver'       => $resolver,
						'i10n'           => array(
							'startTitle'       => $this->getStartTitle(),
							'endTitle'         => $this->getEndTitle(),
							'startValueTitle'  => et_()->array_get( $settings, 'startValueTitle', $this->getStartValueTitle() ),
							'middleValueTitle' => et_()->array_get( $settings, 'middleValueTitle', $this->getMiddleValueTitle() ),
							'endValueTitle'    => et_()->array_get( $settings, 'endValueTitle', $this->getEndValueTitle() ),
						)
					),
				)
			);
		}

		return $options;
	}

	private function getStartTitle() {
		return esc_html__( 'Viewport Bottom', 'et_builder' );
	}

	private function getEndTitle() {
		return esc_html__( 'Viewport Top', 'et_builder' );
	}

	private function getStartValueTitle() {
		return esc_html__( 'Starting', 'et_builder' );
	}

	private function getMiddleValueTitle() {
		return esc_html__( 'Mid', 'et_builder' );
	}

	private function getEndValueTitle() {
		return esc_html__( 'Ending', 'et_builder' );
	}
}

return new ET_Builder_Module_Field_Scroll();
