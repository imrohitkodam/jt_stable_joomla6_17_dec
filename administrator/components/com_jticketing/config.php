<?php 

$emails_config=array(
'message_body'=>"<table class=\"maintab\" style=\"background: #ccc;\">
<tbody>
<tr>
<td class=\"img barcode\" style=\"vertical-align: top;\"> </td>
<td style=\"vertical-align: top;\">
<table class=\"info\" style=\"vertical-align: top;\" cellspacing=\"1\" cellpadding=\"4\">
<tbody>
<tr style=\"border: 5px solid #ccc;\">
<td class=\"jtlabel\" style=\"vertical-align: top; background: #fff;\">Event</td>
<td class=\"val event\" style=\"color: #333; font-size: 28px; font-family: Tahoma; text-align: left; width: 350px; vertical-align: top; background: #fff;\">[EVENT_NAME]</td>
</tr>
<tr style=\"border: 5px solid #ccc;\">
<td class=\"jtlabel\" style=\"vertical-align: top; background: #fff;\">Date + Time</td>
<td class=\"val\" style=\"color: #333; font-size: 18px; font-family: Tahoma; text-align: left; width: 350px; vertical-align: top; background: #fff;\">[ST_DATE] to [EN_DATE]</td>
</tr>
<tr style=\"border: 5px solid #ccc;\">
<td class=\"jtlabel\" style=\"vertical-align: top; background: #fff;\">Booking ID</td>
<td class=\"val valbig\" style=\"color: #333; font-size: 20px; font-family: Tahoma; text-align: left; width: 350px; font-weight: bold; vertical-align: top; background: #fff;\">[TICKET_ID]</td>
</tr>
<tr style=\"border: 5px solid #ccc;\">
<td class=\"jtlabel\" style=\"vertical-align: top; background: #fff;\">Name</td>
<td class=\"val valbig\" style=\"color: #333; font-size: 20px; font-family: Tahoma; text-align: left; width: 350px; font-weight: bold; vertical-align: top; background: #fff;\">[NAME]</td>
</tr>
<tr style=\"border: 5px solid #ccc;\">
<td class=\"jtlabel\" style=\"vertical-align: top; background: #fff;\">Location</td>
<td class=\"val\" style=\"color: #333; font-size: 18px; font-family: Tahoma; text-align: left; width: 350px; vertical-align: top; background: #fff;\">[EVENT_LOCATION]</td>
</tr>
<tr style=\"border: 5px solid #ccc;\">
<td class=\"jtlabel\" style=\"vertical-align: top; background: #fff;\">Order</td>
<td class=\"val valsmall\" style=\"color: #333; font-size: 14px; font-family: Tahoma; text-align: left; width: 350px; vertical-align: top; background: #fff;\">[BUYER_NAME] on [BOOKING_DATE]</td>
</tr>
</tbody>
</table>
</td>
<td style=\"vertical-align: top;\">
<table class=\"info2\" style=\"vertical-align: top;\" cellspacing=\"1\" cellpadding=\"1\">
<tbody>
<tr>
<td class=\"img\" style=\"vertical-align: top; background: #fff;\">[EVENT_IMAGE]</td>
</tr>
<tr>
<td class=\"jtlabel1\" style=\"vertical-align: top; background: #fff;\">Ticket Type:<br>[TICKET_TYPE]</td>
</tr>
<tr>
<td class=\"img\" style=\"vertical-align: top; background: #fff;\">[QR_CODE]</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
<p> </p>"
);

?>