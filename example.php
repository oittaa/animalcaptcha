<html>
<head>
<title>Animal Captcha Example</title>
<style type="text/css">
body
{
    font-family: sans;
    text-align: center;
}
.captcha
{
    padding: 10px;
    background: #999;
    border-radius: 10px;
    display: inline-block;
}
.captcha-image
{
    display: inline-block;
    border: 3px solid transparent;
}
.captcha-image.selected
{
    border-color: green;
}
.controls
{
}
button
{
    margin-top: 10px;
    border: 3px solid #333;
    border-radius: 5px;
    font-size: 20pt;
}
</style>
</head>
<body>
<?
/* Init animal captcha */
require_once( 'class.AnimalCaptcha.php' );
if (!isset($_SESSION))
{
    session_start ();
    header("Cache-control: private");
}
$ac = new AnimalCaptcha();
if ( !isset( $_POST['captcha'] ) )
{
?>
<form method="post">
<div class="captcha">
    <h3>To prove you are human, please select the elephant:</h3>
    <input type="hidden" name="captcha" value="<?=@$_POST['captcha']?>" />
    <?
    $sel = @$_POST['captcha'] or null;
    foreach( $ac->getCaptcha() as $imgid ) { ?>
    <div class="captcha-image<?=$sel==$imgid?' selected':''?>" data-id="<?=$imgid?>"><img src="captcha.php?get_image=<?=$imgid?>"></div>
    <? } ?>
    <div class="controls">
        <button type="submit">Confirm</button>
    </div>
</div>
</form>
<? } else {
    $capt_status = $ac->check( $_POST['captcha'] );

    if ( $capt_status == 'ok' )
    {
        ?><h1>Captcha verification Succeeded!</h1><?
    }
    else
    {
        ?><h2>Catcha verification failed!</h2>
        <p>Reason: <?=$capt_status?></p>
        <?
    }
} ?>
<script src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {  //Captcha
    $('.captcha').on( 'click.captcha', '.captcha-image', function(){
        $(".captcha .selected").removeClass("selected");
        $(this).addClass("selected");
        $( '.captcha input' ).val( $( this ).data( 'id' ) );
    });
} );
</script>
</body>
</html>
