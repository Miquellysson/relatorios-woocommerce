<?php 
/*
Plugin Name: WooCommerce Relatórios

Description:  Relatórios de Vendas 
Version: 0.0.1
Author: Miquellysson Lins 
Author URI: https://github.com/Miquellysson
*/

{
	
	load_plugin_textdomain('woo-export-order', false, dirname( plugin_basename( __FILE__ ) ) . '/');

	
	if (!class_exists('woo_export')) {

		class woo_export {
				
			public function __construct() {

				$this->order_status = array(
					'completed'		=> __( 'Finalizado', 'woo-export-order' ),
					'cancelled'		=> __( 'Cancelado', 'woo-export-order' ),
					'failed'		=> __( 'Falhado', 'woo-export-order' ),
					'refunded'		=> __( 'Reembolsado', 'woo-export-order' ),
					'processing'	=> __( 'Processando', 'woo-export-order' ),
					'pending'		=> __( 'Pendente', 'woo-export-order' ),
					'on-hold'		=> __( 'Em espera', 'woo-export-order' ),
				);
				//  admin menu
				add_action('admin_menu', array(&$this, 'woo_export_orders_menu'));
				
				add_action( 'admin_enqueue_scripts', array(&$this, 'export_enqueue_scripts_css' ));
				add_action( 'admin_enqueue_scripts', array(&$this, 'export_enqueue_scripts_js' ));
			}

			
			/**
			 * Functions
			 */
			
			function export_enqueue_scripts_css() {
					
				if ( isset($_GET['page']) && $_GET['page'] == 'export_orders_page' ) {
					wp_enqueue_style( 'bootstrap4', plugins_url('/css/bootstrap4.css', __FILE__ ) , '', '', false);
						
					wp_enqueue_style( 'bootstrap4min', plugins_url('/css/dataTables.bootstrap4.min.css', __FILE__ ) , '', '', false);

					wp_enqueue_style( 'bootstrap4Buttons', plugins_url('/css/buttons.bootstrap4.min.css', __FILE__ ) , '', '', false);

					//wp_enqueue_style( 'dataTable', plugins_url('/css/data.table.css', __FILE__ ) , '', '', false);

					//wp_enqueue_style('dataTabletheme'), plugins_url ('/css/dataTabletheme.css',__FILE__), '','',false);
				}
			}
			
			function export_enqueue_scripts_js(){
				
				if (isset($_GET['page']) && $_GET['page'] == 'export_orders_page') {
					wp_register_script( 'dataTable', plugins_url().'/woocommerce-relatorio/js/jquery.dataTables.js');
					wp_enqueue_script( 'dataTable' );
			
					wp_register_script( 'bootstrap4min', plugins_url().'/woocommerce-relatorio/js/dataTables.bootstrap4.min.js');
					wp_enqueue_script( 'bootstrap4min' );

					wp_register_script( 'dataTableButtons', plugins_url().'/woocommerce-relatorio/js/dataTables.buttons.min.js');
					wp_enqueue_script( 'dataTableButtons' );

					wp_register_script( 'bootstrap4Buttons', plugins_url().'/woocommerce-relatorio/js/buttons.bootstrap4.min.js');
					wp_enqueue_script( 'bootstrap4Buttons' );

					wp_register_script( 'woo_pdfmake', plugins_url().'/woocommerce-relatorio/js/pdfmake.min.js');
					wp_enqueue_script( 'woo_pdfmake' );

					wp_register_script( 'jszip', plugins_url().'/woocommerce-relatorio/js/jszip.min.js');
					wp_enqueue_script( 'jszip' );

					wp_register_script( 'vfsfonts', plugins_url().'/woocommerce-relatorio/js/vfs_fonts.js');
					wp_enqueue_script( 'vfsfonts' );

					wp_register_script( 'buttonsHTML5', plugins_url().'/woocommerce-relatorio/js/buttons.html5.min.js');
					wp_enqueue_script( 'buttonsHTML5' );
				}
			}
			
			function woo_export_orders_menu(){
				
				add_menu_page( 'Relatório ','Relatório','manage_woocommerce', 'export_orders_page');
				add_submenu_page('export_orders_page.php', __( 'Relatórios Settings', 'woo-export-order' ), __( 'Relatórios Settings', 'woo-export-order' ), 'manage_woocommerce', 'export_orders_page', array(&$this, 'export_orders_page' ));
				
			}
			
			function export_orders_page(){
				
				global $wpdb;
				
				?>
				
					<br>
					<h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
					<a href="admin.php?page=export_orders_page" class="nav-tab nav-tab-active"> <?php _e( 'Relatórios', 'woo-export-order' );?> </a>
					</h2>
				
					
				<?php 
				$query_order = "SELECT ID as 'order_id' FROM `" . $wpdb->prefix . "posts` where post_type='shop_order' ";
				$order_results = $wpdb->get_results( $query_order );
				
				$var = $today_checkin_var = $today_checkout_var = $booking_time = "";
				foreach ( $order_results as $id_key => $id_value ) {


					
					$order = wc_get_order( $id_value->order_id );


						$order_items = $order->get_items();
						
						$my_order_meta = get_post_custom( $id_value->order_id );

						$c = 0;
						foreach ($order_items as $items_key => $items_value ) {
							
							
							if ($this->order_status[$order->get_status()] == 'Pago') {
								echo "<tr >";
							
							};


							$var .= "<tr>
							<td>".$id_value->order_id."</td>
							<td>".$this->order_status[$order->get_status()]."</td>
							<td>".$my_order_meta['_billing_first_name'][0]." ".$my_order_meta['_billing_last_name'][0]."</td>
							<td>".$items_value['name']."</td>
							<td class='line_total'>".$items_value['line_total']."</td>
							<td>".$order->get_date_created('view')."</td>
							<td><a href=\"post.php?post=". $id_value->order_id."&action=edit\">Verficar Inscrição</a></td>
							</tr>";
								
							$c++;
						}

					//}

				}

				
				$swf_path = plugins_url()."/woocommerce-relatorio/TableTools/media/swf/copy_csv_xls.swf";
				?>

				<script>
					
					jQuery(document).ready(function() {
					 	var oTable = jQuery('#order_history').DataTable( {

					 				
								//"bJQueryUI": true,
								//"sScrollX": "",
								"bSortClasses": false,
								"aaSorting": [[0,'desc']],
								"bAutoWidth": true,
								"bInfo": true,
								//"sScrollY": "100%",	
								//"sScrollX": "100%",
								"bScrollCollapse": true,
								"sPaginationType": "full_numbers",
								"bRetrieve": true,
															"oLanguage": {
							    "sEmptyTable": "Nenhum registro encontrado",
							    "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
							    "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
							    "sInfoFiltered": "(Filtrados de _MAX_ registros)",
							    "sInfoPostFix": "",
							    "sInfoThousands": ".",
							    "sLengthMenu": "_MENU_ resultados por página",
							    "sLoadingRecords": "Carregando...",
							    "sProcessing": "Processando...",
							    "sZeroRecords": "Nenhum registro encontrado",
							    "sSearch": "Pesquisar",
							    "oPaginate": {
							        "sNext": "Próximo",
							        "sPrevious": "Anterior",
							        "sFirst": "Primeiro",
							        "sLast": "Último"
							    },
							     lengthChange: false,
        						buttons: [ 'copy', 'excel', 'pdf', 'colvis' ],
        						
							},
								
								"dom": 'Blfrtip',
								"buttons": [
									'copyHtml5',
									'excelHtml5',
									'csvHtml5',
									'pdfHtml5'
								]
					} );
					 	     
   								 
 		
					 } );
 	
									

					</script>



			
			<div>
				<table id="order_history" class="table table-striped table-bordered" cellspacing="0" width="100%">
					<thead>
						<tr>
							<th><?php _e( 'ID' , 'woo-export-order' ); ?></th>
							<th><?php _e( 'Status', 'woo-export-order' ); ?></th>
							<th><?php _e( 'Nome' , 'woo-export-order' ); ?></th>
							<th><?php _e( 'Curso' , 'woo-export-order' ); ?></th>
							<th><?php _e( 'Valor' , 'woo-export-order' ); ?></th>
							<th><?php _e( 'Data de Inscrição' , 'woo-export-order' ); ?></th>
							<th><?php _e( 'Ações' , 'woo-export-order' ); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php echo $var;?>
					</tbody>
				</table>
			</div>
			
			<?php 
								
			}
		}
	}
	
	$woo_export = new woo_export();
}
?>