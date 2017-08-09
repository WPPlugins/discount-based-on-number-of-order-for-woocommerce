<?php
/**
*Plugin Name: Discount based on Number of Order For WooCommerce
*Description: Using this plugin you can provide discount based on total no of previous orders done by customer in a woocommerce store
*Version: 1.0.0
*Author: extensionhawk
*Author URI: https://www.xadapter.com/
*/


//		Add Admin Menu
add_action('admin_menu', 'ehawk_add_custom_settings');
//		Assign a Function Which Parses the Admin Form
function ehawk_add_custom_settings()
{
	add_menu_page(
		'Discount by Total Number of Order',	#$page_title
		'Order Based Discount',						#$menu_title
		'manage_options',						#$capability
		'ehawk_ssp_order_discount',						#$menu_slug - Used in url
		'ehawk_ssp_menu_callback'							#The function to be called to output the content for this page.
		);
}


function ehawk_ssp_menu_callback()
{
?>
	<form method="post" action="options.php">
	<?php
		settings_fields('ehawk_ssp_order_discount');
		do_settings_sections('ehawk_ssp_order_discount');
		submit_button('Submit');
	?>
	</form>
			
<?php
}

add_action('admin_init','ehawk_ssp_register_my_setting');
//		Register a settings
function ehawk_ssp_register_my_setting()
{
	//add_settings_section( $id, $title, $callback, $page )
	add_settings_section(
		'ehawk_setting_section',
		'Discount based on total number of orders placed by specific customer',
		'ehawk_setting_section_callback',
		'ehawk_ssp_order_discount'
		);


	//add_settings_field( $id, $title, $callback, $page, $section, $args )
	add_settings_field(
		'xa_setting_field',
		'',
		'ehawk_setting_field_callback',
		'ehawk_ssp_order_discount',
		'ehawk_setting_section'
		);
	register_setting( 'ehawk_ssp_order_discount', 'ehawk_ssp_order_discount' );
}

//-----------------------------------------------------------------------------------
//					Setting Section Callback Function
//-----------------------------------------------------------------------------------
// This function is needed if we added a new section. This function will be run at the start of our section
function ehawk_setting_section_callback()
{
}

//-----------------------------------------------------------------------------------
//					Setting Field Callback Function
//-----------------------------------------------------------------------------------
function ehawk_setting_field_callback()
{
	$data=get_option('ehawk_ssp_order_discount');
	?>
	<html>
	<head>
	<script type="text/javascript">
 	function ehawk_add_new_rule(rap)
 	{
  	var count = document.querySelectorAll('.form-row').length;
 		var newdiv = document.createElement('div');
 		newdiv.innerHTML="<div class='form-row'>Total no. of Orders : <input type='text' name='ehawk_ssp_order_discount["+count+"][noo]' value='<?php $y=get_option('ehawk_ssp_order_discount'); echo $y[count][noo]; ?>' required/> Discount: <input type='text' name='ehawk_ssp_order_discount["+count+"][disc]' value='<?php $y=get_option('ehawk_ssp_order_discount'); echo $y[count][disc];?>' required/>";

 		document.getElementById(rap).appendChild(newdiv);
 	}
	</script>
	</head>

	<body>
	<h2><i><b>Discount Rules</b></i></h2>		
	
	<form method="post" action="options.php">		<!--optios.php handles the form data its already present -->
		<div id = "wrap" calss="cl">
		<?php
			$xa_data=get_option(ehawk_ssp_order_discount);
		if(!empty($xa_data))
		{	foreach($xa_data as $index=>$val)
			{
		?>
				<div class='form-row'>
					Total no. of Orders: <input type='text' name='ehawk_ssp_order_discount[<?php echo $index; ?>][noo]' value='<?php echo $val['noo']; ?>' />
					Discount: <input type='text' name='ehawk_ssp_order_discount[<?php echo $index; ?>][disc]' value='<?php echo $val['disc'];?>' /> 
				</div>

		<?php
			}

		}
		?>

		</div>
		<input type="button" value="Add New" onclick="ehawk_add_new_rule('wrap');"/>
	</form>
	
	</body>
	</html>

<?php

}

add_filter('woocommerce_product_get_price','ehawk_discount_callback',1,1);
add_filter('woocommerce_product_variation_get_price','ehawk_discount_callback',1,1);
    
function ehawk_discount_callback($price)
{
	$customer_orders = get_posts( array(
         'numberposts' => -1,
         'meta_key'    => '_customer_user',
         'meta_value'  => get_current_user_id(),
         'post_type'   => wc_get_order_types(),
         'post_status' => array_keys( wc_get_order_statuses()),)
      );
	$total_order = count($customer_orders);
	$max_disc=0;
    $z=get_option('ehawk_ssp_order_discount');
    if(!empty($z))
    {
    	foreach ($z as $x)
    	{
            if($total_order >= $x['noo'])
            { 
                if($max_disc < $x['disc'])
                {
                    $max_disc=$x['disc'];
                }
            } 		 		 	
     	}
     }
$price = $price - ($max_disc * .01 * $price);     
return $price;

}