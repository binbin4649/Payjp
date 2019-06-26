
━━━━━━━━━━━━━━━━━━━━━━━━━━
ご購入ありがとうございます
━━━━━━━━━━━━━━━━━━━━━━━━━━

ポイントのご購入ありがとうございます。
以下明細になります。

日時：<?php echo $PointBook['created']."\n" ?>
決済：<?php echo $PointBook['brand'].' **** **** **** '.$PointBook['last4']."\n" ?>
金額：<?php echo number_format($PointBook['charge']).'円'."\n" ?>
決済番号：<?php echo $PointBook['id']."\n" ?>

会員番号：<?php echo $PointBook['mypage_id']."\n" ?>
ポイント残高合計：<?php echo number_format($PointBook['point_balance'])."\n" ?>


ログインURL
<?php echo $PointBook['loginUrl']."\n" ?>

