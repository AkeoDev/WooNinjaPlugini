<?php
function sendEmailStatusUpdate($order_id) {
    // Mail content type

    $order = wc_get_order( $order_id );

/*     $pm = get_post_meta( $order->get_id(), '_payment_method', true );

    if ( $pm != "bacs" ) {
        return;
    } */

     function mail_content_type() {
        return "text/html";
    }
    add_filter( 'wp_mail_content_type','mail_content_type' );  

    $headers_email = array();
    $woocommerce_email_from_name = get_option('woocommerce_email_from_name');

    if (!empty(get_option('emailRecipient_upn'))) {
        $emailRecipient = get_option('emailRecipient_upn');
    } else {
        $emailRecipient = get_option('woocommerce_email_from_address');
    }
    $from_email = get_option('woocommerce_email_from_address');
    $headers_email[] = "From: {$woocommerce_email_from_name} <$from_email>";
    $subject = "Wooninja - uPay: Sprememba statusa uPay naloga za naročilo: #".$order->get_id()." ";
    $message = "";
    $message .= '<html><body>';
    $message .= '<div style="background: #eee;">';
    $message .= '<table border="0" cellpadding="0" cellspacing="0" width="100%" style="table-layout:fixed;background-color:#e5e5e5" id="bodyTable">';
    $message .= '<tbody><tr>';
    $message .= '<td style="padding-right:10px;padding-left:10px;" align="center" valign="top" id="bodyCell">';
    $message .= '<table border="0" cellpadding="0" cellspacing="0" width="100%" class="wrapperBody" style="max-width:600px">';
    $message .= '<td style="background-color:"white";font-size:1px;line-height:3px" class="topBorder" height="3">&nbsp;</td>';
    $message .= '</tr><tr>';
    $message .= '<tr><td style="padding-left:20px;padding-right:20px" align="center" valign="top" class="containtTable ui-sortable">';
    $message .= '<table border="0" cellpadding="0" cellspacing="0" width="100%" class="tableDescription" style="">';
    $message .= '<tbody><tr><td style="padding-bottom: 20px;" valign="top" class="description">';
    $message .= '<tr>';
    $message .= '<p>Pozdravljeni,</p>';
    $message .= '<p>Sprememba status uPay naloga za:</p><br>';
    $message .= '<p><b>Namen plačila: </b>Plačilo predračuna</p>';
    $message .= '<p><b>Znesek: </b>'.$order->get_total().'</p>';
    $message .= '<p><b>Ident: </b>'.$order->get_meta('upay_referenca').'</p>';
    $message .= '<p><b>Status: </b>'.$order->get_status().'</p>';
    $message .= '<p><b>Čas in ura:</b> '.date("F j, Y, g:i a").'</p>';
    $message .= '</td></tr></tbody></table>';
    $message .= '<table border="0" cellpadding="0" cellspacing="0" width="100%" class="tableButton" style="">';
    $message .= '<tbody><tr><td style="padding-top:20px;padding-bottom:20px" align="center" valign="top">';
    $message .= '<table border="0" cellpadding="0" cellspacing="0" align="center">';
    $message .= '<tbody><tr><td style="padding: 12px 35px; border-radius: 50px;" align="center" class="ctaButton"> <a href=" '. $order->get_view_order_url() .'" style="color:#fff;font-family:Poppins,Helvetica,Arial,sans-serif;font-size:13px;font-weight:600;font-style:normal;letter-spacing:1px;line-height:20px;text-transform:uppercase;text-decoration:none;display:block; background-color: #D2DE26; padding: 20px;color:#fff;" target="_blank" class="text">Ogled naročila</a>';
    $message .= '</td></tr></tbody></table></td></tr></tbody></table></td></tr>';
    $message .= '<tr><td style="font-size:1px;line-height:1px" height="20">&nbsp;</td></tr>';
    $message .= '<table border="0" cellpadding="0" cellspacing="0" width="100%" class="space"><tbody><tr><td style="font-size:1px;line-height:1px" height="30">&nbsp;</td></tr></tbody></table>';
    $message .= '<tr><td align="center" valign="middle" style="padding-bottom: 40px;" class="emailRegards">Wooninja.si</td></tr>';
    $message .= '</tbody></table></td></tr></tbody></table></td></tr></tbody></table>';
    $message .= '</div>';
    $message .= '</html></body>';

    wp_mail($emailRecipient,$subject,$message,$headers_email);


} 
?>