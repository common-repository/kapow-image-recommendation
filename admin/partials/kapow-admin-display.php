<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://brightminded.com
 * @since      1.0.0
 *
 * @package    Kapow
 * @subpackage Kapow/admin/partials
 */
?>

<h1>KAPOW Settings</h1>
<form action="" method="POST">
	<table class="form-table" role="presentation">
		<tbody>
			<?php foreach( $props as $prop ) { ?>
				<tr>
					<th scope="row">
						<label><?php echo esc_html( $prop[ 'label' ] ); ?>:</label>
					</th>
					<td>
						<?php if( $prop[ 'name' ] == 'api_key' ) { ?>
							<input type="text" name="<?php echo esc_attr( $prop[ 'name' ] ); ?>" value="<?php echo esc_attr( $prop[ 'value' ] ); ?>" class="regular-text">
						<?php } else { ?>
							<input 
								type="range" 
								max="<?php echo esc_attr( $prop[ 'max' ] ); ?>" 
								min="<?php echo esc_attr( $prop[ 'min' ] ); ?>" 
								step="<?php echo esc_attr( $prop[ 'step' ] ); ?>" 
								name="<?php echo esc_attr( $prop[ 'name' ] ); ?>" 
								value="<?php echo esc_attr( $prop[ 'value' ] ); ?>" 
								class="regular-text"
								oninput="this.nextElementSibling.value = this.value" >
							<output><?php echo esc_html( $prop[ 'value' ] ); ?></output>
						<?php } ?>
					</td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
	<input type="submit" value="Save Settings" name="kapow-save">
</form>
