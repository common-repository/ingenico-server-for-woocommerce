<?php
/**
 * Plugin demonstrates a way to integrate Ingenico terminals with your WordPress/WooCommerce website. 
 * Ingenico fiscal terminals are widely used by eService, Polcard First Data and others in Poland.
 *
 * @package Ingenico Server Integration Plugin
 * @author BigDotSoftware
 * @license GPL-2.0+
 * @link https://bigdotsoftware.pl/ingenicoserver-restful-service-dla-terminali-platniczych/
 * @copyright 2021 BigDotSoftware All rights reserved.
 *
 * @wordpress-plugin
 * Plugin Name:             Ingenico Server for WooCommerce
 * Plugin URI:              https://wordpress.org/plugins/ingenico-server-for-woocommerce
 * Description:             Ingenico terminals integration plugin | Plugin umożliwiający integrację z terminalami płatniczymi Ingenico
 * Version:                 1.0.0
 * Author:                  BigDotSoftware
 * Author URI:              https://bigdotsoftware.pl/ingenicoserver-restful-service-dla-terminali-platniczych/
 * License:                 GPL-2.0+
 * License URI:             http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:             ingenico-server-for-woocommerce
 * Domain Path:             /lang
 * WC requires at least:    4.0.0
 * WC tested up to:         5.8.0
 */

defined( 'ABSPATH' ) || exit;

define('Ingenico_Server_For_Woo_TAB0', 'tab0');
define('Ingenico_Server_For_Woo_TAB1', 'tab1');

//Enable below for development purposes only! comment while creating official release
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Ingenico_Server_For_Woocommerce_Orders_Table extends WP_List_Table {
   
   function __construct(){
      global $status, $page;
      parent::__construct( array(
            'singular'  => __( 'orderid', 'mylisttable' ),     //singular name of the listed records
            'plural'    => __( 'orderids', 'mylisttable' ),   //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
      ) );
      add_action( 'admin_head', array( &$this, 'admin_header' ) );            
   }
    
   function admin_header() {
      echo '<style type="text/css">';
      echo '.wp-list-table .column-id { width: 5%; }';
      echo '.wp-list-table .column-billing { width: 40%; }';
      echo '.wp-list-table .column-date_created { width: 35%; }';
      echo '.wp-list-table .column-total { width: 20%;}';
      echo '</style>';
   }
   function no_items() {
      _e( 'No Woocommerce orders found.' );
   }
  
   function column_default( $item, $column_name ) {
      switch( $column_name ) { 
         case 'id':
            return '<b>#' . $item->get_id() . '</b>';
         case 'billing':
            return $item->get_billing_first_name() . ' ' . $item->get_billing_last_name(). ', ' . $item->get_billing_address_1(). ' ' . $item->get_billing_address_2() . ', ' . $item->get_billing_postcode() . ' ' . $item->get_billing_city();
         case 'date_created':
            return $item->get_date_created();
         case 'total':
            return $item->get_formatted_order_total();
         default:
            return print_r( $item, true );
      }
   }
   function get_sortable_columns() {
      $sortable_columns = array(
         'id'  => array('id',false),
         'billing' => array('billing',false),
         'date_created'   => array('date_created',false),
         'total'   => array('total',false)
      );
      return $sortable_columns;
   }

   function get_columns(){
      $columns = array(
            'cb'           => '<input type="checkbox" />',
            'id'           => __( 'Zamówienie', 'mylisttable' ),
            'billing'      => __( 'Klient', 'mylisttable' ),
            'date_created' => __( 'Data', 'mylisttable' ),
            'total'        => __( 'Suma', 'mylisttable' )
      );
      return $columns;
   }

   function column_id($item){
      $page = "ingenico-server-for-woocommerce"; //is_int((int)$_REQUEST['page'])?(int)$_REQUEST['page']:0;
      $actions = array(
         'view'      => sprintf('<a href="?page=%s&action=%s&orderid=%s">Pobierz płatność</a>', $page, 'view', $item->get_id())
      );
      return sprintf('%1$s %2$s', $item->get_id(), $this->row_actions($actions) );
   }
   /*function get_bulk_actions() {
      $actions = array(
         'view'    => 'Pobierz płatność'
      );
      return $actions;
   }*/
   function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="orderid[]" value="%s" />',$item->get_id()
        );    
   }
   function prepare_items() {
      $columns  = $this->get_columns();
      $hidden   = array();
      $sortable = $this->get_sortable_columns();
      $this->_column_headers = array( $columns, $hidden, $sortable );
     
      $per_page = 5; //$per_page = $this->get_items_per_page('orders_per_page', 5);
      $current_page = $this->get_pagenum();
      
      $args = array(
         'type' => 'shop_order',
         'paginate' => true,
         'limit' => $per_page,
         'paged' => $current_page,
      );
      $results = wc_get_orders( $args );
      
      /*$found_data = array();
      foreach ($results->orders as $order) {
         $found_data[] = $order->get_data();
      }
      print_r($found_data);*/
      //$total_items = count( $this->example_data );
      $total_items = $results->total;
      
      //$found_data = array_slice( $this->example_data,( ( $current_page-1 )* $per_page ), $per_page );
      $this->set_pagination_args( array(
         'total_items' => $total_items,                  //WE have to calculate the total number of items
         'per_page'    => $per_page                     //WE have to determine how many items to show on a page
      ) );
      $this->items = $results->orders;//$found_data;
      //print_r($this->items);
   }
}


function ingenico_server_for_woocommerce_add_menu() {
   add_submenu_page( 'woocommerce', "Ingenico Server", "Ingenico Server", 'manage_options', "ingenico-server-for-woocommerce", "ingenico_server_for_woocommerce_page" );
}
add_action("admin_menu", "ingenico_server_for_woocommerce_add_menu");

function ingenico_server_for_woocommerce_validateOrderIDs($orderids) {
   $result = array();
   foreach($orderids as $orderid) {
      $orderid = (int)$orderid;
      if( is_int($orderid) )
         $result[] = $orderid;
   }
   return $result;
}

function ingenico_server_for_woocommerce_processOrderIDs($orderids) {
   $documents = array();
   foreach($orderids as $orderid) {
      $order = wc_get_order( $orderid );
      // print_r($order);
      // print_r($order->get_items());
      $positions = array();   // Pozycje do ufiskalnienia
      
      echo '<h3>Zamówienie #'.esc_html($order->get_id()).'</h3>';
      echo '<b>Zamawiający:</b> ' . esc_html($order->get_billing_first_name()) . ' ' . esc_html($order->get_billing_last_name()). ', ' . 
            esc_html($order->get_billing_address_1()). ' ' . esc_html($order->get_billing_address_2()) . ', ' . 
            esc_html($order->get_billing_postcode()) . ' ' . esc_html($order->get_billing_city()) . '<br/>';
      echo '<br/>';
      echo '<table><tr><th>#</th><th>Nazwa</th><th>Ilość</th><th>Stawka</th><th>VAT</th><th>Netto</th><th>Brutto</th></tr>';
      $lp = 1;
      foreach ($order->get_items() as $item_key => $item ) {   //dla każdej pozycji wyliczamy VAT
         // print_r($item);
         $item_name       = $item->get_name(); // Name of the product
         $quantity        = $item->get_quantity();  
         $tax_class       = $item->get_tax_class();
         $line_total      = $item->get_total(); // Line total (discounted)
         $line_total_tax  = round($item->get_total_tax(),2); // Line total tax (discounted)
         $vatp = round(($line_total_tax / $line_total) * 100.0);
         $brutto = $line_total_tax + $line_total;
         $positions[] = array('na' => $item_name, 'il'=>$quantity, 'vtp'=>$vatp, 'pr'=> round((($item->get_total_tax() + $item->get_total()) / $quantity)*100) );
         echo "<tr>" . 
            "<td>$lp</td><td>$item_name</td><td align=\"right\">$quantity szt</td>" . 
            "<td align=\"right\">$vatp %</td><td align=\"right\">$line_total_tax " . esc_html($order->get_currency()) . "</td>" . 
            "<td align=\"right\">$line_total " . esc_html($order->get_currency()) . "</td><td align=\"right\">$brutto ". esc_html($order->get_currency()) . "</td>" . 
            "</tr>";
         $lp++;
      }
      if( count($order->get_shipping_methods() )>0) {
         $line_total       = $order->get_shipping_total();
         $line_total_tax   = round($order->get_shipping_tax(),2);
         $item_name        = esc_html($order->get_shipping_method());
         $quantity = 1;
         $vatp = round(($line_total_tax / $line_total) * 100.0);
         $brutto = round($order->get_shipping_tax() + $order->get_shipping_total(),2);
         $positions[] = array('na' => $item_name, 'il'=>$quantity, 'vtp'=>$vatp, 'pr'=>round($order->get_shipping_tax() + $order->get_shipping_total() )*100);
         echo "<tr>" . 
            "<td>$lp</td><td>$item_name</td><td align=\"right\">$quantity szt</td><td align=\"right\">$vatp %</td>" . 
            "<td align=\"right\">$line_total_tax " . esc_html($order->get_currency()) . "</td>" . 
            "<td align=\"right\">$line_total " . esc_html($order->get_currency()) . "</td>" . 
            "<td align=\"right\">$brutto ". esc_html($order->get_currency()) . "</td>" . 
            "</tr>";
      }
      echo '</table>';
      echo '<br/>';
      echo '<b>VAT:</b> ' .round($order->get_total_tax(),2) . ' ' . esc_html($order->get_currency()) . '</br>';
      echo '<b>Razem brutto:</b> ' .esc_html($order->get_total()) . ' ' . esc_html($order->get_currency()) . '</br>';
      
      $documents[] = array('to'=>$order->get_total()*100, 'lines'=>$positions);
   }
   return $documents;
}

function ingenico_server_for_woocommerce_readPluginConfiguration() {
   
   $options = get_option('woocommerce_ingenico_server_config');
   if ($options === false){
      $options = array(
         'IngenicoServerHost' => 'http://127.0.0.1:3020',
         'ExtraLines' => '{
            "transport" : {
               "encryptSession" : false
            }
         }'
      );
      update_option( 'woocommerce_ingenico_server_config', $options );
   }
   return $options;
}
/**
 * Setting Page Options
 * - add setting page
 * - save setting page
 *
 * @since 1.0
 */
function ingenico_server_for_woocommerce_page()
{
   $configdata = ingenico_server_for_woocommerce_readPluginConfiguration();
   $IngenicoServerHost = $configdata['IngenicoServerHost'];
   $ExtraLines = $configdata['ExtraLines'];
?>
<div class="wrap">
 
   <h1>Ingenico Server Woocommerce plugin by <a href="http://bigdotsoftware.pl/ingenicoserver-restful-service-dla-terminali-platniczych" target="_blank">BigDotSoftware</a></h1>
   
   <?php
   // Determine active tab
   if( isset( $_GET[ 'tab' ] ) ) {
      $active_tab = isset( $_GET[ 'tab' ] ) && in_array($_GET[ 'tab' ], array(Ingenico_Server_For_Woo_TAB0, Ingenico_Server_For_Woo_TAB1)) ? $_GET[ 'tab' ] : Ingenico_Server_For_Woo_TAB0;
   } else if( isset( $_POST[ 'tab' ] ) ) {
      $active_tab = isset( $_POST[ 'tab' ] ) && in_array($_POST[ 'tab' ], array(Ingenico_Server_For_Woo_TAB0, Ingenico_Server_For_Woo_TAB1)) ? $_POST[ 'tab' ] : Ingenico_Server_For_Woo_TAB0;
   } else {
      $active_tab = Ingenico_Server_For_Woo_TAB0;
   }

   ?>
   <h2 class="nav-tab-wrapper">
      <a href="?page=ingenico-server-for-woocommerce&tab=<?php echo Ingenico_Server_For_Woo_TAB0; ?>" class="nav-tab <?php echo $active_tab == Ingenico_Server_For_Woo_TAB0 ? 'nav-tab-active' : ''; ?>">Płatności</a>
      <a href="?page=ingenico-server-for-woocommerce&tab=<?php echo Ingenico_Server_For_Woo_TAB1; ?>" class="nav-tab <?php echo $active_tab == Ingenico_Server_For_Woo_TAB1 ? 'nav-tab-active' : ''; ?>">Ustawienia</a>
   </h2>
         
   <!-- ACTIVE TAB0 -->
   <?php if( $active_tab == Ingenico_Server_For_Woo_TAB0 ) { ?>
   
      <h2>Pobierz płatność do wybranego zamówienia</h2>
      <form method='POST' action='<?php echo admin_url( 'admin.php?page=ingenico-server-for-woocommerce' ); ?>'>
         <input type='text' value='<?php echo isset( $_REQUEST[ 'orderid' ] ) && is_int((int)$_REQUEST[ 'orderid' ]) ? (int)$_REQUEST[ 'orderid' ] : ''; ?>' name='orderid'/>
         <input type='hidden' name='action' value='view'/>
         <input type='hidden' name='tab' value='<?php echo $active_tab; ?>'/>
         <?php wp_nonce_field( 'submitform', 'submitform_nonce' ); ?>
         <?php submit_button('Pokaż zamówienie'); ?>
      </form>
      
      <?php
      if( isset( $_REQUEST[ 'orderid' ] ) && (isset( $_REQUEST[ 'action' ] ) && in_array($_REQUEST[ 'action' ], array('view','print')) || isset( $_REQUEST[ 'action2' ] ) && in_array($_REQUEST[ 'action2' ], array('view','print'))) ) {
         $orderids = array();
         if( is_array($_REQUEST[ 'orderid' ]) )
            $orderids = (int) $_REQUEST[ 'orderid' ];
         else
            $orderids[] = (int) $_REQUEST[ 'orderid' ];
         
         //bulk actions are disabled, size($orderids) is always 1
         $orderids = ingenico_server_for_woocommerce_validateOrderIDs($orderids);
         $documents = ingenico_server_for_woocommerce_processOrderIDs($orderids)
         
         ?>
         <div id="printwait" style="display:none">
            Trwa pobieranie płatności, proszę czekać
            <img src="<?php echo esc_url(plugins_url('spinner-1s-200px.gif', __FILE__ )); ?>"/>
         </div>
         <div id="printwaitok" style="display:none;background: green;color: white;padding: 10px;border-radius: 5px;">
            Pobieranie płatności zakończono pomyślnie
            <img src="<?php echo esc_url(plugins_url('spinner-1s-200px.gif', __FILE__ )); ?>"/>
         </div>
         <div id="printwaitwarning" style="display:none;background: orange;color: white;padding: 10px;border-radius: 5px;">
            Pobieranie płatności zakończono częściowo pomyślnie
            <img src="<?php echo esc_url(plugins_url('spinner-1s-200px.gif', __FILE__ )); ?>"/>
         </div>         
         <div id="printwaiterror" style="display:none;background: red;color: white;padding: 10px;border-radius: 5px;">
            Błąd
         </div>
         
         <script>
         function readResponseJson(txt) {
            var responseObj = {};
            try{
               responseObj = JSON.parse(txt);
            }catch(e) {
               // something is wrong
               console.error('Cannot parse:' + txt);
               document.getElementById("printwaiterror").style.display = "initial";
               document.getElementById("printwaiterror").innerText = 'Nie można odczytać odpowiedzi z serwisu';
            }
            return responseObj;
         }
         function readTransactionStatus() {
            document.getElementById("printwait").style.display = "initial";
            var request = new XMLHttpRequest();
               var url = '<?php echo $IngenicoServerHost; ?>/polcard/v1/ingenico_transaction_end?fulldebug=true';
               request.open('GET', url, true);
               request.setRequestHeader('Content-type', 'application/json');
               request.onreadystatechange = function() {
                  console.log('trendcheck=' + request.readyState);
                  console.log('trendcheck=' + request.status);
      
                  if(request.readyState == 4 /*DONE*/) {
                     var responseObj = readResponseJson(request.responseText);
                     if( request.status == 200 && responseObj.ok ) {
                        document.getElementById("printwait").style.display = "none";
                        document.getElementById("printwaitok").style.display = "initial";
                        document.getElementById("printwaitok").innerHTML = 'Kod autoryzacji: <b>' 
                                 + responseObj.terminal.transactionDetails.authorizationCode + '</b>'
                                 + ' Status transakcji: <b>'
                                 + responseObj.terminal.authorizationResult + '</b>';
                     }else{
                        document.getElementById("printwaiterror").style.display = "initial";
                        document.getElementById("printwaiterror").innerText = 'Błąd: ' + (responseObj.message!=null?responseObj.message:'') + (responseObj.error!=null?responseObj.error:'');
                     }
                  }else{
                     document.getElementById("printwait").style.display = "none";
                  }
               };
               request.send(null);
         }
         function scheduleStatusChecker(interval, every) {
            document.getElementById("printwait").style.display = "initial";
            var i = 0;
            var lastStatusText = '';
            var intervalHandle = setInterval(function () {
               
               if( i%every == 0 ) {
                  var statusrequest = new XMLHttpRequest();
                  var url = '<?php echo $IngenicoServerHost; ?>/polcard/v1/ingenico_status?fulldebug=true';
                  statusrequest.open('GET', url, true);
                  statusrequest.setRequestHeader('Content-type', 'application/json');
                  statusrequest.onreadystatechange = function() {
                     console.log('statuscheck=' + statusrequest.readyState);
                     console.log('statuscheck=' + statusrequest.status);
         
                     if(statusrequest.readyState == 4 /*DONE*/) {
                        var responseObj = readResponseJson(statusrequest.responseText);
                        
                        if( statusrequest.status == 200 ) {
                           lastStatusText = responseObj.terminal.transactionStatusText;
                           document.getElementById("printwaitok").style.display = "initial";
                           document.getElementById("printwaitok").innerHTML = 'Aktualny status: <b>' + lastStatusText + '</b>';
                           
                           if( lastStatusText == 'WaitTrEnd' || lastStatusText == 'TransactionAborted' ) {
                              document.getElementById("printwait").style.display = "none";
                              clearInterval(intervalHandle);
                              readTransactionStatus();
                           }
                           if( lastStatusText == 'AskingEcrToPrintData' ) {
                              lastStatusText += ' -> ' + responseObj.terminal.promptText;
                           }
                        }else{
                           document.getElementById("printwaiterror").style.display = "initial";
                           document.getElementById("printwaiterror").innerText = 'Błąd: ' + (responseObj.message!=null?responseObj.message:'') + (responseObj.error!=null?responseObj.error:'');
                        }
                     }else{
                        // document.getElementById("printwait").style.display = "none";
                     }
                  };
                  statusrequest.send(null);
               }
               document.getElementById("printwaitok").style.display = "initial";
               document.getElementById("printwaitok").innerHTML = 'Oczekiwanie na terminal: <b>' + Math.round(i/1000) + ' s.</b>' + (lastStatusText!=''?' (Ostatni status: ' + lastStatusText + ')':'');
               i += interval;
            }, interval);
                     
         }
         function pobierzplatnosc() {
            
            document.getElementById("printwait").style.display = "initial";
            document.getElementById("printwaitok").style.display = "none";
            document.getElementById("printwaitwarning").style.display = "none";
            document.getElementById("printwaiterror").style.display = "none";
            
            var http = new XMLHttpRequest();
            var url = '<?php echo $IngenicoServerHost; ?>/polcard/v1/ingenico_transaction?fulldebug=true';
            var params = [];
            <?php foreach($documents as $document) { ?>
            params.push(
            {
               type: "purchase",
               transactionAmount: <?php echo $document['to']; ?>,
               cashbackAmount: 0,
               tipAmount: 0,
               trackData : "",
               surchargeFeeAmount : 0,
               foreignGoodsAmount: 0,
               currencyCode: 985,
               reversal : false,
               transport: <?php 
                  if( $ExtraLines=="" ) {
                     echo '{encryptSession : false}';
                  } else {
                     $obj = json_decode(htmlspecialchars_decode($ExtraLines), true);
                     //print_r($obj);
                     echo json_encode($obj["transport"]);
                  } ?>,
               extras: {
                  responseType: "extended_full",
                  date: "2021/04/28 13:23:15",
                  version: "v2.0",
                  manufacturerName: "Big Dot Software",
                  protocolVersion: "0046800"
               }
            });
            <?php } ?>
            http.open('POST', url, true);

            //Send the proper header information along with the request
            http.setRequestHeader('Content-type', 'application/json');

            http.onreadystatechange = function() {//Call a function when the state changes.
               //alert(http.responseText);
               console.log(http.readyState);
               console.log(http.status);
               
               if(http.readyState == 4 /*DONE*/) {
                  
                  var responseObj = readResponseJson(http.responseText);
               
                  if( http.status == 200 && responseObj.ok ) {
                     document.getElementById("printwaitok").style.display = "initial";
                     document.getElementById("printwaitok").innerHTML = 'Rozpoczęcie płatności zakończono pomyślnie. <b>Czekam na zakończenie transakcji</b>';
                     
                     //check status every 3s
                     scheduleStatusChecker(100, 3000);
                     
                  } else {
                     // w przypadku bledy pojedynczego paragonu
                     document.getElementById("printwaiterror").style.display = "initial";
                     document.getElementById("printwaiterror").innerText = 'Błąd: ' + (responseObj.message!=null?responseObj.message:'') + (responseObj.error!=null?responseObj.error:'');
                  }
               }
               document.getElementById("printwait").style.display = "none";
            }
            <?php if(count($orderids)>1) { ?>
            http.send(JSON.stringify(params));
            <?php } else { ?>
            http.send(JSON.stringify(params[0]));
            <?php } ?>
         }
         </script>
         <?php if(count($orderids)>1) { ?>
         <p class="submit">
            <input type="submit" onclick="pobierzplatnosc()" name="submit" id="submit" class="button button-primary" value="Rozpocznij pobieranie płatności do zamówień <?php echo implode (", ", $orderids); ?>">
         </p>
         <?php } else { ?>
         <p class="submit">
            <input type="submit" onclick="pobierzplatnosc()" name="submit" id="submit" class="button button-primary" value="Rozpocznij pobieranie płatności do zamówienia <?php echo $orderids[0]; ?>">
         </p>
         <?php } ?>
         <?php
         
      }
      ?>
      
      
      <h2>Przeglądaj zamówienia</h2>
      <?php /*print_r($_POST);*/ ?>
      <?php 
         $myListTable = new Ingenico_Server_For_Woocommerce_Orders_Table();
         $myListTable->prepare_items();
      ?>
   <form method="post">
      <input type="hidden" name="page" value="ingenico-server-for-woocommerce">
      <?php
         $myListTable->search_box( 'search', 'search_id' );
         $myListTable->display(); 
      ?>
   </form>
   
   <!-- ACTIVE TAB1 -->
   <?php } else { ?>
   
      <h2>Ustawienia</h2>
      <i>Plugin nie jest kompletnym rozwiązaniem a jedynie przykładem wykorzystania komponentu Ingenico Server do rozpoczęcia transakcji na terminalu płatniczym Polcard First Data. Ingenico Server wspiera również inncyh dostawców jak eService. W ramach każdego dostawcy Ingenico Server umożliwia wykonywanie transakcji dowolnego typu, między innymi preautoryzacji, transakcji cash back, BLIK, obsługi kart lojalnościowych, anulowania transakcji, zwrotów na kartę i wielu, wielu innych operacji. Możliwe jest również zarządzanie samym terminalem. Stąd, plugin często wymaga indywidualnego dopasowania.</br>
      <a href="https://www.youtube.com/channel/UCbX9ECPnLMRq8oMOWT2k8UQ">Our Youtube channel</a></i>
      <form method="post" action="options.php">
         <?php
         settings_fields("woocommerce_ingenico_server_config");
         do_settings_sections("ingenico-server-for-woocommerce");      //Prints out all settings sections added to a particular settings page
         submit_button();
         ?>
      </form>
      
   <?php } ?>
   
   <!-- END OF TABS -->
    
</div>
 
<?php
}

/**
 * Init setting section, Init setting field and register settings page
 *
 * @since 1.0
 */
function ingenico_server_for_woocommerce_settings() {   
   register_setting("woocommerce_ingenico_server_config", "woocommerce_ingenico_server_config");
   add_settings_section("woocommerce_ingenico_server_main_section", "", null, "ingenico-server-for-woocommerce");
   add_settings_field("woocommerce-ingenico-server-serverurl-text", "Ingenico Server", "ingenico_server_for_woocommerce_serverurl_options", "ingenico-server-for-woocommerce", "woocommerce_ingenico_server_main_section");
   add_settings_field("woocommerce-ingenico-server-extralines-text", "Parametry transmisji", "ingenico_server_for_woocommerce_extralines_options", "ingenico-server-for-woocommerce", "woocommerce_ingenico_server_main_section");
}
add_action("admin_init", "ingenico_server_for_woocommerce_settings");


function ingenico_server_for_woocommerce_serverurl_options() {
   $configdata = ingenico_server_for_woocommerce_readPluginConfiguration();
   // print_r($configdata);
   $IngenicoServerHost = $configdata['IngenicoServerHost'];
?>
<div class="postbox" style="width: 65%; padding: 30px;">
	<input type="text" id="woocommerce-ingenico-server-serverurl-text" name="woocommerce_ingenico_server_config[IngenicoServerHost]"
		value="<?php echo $IngenicoServerHost; ?>" />Podaj URL serwisu Ingenico Server (domyślnie: http://127.0.0.1:3020)<br />
      <br /><br />
      Pobierz najnowszą wersję Ingenico Server dla swojego systemu operacyjnego: <a href="https://blog.bigdotsoftware.pl/ingenico-server-instalacja/">https://blog.bigdotsoftware.pl/ingenico-server-instalacja/</a>
</div>
<?php
}

function ingenico_server_for_woocommerce_extralines_options() {
   $configdata = ingenico_server_for_woocommerce_readPluginConfiguration();
   // print_r($configdata);
   $ExtraLines = $configdata['ExtraLines'];
?>
<div class="postbox" style="width: 65%; padding: 30px;">
	<textarea id="woocommerce-ingenico-server-extralines-text" name="woocommerce_ingenico_server_config[ExtraLines]" rows="10" cols="120"><?php echo $ExtraLines; ?></textarea><br/>
   Dodatkowe parametry transmisji, dokumentacja: <a href="https://blog.bigdotsoftware.pl/ingenico-server-integracja/">https://blog.bigdotsoftware.pl/ingenico-server-integracja/</a><br />
</div>
<?php
}

/*
add_filter('the_content', 'woocommerce_ingenico_server_content');
function woocommerce_ingenico_server_content($content) {
	return $content . stripslashes_deep(esc_attr(get_option('woocommerce-ingenico-server-serverurl-text')));
}
*/
