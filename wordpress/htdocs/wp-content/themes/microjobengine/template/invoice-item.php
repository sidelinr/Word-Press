<?php
global $post, $ae_post_factory;
$invoice = MJE_Invoices::convert_invoice( $post );
?>
<tr class="invoice-item">
    <td><a href="<?php echo $invoice->detail_url; ?>"><?php echo $invoice->post_title; ?></a></td>
    <td><?php echo $invoice->date; ?></td>
    <td><?php echo $invoice->total; ?></td>
    <td><?php echo $invoice->payment_text; ?></td>
    <td><?php echo $invoice->status; ?></td>
</tr>