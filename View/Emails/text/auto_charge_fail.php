
━━━━━━━━━━━━━━━━━━━━━━━━━━
ポイントチャージ失敗のご連絡
━━━━━━━━━━━━━━━━━━━━━━━━━━

<?php echo $PointBook['BreakPoint'] ?>ポイント以下になりましたので、ポイントチャージを行いましたが失敗しました。
クレジットカードを確認し再登録するか、違うクレジットーカードをご登録ください。


会員番号：<?php echo $Mypage['id']."\n" ?>
お名前：<?php echo $Mypage['name']."\n" ?>
残ポイント：<?php echo number_format($PointUser['point'])."\n" ?>

オートチャージ登録情報：
<?php echo $PointUser['payjp_brand'].' **** **** **** '.$PointUser['payjp_last4']."\n" ?>
設定金額：<?php echo number_format($PointUser['charge_point'])."\n" ?>


予約があってもポイントがなくなり次第、サービスが停止いたします。
予めご了承くださいませ。


---

マイページログイン
　<?php echo $mailConfig['site_url'] ?>members/mypages/login

お問合せ
　<?php echo $mailConfig['site_url'] ?>contact/

不具合報告
　<?php echo $mailConfig['site_url'] ?>error_report/

---
　<?php echo $mailConfig['site_name'] ?>　
　<?php echo $mailConfig['site_url'] ?>　
　Mail:<?php echo $mailConfig['site_email'] ?>　