<tr>
  <th>ポイント管理メニュー</th>
  <td>
    <ul class="cleafix">
      <li>
        <?php $this->bcBaser->link('ユーザー管理', array('controller' => 'point_users', 'action' => 'index')) ?>
      </li>
      <li>
        <?php $this->bcBaser->link('PointBook', array('controller' => 'point_books', 'action' => 'index')) ?>
      </li>
      <li>
        <?php $this->bcBaser->link('クーポン一覧', array('controller' => 'point_coupons', 'action' => 'index')) ?>
      </li>
      <li>
        <?php $this->bcBaser->link('クーポン生成', array('controller' => 'point_coupons', 'action' => 'add')) ?>
      </li>
    </ul>
  </td>
</tr>