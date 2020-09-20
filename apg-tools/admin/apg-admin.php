<div class="wrap">
  <h1>APG Tools</h1>
  <form method="post" action="<?=base_url('admin/save.php');?>">
    <table class="form-table" role="presentation">
      <tbody>
        <tr>
          <th scope="row"><label for="token-auth">Token Auth</label></th>
          <td>
            <input name="token-auth" type="text" id="token-auth" value="<?=$apg_config->token_auth;?>" class="regular-text" style="background-image: url(&quot;data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAABHklEQVQ4EaVTO26DQBD1ohQWaS2lg9JybZ+AK7hNwx2oIoVf4UPQ0Lj1FdKktevIpel8AKNUkDcWMxpgSaIEaTVv3sx7uztiTdu2s/98DywOw3Dued4Who/M2aIx5lZV1aEsy0+qiwHELyi+Ytl0PQ69SxAxkWIA4RMRTdNsKE59juMcuZd6xIAFeZ6fGCdJ8kY4y7KAuTRNGd7jyEBXsdOPE3a0QGPsniOnnYMO67LgSQN9T41F2QGrQRRFCwyzoIF2qyBuKKbcOgPXdVeY9rMWgNsjf9ccYesJhk3f5dYT1HX9gR0LLQR30TnjkUEcx2uIuS4RnI+aj6sJR0AM8AaumPaM/rRehyWhXqbFAA9kh3/8/NvHxAYGAsZ/il8IalkCLBfNVAAAAABJRU5ErkJggg==&quot;); background-repeat: no-repeat; background-attachment: scroll; background-size: 16px 18px; background-position: 98% 50%; cursor: auto;">
          </td>
        </tr>
      </tbody>
    </table>
    <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
  </form>
</div>