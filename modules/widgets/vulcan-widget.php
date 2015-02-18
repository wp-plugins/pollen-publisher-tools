<?php

/**
 * Adds Foo_Widget widget.
 */
class Pollen_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'pollen_widget', // Base ID
			__( 'Pollen Widget', 'pollen_plugin_domain' ), // Name
			array( 'description' => __( 'A widget for recommendation, realtime stats.', 'pollen_plugin_domain' ), ) // Args
		);
		$this->wpplugin_url = PollenPlugin::getWpPluginUrl();
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {

		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}
		$time = time();
		echo '<div><iframe class="pollen-pending-init" id="vplgn_recommendation_' . $instance['widget_id'] . '_' . $time . '" frameborder="0" scrolling="no" style="width: 100 %; height: 100px; opacity: 1; overflow: hidden;" data-widget-id="' . $instance['widget_id'] . '" data-widget-base-url="' . pollenplugin_url . '"></iframe></div>';
		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array(
			'title'     => '',
			'widget_id' => 0
		) );

		$this->display_dependency_scripts();
		$this->display_title( $instance['title'] );
		$this->display_widget_id( $instance['widget_id'] );
	}

	public function display_dependency_scripts() {
		wp_enqueue_script( 'wppollenplugin-angularjs', $this->wpplugin_url . 'assets/dependencies/angular.min.js', array( 'jquery' ), false, true );
	}

	public function display_title( $existing_value = '' ) {
		echo '<p><label>' . esc_html( __( 'Title', 'pollen_plugin_domain' ) ) . ': ';
		echo '<input type="text" id="' . $this->get_field_id( 'title' ) . '" name="' . $this->get_field_name( 'title' ) . '" class="widefat"';
		if ( $existing_value ) {
			echo ' value="' . esc_attr( $existing_value ) . '"';
		}
		echo ' /></label></p>';
	}

	public function display_widget_id( $existing_value = '' ) {
		echo '<div>';
		echo '<p><label>' . esc_html( __( 'Widget ID', 'pollen_plugin_domain' ) ) . ': ';
		echo '<input type="text" id="' . $this->get_field_id( 'widget_id' ) . '" name="' . $this->get_field_name( 'widget_id' ) . '" class="widefat"';
		if ( $existing_value ) {
			echo ' value="' . esc_attr( $existing_value ) . '"';
		}
		echo ' /></label></p>';
		echo '<div class="description">Retrieve your widget ID from <a href="' . pollenplugin_url . '/dashboard/widgets" target="_blank">Pollen Dashboard</a></div>';
		echo '</div>';

	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance              = array();
		$instance['title']     = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['widget_id'] = ( ! empty( $new_instance['widget_id'] ) ) ? strip_tags( $new_instance['widget_id'] ) : '';

		return $instance;
	}

} // class

// register Foo_Widget widget
function register_pollen_widget() {
	register_widget( 'Pollen_Widget' );
}

add_action( 'widgets_init', 'register_pollen_widget' );
