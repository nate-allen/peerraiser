<p>
    <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'peerraiser' ); ?></label>
    <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $peerraiser['title'] ); ?>">
</p>
<p>
    <label for="<?php echo esc_attr( $this->get_field_id( 'list_size' ) ); ?>"><?php esc_attr_e( 'List Size:', 'peerraiser' ); ?></label>
    <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'list_size' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'list_size' ) ); ?>" type="number" value="<?php echo esc_attr( $peerraiser['list_size'] ); ?>">
</p>