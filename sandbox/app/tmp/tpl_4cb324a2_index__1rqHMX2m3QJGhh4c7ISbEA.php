<?php 
function tpl_4cb324a2_index__1rqHMX2m3QJGhh4c7ISbEA(PHPTAL $tpl, PHPTAL_Context $ctx) {
$_thistpl = $tpl ;
$_translator = $tpl->getTranslator() ;
/* tag "documentElement" from line 1 */ ;
$ctx->setDocType('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',false) ;
?>

<?php /* tag "html" from line 4 */; ?>
<html xmlns="http://www.w3.org/1999/xhtml">
    <?php /* tag "head" from line 5 */; ?>
<head>
        <?php /* tag "meta" from line 6 */; ?>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
        <?php /* tag "title" from line 7 */; ?>
<title><?php 
$ctx->noThrow(true) ;
if (!phptal_isempty($_tmp_1 = $ctx->title)):  ;
?>
<?php 
echo phptal_escape($_tmp_1) ;
else:  ;
?>
Pinoco Test<?php 
endif ;
$ctx->noThrow(false) ;
?>
</title>
    </head>
    <?php /* tag "body" from line 9 */; ?>
<body>
        <?php /* tag "h1" from line 10 */; ?>
<h1>Hello world</h1>
        <?php /* tag "p" from line 11 */; ?>
<p><?php 
$ctx->noThrow(true) ;
if (!phptal_isempty($_tmp_1 = $ctx->title)):  ;
?>
<?php 
echo phptal_escape($_tmp_1) ;
else:  ;
?>
Value of "title" registerd to autolocal would be shown here.<?php 
endif ;
$ctx->noThrow(false) ;
?>
</p>
        
        <?php 
/* tag "div" from line 13 */ ;
$ctx->pushSlots() ;
$tpl->_executeMacroOfTemplate('/_vardump.html/alltests', $_thistpl) ;
$ctx->popSlots() ;
?>

        
        <?php /* tag "p" from line 17 */; ?>
<p>Sub contents
            <?php 
/* tag "a" from line 18 */ ;
if (null !== ($_tmp_1 = ($ctx->this->url('sub/')))):  ;
$_tmp_1 = ' href="'.phptal_escape($_tmp_1).'"' ;
else:  ;
$_tmp_1 = '' ;
endif ;
?>
<a<?php echo $_tmp_1 ?>
>sub/</a>
            <?php 
/* tag "a" from line 19 */ ;
if (null !== ($_tmp_1 = ($ctx->this->url('sub/index.html')))):  ;
$_tmp_1 = ' href="'.phptal_escape($_tmp_1).'"' ;
else:  ;
$_tmp_1 = '' ;
endif ;
?>
<a<?php echo $_tmp_1 ?>
>sub/index.html</a></p>
        <?php /* tag "p" from line 20 */; ?>
<p>Sub contents 2
            <?php 
/* tag "a" from line 21 */ ;
if (null !== ($_tmp_1 = ($ctx->this->url('sub2/')))):  ;
$_tmp_1 = ' href="'.phptal_escape($_tmp_1).'"' ;
else:  ;
$_tmp_1 = '' ;
endif ;
?>
<a<?php echo $_tmp_1 ?>
>sub2/</a>
            <?php 
/* tag "a" from line 22 */ ;
if (null !== ($_tmp_1 = ($ctx->this->url('sub2/index.php')))):  ;
$_tmp_1 = ' href="'.phptal_escape($_tmp_1).'"' ;
else:  ;
$_tmp_1 = '' ;
endif ;
?>
<a<?php echo $_tmp_1 ?>
>sub2/index.php</a></p>
        <?php /* tag "p" from line 23 */; ?>
<p>Smarty renderer
            <?php 
/* tag "a" from line 24 */ ;
if (null !== ($_tmp_1 = ($ctx->this->url('smarty/')))):  ;
$_tmp_1 = ' href="'.phptal_escape($_tmp_1).'"' ;
else:  ;
$_tmp_1 = '' ;
endif ;
?>
<a<?php echo $_tmp_1 ?>
>smarty/</a></p>
        <?php /* tag "p" from line 25 */; ?>
<p>Default pages
            <?php 
/* tag "a" from line 26 */ ;
if (null !== ($_tmp_1 = ($ctx->this->url('sub/x.html')))):  ;
$_tmp_1 = ' href="'.phptal_escape($_tmp_1).'"' ;
else:  ;
$_tmp_1 = '' ;
endif ;
?>
<a<?php echo $_tmp_1 ?>
>sub/x.html</a>
            <?php 
/* tag "a" from line 27 */ ;
if (null !== ($_tmp_1 = ($ctx->this->url('sub/y.html')))):  ;
$_tmp_1 = ' href="'.phptal_escape($_tmp_1).'"' ;
else:  ;
$_tmp_1 = '' ;
endif ;
?>
<a<?php echo $_tmp_1 ?>
>sub/y.html</a>
            <?php 
/* tag "a" from line 28 */ ;
if (null !== ($_tmp_1 = ($ctx->this->url('noidx/foo/')))):  ;
$_tmp_1 = ' href="'.phptal_escape($_tmp_1).'"' ;
else:  ;
$_tmp_1 = '' ;
endif ;
?>
<a<?php echo $_tmp_1 ?>
>noidx/foo/</a>
            <?php 
/* tag "a" from line 29 */ ;
if (null !== ($_tmp_1 = ($ctx->this->url('noidx/foo/index.html')))):  ;
$_tmp_1 = ' href="'.phptal_escape($_tmp_1).'"' ;
else:  ;
$_tmp_1 = '' ;
endif ;
?>
<a<?php echo $_tmp_1 ?>
>noidx/foo/index.html</a></p>
        <?php /* tag "p" from line 30 */; ?>
<p>Canceling default
            <?php 
/* tag "a" from line 31 */ ;
if (null !== ($_tmp_1 = ($ctx->this->url('cancel-default.html')))):  ;
$_tmp_1 = ' href="'.phptal_escape($_tmp_1).'"' ;
else:  ;
$_tmp_1 = '' ;
endif ;
?>
<a<?php echo $_tmp_1 ?>
>cancel-default.html</a>
            <?php 
/* tag "a" from line 32 */ ;
if (null !== ($_tmp_1 = ($ctx->this->url('cancel-default2.html')))):  ;
$_tmp_1 = ' href="'.phptal_escape($_tmp_1).'"' ;
else:  ;
$_tmp_1 = '' ;
endif ;
?>
<a<?php echo $_tmp_1 ?>
>cancel-default2.html</a></p>
    </body>
</html><?php 
/* end */ ;

}

?>
<?php /* 
*** DO NOT EDIT THIS FILE ***

Generated by PHPTAL from /Users/tanakahisateru/Sites/pinoco/test/www/basic/index.html (edit that file instead) */; ?>