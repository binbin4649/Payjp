<?php echo $Mypage['name'] ?>様


「<?php echo $mailConfig['site_name'] ?>」のカード決済が完了しました。
ご確認ください。


日時：<?php echo $PayjpCharge['created']."\n" ?>
Card：<?php echo $PayjpCharge['brand'].' **** **** **** '.$PayjpCharge['last4']."\n" ?>
金額：<?php echo number_format($PayjpCharge['charge'])."\n" ?>
決済番号：<?php echo $PayjpCharge['id']."\n" ?>


---
　<?php echo $mailConfig['site_name'] ?>　
　<?php echo $mailConfig['site_url'] ?>　
　Mail:<?php echo $mailConfig['site_email'] ?>　
